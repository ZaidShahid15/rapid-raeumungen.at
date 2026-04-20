<?php

namespace App\Support;

class FormMailHelper
{
    public const RECIPIENT_EMAILS = [
        'ast.mediainternational@gmail.com',
        'office@rapid-raeumungen.at',
    ];

    public static function extractPayload(array $input): array
    {
        $payload = [];

        foreach ($input as $key => $value) {
            $key = (string) $key;

            if (self::shouldIgnoreField($key)) {
                continue;
            }

            if (is_array($value)) {
                foreach ($value as $nestedKey => $nestedValue) {
                    $flattenedKey = $key . '[' . (string) $nestedKey . ']';

                    if (self::shouldIgnoreField($flattenedKey)) {
                        continue;
                    }

                    $normalizedValue = self::normalizeValue($nestedValue);

                    if ($normalizedValue === null || $normalizedValue === '') {
                        continue;
                    }

                    $payload[] = [
                        'key' => $flattenedKey,
                        'label' => self::labelFor($flattenedKey),
                        'value' => $normalizedValue,
                    ];
                }

                continue;
            }

            $normalizedValue = self::normalizeValue($value);

            if ($normalizedValue === null || $normalizedValue === '') {
                continue;
            }

            $payload[] = [
                'key' => $key,
                'label' => self::labelFor($key),
                'value' => $normalizedValue,
            ];
        }

        return $payload;
    }

    public static function replyTo(array $payload): ?string
    {
        foreach ($payload as $item) {
            $value = is_string($item['value']) ? trim($item['value']) : null;

            if ($value !== null && filter_var($value, FILTER_VALIDATE_EMAIL)) {
                return $value;
            }
        }

        return null;
    }

    public static function subjectFor(string $pagePath, array $payload): string
    {
        $email = self::replyTo($payload);
        $source = $pagePath === '/' ? 'homepage' : ltrim($pagePath, '/');
        $prefix = match (self::templateTypeFor($payload)) {
            'hero' => 'New hero lead',
            'contact' => 'New contact request',
            'newsletter' => 'New newsletter signup',
            'service-request' => 'New service request',
            default => 'New website enquiry',
        };

        return $email
            ? "{$prefix} from {$source} ({$email})"
            : "{$prefix} from {$source}";
    }

    public static function templateViewFor(array $payload): string
    {
        return match (self::templateTypeFor($payload)) {
            'hero' => 'emails.forms.hero',
            'contact' => 'emails.forms.contact',
            'newsletter' => 'emails.forms.newsletter',
            'service-request' => 'emails.forms.service-request',
            default => 'emails.forms.generic',
        };
    }

    public static function templateDataFor(array $payload): array
    {
        return [
            'sections' => self::buildSections($payload),
            'fields' => self::namedFieldsFor($payload),
        ];
    }

    public static function templateTypeFor(array $payload): string
    {
        $labels = collect($payload)
            ->pluck('label')
            ->filter(fn (mixed $label) => is_string($label))
            ->map(fn (string $label) => strtolower($label))
            ->values();

        $hasEmail = self::hasAnyLabel($labels->all(), ['email']);
        $hasPhone = self::hasAnyLabel($labels->all(), ['phone', 'number', 'tel']);
        $hasName = self::hasAnyLabel($labels->all(), ['name']);
        $hasMessage = self::hasAnyLabel($labels->all(), ['message', 'question', 'description']);
        $hasRequestDetails = self::hasAnyLabel($labels->all(), [
            'service',
            'shipping',
            'weight',
            'amount',
            'category',
            'date',
            'time',
            'address',
            'postal',
            'location',
            'city',
            'delivery',
            'length',
            'width',
            'height',
            'subject',
        ]);

        if ($hasRequestDetails) {
            return 'service-request';
        }

        if ($hasMessage || ($hasName && $hasEmail)) {
            return 'contact';
        }

        if ($hasName && $hasPhone) {
            return 'hero';
        }

        if ($hasEmail && count($payload) <= 2) {
            return 'newsletter';
        }

        return 'generic';
    }

    private static function buildSections(array $payload): array
    {
        $sections = [
            'contact' => [],
            'request' => [],
            'schedule' => [],
            'location' => [],
            'message' => [],
            'other' => [],
        ];

        foreach ($payload as $item) {
            $key = (string) ($item['key'] ?? '');
            $label = self::cleanDisplayLabel(
                (string) ($item['label'] ?? ''),
                $key
            );
            $value = trim((string) self::normalizeValue($item['value'] ?? ''));
            $normalizedLabel = strtolower($label);

            if ($label === '' || $value === '' || ($key !== '' && self::shouldIgnoreField($key))) {
                continue;
            }

            $entry = [
                'label' => $label,
                'value' => $value,
            ];

            if (self::matchesLabel($normalizedLabel, ['name', 'email', 'phone', 'number', 'tel', 'website', 'preferred contact'])) {
                $sections['contact'][] = $entry;
                continue;
            }

            if (self::matchesLabel($normalizedLabel, ['message', 'question', 'description'])) {
                $sections['message'][] = $entry;
                continue;
            }

            if (self::matchesLabel($normalizedLabel, ['date', 'time'])) {
                $sections['schedule'][] = $entry;
                continue;
            }

            if (self::matchesLabel($normalizedLabel, ['address', 'postal', 'location', 'city'])) {
                $sections['location'][] = $entry;
                continue;
            }

            if (self::matchesLabel($normalizedLabel, [
                'service',
                'shipping',
                'weight',
                'amount',
                'category',
                'delivery',
                'length',
                'width',
                'height',
                'subject',
                'affiliation',
                'department',
            ])) {
                $sections['request'][] = $entry;
                continue;
            }

            $sections['other'][] = $entry;
        }

        foreach ($sections as $key => $items) {
            $sections[$key] = collect($items)
                ->unique(fn (array $item) => strtolower($item['label']) . '|' . strtolower($item['value']))
                ->values()
                ->all();
        }

        return $sections;
    }

    private static function namedFieldsFor(array $payload): array
    {
        return [
            'name' => self::firstMatchingValue($payload, ['name'], ['name']),
            'email' => self::firstMatchingValue($payload, ['email'], ['email', 'mail']),
            'phone' => self::firstMatchingValue($payload, ['phone number', 'phone', 'tel', 'number'], ['phone', 'tel', 'number', '3c3e0f2']),
            'subject' => self::firstMatchingValue($payload, ['subject'], ['subject']),
            'type' => self::firstMatchingValue($payload, ['service type', 'service category', 'type', 'service'], ['service', 'type', 'category']),
            'message' => self::firstMatchingValue($payload, ['message', 'question'], ['message', 'question']),
            'details' => self::firstMatchingValue($payload, ['details', 'description'], ['details', 'detail', 'description']),
            'address' => self::firstMatchingValue($payload, ['address'], ['address']),
            'city' => self::firstMatchingValue($payload, ['city'], ['city']),
            'postal_code' => self::firstMatchingValue($payload, ['postal code'], ['postal', 'zip']),
            'location' => self::firstMatchingValue($payload, ['location'], ['location']),
            'date' => self::firstMatchingValue($payload, ['selected date', 'preferred date', 'shipping date', 'date from', 'date'], ['selected_date', 'date_from', 'date_to', 'date']),
            'time' => self::firstMatchingValue($payload, ['time preference', 'time'], ['time']),
            'preferred_contact_method' => self::firstMatchingValue($payload, ['preferred contact method'], ['preferred_contact_method']),
        ];
    }

    private static function shouldIgnoreField(string $key): bool
    {
        $lowercaseKey = strtolower(trim($key));

        return $lowercaseKey === '_token' ||
            $lowercaseKey === 'g-recaptcha-response' ||
            in_array($lowercaseKey, [
                'action',
                'nonce',
                'post_id',
                'form_id',
                'referer_title',
                'queried_id',
                'wpr form id',
                'wpr_form_id',
            ], true) ||
            str_starts_with($key, '_wpcf7') ||
            str_starts_with($key, '_wp');
    }

    private static function normalizeValue(mixed $value): ?string
    {
        if (is_array($value)) {
            $parts = array_filter(array_map(
                static fn (mixed $item) => self::normalizeValue($item),
                $value
            ));

            return $parts === [] ? null : implode(', ', $parts);
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);

        if (preg_match('/^(text|tel|email|textarea)\s*,\s*(.+)$/i', $string, $matches)) {
            $string = trim($matches[2]);
        }

        return $string === '' ? null : $string;
    }

    private static function labelFor(string $key): string
    {
        $labels = [
            'search-field' => 'Email',
            'text-475' => 'Name',
            'text-476' => 'Affiliation',
            'text-477' => 'Phone Number',
            'text-478' => 'Subject',
            'text-479' => 'Preferred Date',
            'text-71' => 'Name',
            'text-72' => 'Website',
            'text-130' => 'Subject',
            'text-131' => 'Phone Number',
            'text-139' => 'Name',
            'text-140' => 'Phone Number',
            'text-141' => 'Subject',
            'text-158' => 'Name',
            'text-159' => 'Phone Number',
            'text-160' => 'Address',
            'text-161' => 'City',
            'text-162' => 'Name',
            'text-163' => 'Phone Number',
            'text-164' => 'Address',
            'text-165' => 'City',
            'text-594' => 'Name',
            'text-706' => 'Amount',
            'text-707' => 'Name',
            'text-708' => 'Phone Number',
            'text-747' => 'Name',
            'email-28' => 'Email',
            'email-187' => 'Email',
            'email-539' => 'Email',
            'email-621' => 'Email',
            'email-631' => 'Email',
            'email-781' => 'Email',
            'email-846' => 'Email',
            'textarea-233' => 'Question',
            'textarea-377' => 'Message',
            'textarea-527' => 'Message',
            'textarea-803' => 'Message',
            'menu-732' => 'Service Type',
            'menu-733' => 'Weight',
            'menu-784' => 'Service Type',
            'menu-785' => 'Weight',
            'radio-990' => 'Delivery',
            'shiping-cun' => 'Shipping From',
            'shiping-to' => 'Shipping To',
            'shiping-date' => 'Shipping Date',
            'shiping-type' => 'Shipping Type',
            'msg-tm-478' => 'Shipment Details',
            'mail-aff' => 'Email',
            'sh-name' => 'Name',
            'form_fields[name]' => 'Name',
            'form_fields[email]' => 'Email',
            'form_fields[phone]' => 'Phone Number',
            'form_fields[message]' => 'Message',
            'form_fields[description]' => 'Description',
            'form_fields[question]' => 'Question',
            'form_fields[3c3e0f2]' => 'Phone Number',
            'form_fields[zip-code]' => 'Postal Code',
            'preferred_contact_method' => 'Preferred Contact Method',
            'category' => 'Service Category',
            'postal_code' => 'Postal Code',
            'location' => 'Location',
            'address' => 'Address',
            'time' => 'Time Preference',
            'date_from' => 'Date From',
            'date_to' => 'Date To',
            'selected_date' => 'Selected Date',
            'name' => 'Name',
            'email' => 'Email',
            'phone' => 'Phone Number',
            'city' => 'City',
            'delivery' => 'Delivery',
            'weight' => 'Weight',
            'length' => 'Length',
            'height' => 'Height',
            'width' => 'Width',
        ];

        $lowercaseKey = strtolower($key);

        if (isset($labels[$key])) {
            return $labels[$key];
        }

        if (str_contains($lowercaseKey, 'form content')) {
            $innerKey = self::extractBracketValue($key);

            if ($innerKey !== null) {
                $normalizedInnerKey = self::normalizeFormContentFieldKey($innerKey);

                if (isset($labels[$normalizedInnerKey])) {
                    return $labels[$normalizedInnerKey];
                }

                $formFieldsKey = 'form_fields[' . $normalizedInnerKey . ']';

                if (isset($labels[$formFieldsKey])) {
                    return $labels[$formFieldsKey];
                }

                $inferredLabel = self::inferFriendlyLabel($normalizedInnerKey);

                if ($inferredLabel !== null) {
                    return $inferredLabel;
                }

                return self::humanizeFieldKey($normalizedInnerKey);
            }
        }

        if (preg_match('/^email-\d+$/', $lowercaseKey)) {
            return 'Email';
        }

        if (preg_match('/^textarea-\d+$/', $lowercaseKey)) {
            return 'Message';
        }

        return ucwords(str_replace(['-', '_'], ' ', $key));
    }

    private static function hasAnyLabel(array $labels, array $terms): bool
    {
        foreach ($labels as $label) {
            if (self::matchesLabel((string) $label, $terms)) {
                return true;
            }
        }

        return false;
    }

    private static function matchesLabel(string $label, array $terms): bool
    {
        foreach ($terms as $term) {
            if (str_contains($label, $term)) {
                return true;
            }
        }

        return false;
    }

    private static function extractBracketValue(string $key): ?string
    {
        if (! preg_match('/\[(.+)\]/', $key, $matches)) {
            return null;
        }

        $value = trim((string) ($matches[1] ?? ''));

        return $value === '' ? null : $value;
    }

    private static function normalizeFormContentFieldKey(string $key): string
    {
        $normalized = strtolower(trim($key));
        $normalized = preg_replace('/^form\s+field\s+/i', '', $normalized) ?? $normalized;

        return trim($normalized);
    }

    private static function inferFriendlyLabel(string $key): ?string
    {
        if ($key === '') {
            return null;
        }

        if (str_contains($key, 'email') || $key === 'mail') {
            return 'Email';
        }

        if (str_contains($key, 'phone') || str_contains($key, 'tel') || str_contains($key, 'number') || $key === '3c3e0f2') {
            return 'Phone Number';
        }

        if (
            str_contains($key, 'message') ||
            str_contains($key, 'question') ||
            str_contains($key, 'description') ||
            str_contains($key, 'details') ||
            str_contains($key, 'detail')
        ) {
            return 'Details';
        }

        if (
            str_contains($key, 'type') ||
            str_contains($key, 'service') ||
            str_contains($key, 'category')
        ) {
            return 'Type';
        }

        if (str_contains($key, 'name')) {
            return 'Name';
        }

        return null;
    }

    private static function humanizeFieldKey(string $key): string
    {
        $humanized = preg_replace('/[\-_]+/', ' ', $key) ?? $key;

        return ucwords(trim($humanized));
    }

    private static function cleanDisplayLabel(string $label, string $key = ''): string
    {
        $trimmedLabel = trim($label);
        $lowercaseLabel = strtolower($trimmedLabel);

        if (
            $trimmedLabel === '' ||
            str_contains($lowercaseLabel, 'form content[') ||
            str_contains($lowercaseLabel, 'form field')
        ) {
            $resolvedLabel = $key !== '' ? self::labelFor($key) : '';

            return trim($resolvedLabel);
        }

        return $trimmedLabel;
    }

    private static function firstMatchingValue(array $payload, array $labels, array $keys): ?string
    {
        foreach ($payload as $item) {
            $itemLabel = strtolower(trim((string) ($item['label'] ?? '')));
            $itemKey = strtolower(trim((string) ($item['key'] ?? '')));
            $value = trim((string) self::normalizeValue($item['value'] ?? ''));

            if ($value === '') {
                continue;
            }

            foreach ($labels as $label) {
                if ($itemLabel === strtolower($label)) {
                    return $value;
                }
            }

            foreach ($keys as $key) {
                $normalizedKey = strtolower($key);

                if ($itemKey === $normalizedKey || str_contains($itemKey, $normalizedKey)) {
                    return $value;
                }
            }
        }

        return null;
    }
}
