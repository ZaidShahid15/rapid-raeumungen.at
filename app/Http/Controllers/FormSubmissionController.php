<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class FormSubmissionController extends Controller
{
    private const RECIPIENT_EMAIL = 'karthaus.media@gmail.com';

    public function submit(Request $request, ?string $path = null): RedirectResponse
    {
        $payload = $this->extractPayload($request->except('_token'));

        if ($payload === []) {
            return back()->with('form_status', 'empty');
        }

        $pagePath = '/' . ltrim($path ?? '', '/');
        $pagePath = $pagePath === '/' ? '/' : rtrim($pagePath, '/');

        try {
            Mail::send('emails.form-submission', [
                'submittedAt' => now(),
                'pagePath' => $pagePath,
                'ipAddress' => $request->ip(),
                'payload' => $payload,
            ], function ($message) use ($payload, $pagePath) {
                $replyTo = $this->findReplyTo($payload);

                $message
                    ->from(self::RECIPIENT_EMAIL, 'Rapid Raeumungen')
                    ->to(self::RECIPIENT_EMAIL)
                    ->subject($this->buildSubject($pagePath, $payload));

                if ($replyTo !== null) {
                    $message->replyTo($replyTo);
                }
            });

            return back()->with('form_status', 'success');
        } catch (\Throwable $exception) {
            Log::error('Form submission mail failed.', [
                'page' => $pagePath,
                'error' => $exception->getMessage(),
            ]);

            return back()->with('form_status', 'error');
        }
    }

    private function extractPayload(array $input): array
    {
        $payload = [];

        foreach ($input as $key => $value) {
            $key = (string) $key;

            if (
                $key === '_token' ||
                str_starts_with($key, '_wpcf7') ||
                str_starts_with($key, '_wp') ||
                $key === 'g-recaptcha-response'
            ) {
                continue;
            }

            $normalizedValue = $this->normalizeValue($value);

            if ($normalizedValue === null || $normalizedValue === '') {
                continue;
            }

            $payload[] = [
                'key' => $key,
                'label' => $this->labelFor($key),
                'value' => $normalizedValue,
            ];
        }

        return $payload;
    }

    private function findReplyTo(array $payload): ?string
    {
        foreach ($payload as $item) {
            $value = is_string($item['value']) ? trim($item['value']) : null;

            if ($value !== null && filter_var($value, FILTER_VALIDATE_EMAIL)) {
                return $value;
            }
        }

        return null;
    }

    private function buildSubject(string $pagePath, array $payload): string
    {
        $replyTo = $this->findReplyTo($payload);
        $source = $pagePath === '/' ? 'homepage' : ltrim($pagePath, '/');

        return $replyTo !== null
            ? "New website enquiry from {$source} ({$replyTo})"
            : "New website enquiry from {$source}";
    }

    private function normalizeValue(mixed $value): ?string
    {
        if (is_array($value)) {
            $parts = array_filter(array_map(
                fn (mixed $item) => $this->normalizeValue($item),
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

        return $string === '' ? null : $string;
    }

    private function labelFor(string $key): string
    {
        $labels = [
            'search-field' => 'Email',
            'text-475' => 'Name',
            'email-621' => 'Email',
            'text-476' => 'Affiliation',
            'text-477' => 'Phone / Number',
            'text-478' => 'Inquiry Department',
            'text-479' => 'Preferred Date',
            'textarea-527' => 'Message',
            'text-71' => 'Name',
            'email-631' => 'Email',
            'text-72' => 'Website',
            'textarea-233' => 'Question',
            'shiping-cun' => 'Shipping From',
            'shiping-to' => 'Shipping To',
            'shiping-date' => 'Shipping Date',
            'shiping-type' => 'Shipping Type',
            'msg-tm-478' => 'Shipment Details',
            'mail-aff' => 'Email',
            'sh-name' => 'Name',
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
        ];

        return $labels[$key] ?? ucwords(str_replace(['-', '_'], ' ', $key));
    }
}
