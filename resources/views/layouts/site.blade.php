<!DOCTYPE html>
<html @yield('html_attributes')>
    <head>
    <meta name="robots" content="index, follow">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @yield('head')
        <style>
            .site-form-alert {
                max-width: 720px;
                margin: 20px auto;
                padding: 14px 18px;
                border-radius: 12px;
                border: 1px solid transparent;
                font-family: Arial, Helvetica, sans-serif;
                font-size: 15px;
                line-height: 1.5;
                box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
            }

            .site-form-alert.success {
                background: #ecfdf3;
                color: #166534;
                border-color: #86efac;
            }

            .site-form-alert.empty,
            .site-form-alert.error {
                background: #fef2f2;
                color: #991b1b;
                border-color: #fca5a5;
            }

            .site-inline-form-alert {
                display: none;
                width: 100%;
                margin-top: 14px;
                padding: 12px 16px;
                border-radius: 12px;
                border: 1px solid transparent;
                font-family: Arial, Helvetica, sans-serif;
                font-size: 15px;
                line-height: 1.5;
                box-sizing: border-box;
            }

            .site-inline-form-alert.is-visible {
                display: block;
            }

            .site-inline-form-alert.success {
                background: #ecfdf3;
                color: #166534;
                border-color: #86efac;
            }

            .site-inline-form-alert.empty,
            .site-inline-form-alert.error {
                background: #fef2f2;
                color: #991b1b;
                border-color: #fca5a5;
            }
        </style>
    </head>
    <body @yield('body_attributes')>
        @if (session('form_status'))
            @php
                $formStatus = session('form_status');
                $formMessages = [
                    'success' => 'Your form was submitted successfully.',
                    'empty' => 'Please complete the form before submitting.',
                    'error' => 'Something went wrong while sending your request.',
                ];
                $formMessage = $formMessages[$formStatus] ?? 'Form status updated.';
            @endphp
            <div class="site-form-alert {{ $formStatus }}" role="alert" aria-live="polite">
                {{ $formMessage }}
            </div>
        @endif
        @php
            $pageContent = $__env->yieldContent('content');
            $pageContent = preg_replace_callback(
                '/<form\b[^>]*\bmethod=(["\'])post\1[^>]*>/i',
                static function (array $matches): string {
                    return $matches[0] . PHP_EOL . csrf_field();
                },
                $pageContent
            );
        @endphp
        {!! $pageContent !!}
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                document.querySelectorAll('form[method="post"]').forEach(function (form) {
                    function ensureCsrfToken() {
                        if (!csrfToken || form.querySelector('input[name="_token"]')) {
                            return;
                        }

                        var tokenInput = document.createElement('input');
                        tokenInput.type = 'hidden';
                        tokenInput.name = '_token';
                        tokenInput.value = csrfToken;
                        form.prepend(tokenInput);
                    }

                    function ensureInlineAlert() {
                        var existingAlert = form.querySelector('.site-inline-form-alert');

                        if (existingAlert) {
                            return existingAlert;
                        }

                        var alert = document.createElement('div');
                        alert.className = 'site-inline-form-alert';
                        alert.setAttribute('role', 'status');
                        alert.setAttribute('aria-live', 'polite');

                        var fieldsWrap = form.querySelector('.wpr-form-fields-wrap');
                        if (fieldsWrap && fieldsWrap.parentNode) {
                            fieldsWrap.parentNode.insertBefore(alert, fieldsWrap.nextSibling);
                        } else {
                            form.appendChild(alert);
                        }

                        return alert;
                    }

                    function updateInlineAlert(status, message) {
                        var alert = ensureInlineAlert();
                        alert.className = 'site-inline-form-alert is-visible ' + status;
                        alert.textContent = message;
                    }

                    function markSubmitting() {
                        form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(function (button) {
                            button.disabled = true;
                            button.style.opacity = '0.75';

                            if (button.tagName === 'BUTTON') {
                                var textNode = button.querySelector('span:last-child span') || button.querySelector('span:last-child') || button;
                                if (!button.dataset.originalLabel) {
                                    button.dataset.originalLabel = textNode.textContent.trim();
                                }
                                textNode.textContent = 'WIRD GESENDET...';
                            } else if (!button.dataset.originalLabel) {
                                button.dataset.originalLabel = button.value;
                                button.value = 'WIRD GESENDET...';
                            }
                        });
                    }

                    function submitFormDirectly(event) {
                        if (form.dataset.nativeSubmitting === 'true') {
                            return;
                        }

                        if (event) {
                            event.preventDefault();
                            event.stopImmediatePropagation();
                        }

                        ensureCsrfToken();
                        updateInlineAlert('success', 'Anfrage wird gesendet...');
                        markSubmitting();
                        form.dataset.nativeSubmitting = 'true';
                        HTMLFormElement.prototype.submit.call(form);
                    }

                    ensureCsrfToken();
                    ensureInlineAlert();
                    form.setAttribute('data-direct-post', 'true');
                    form.removeAttribute('onsubmit');

                    form.addEventListener('submit', function (event) {
                        submitFormDirectly(event);
                    }, true);

                    form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(function (button) {
                        button.addEventListener('click', function (event) {
                            // Some builder widgets stop the native submit on click before the form submit event fires.
                            submitFormDirectly(event);
                        }, true);
                    });
                });

                document.querySelectorAll('input.method-input[name="#"]').forEach(function (input) {
                    var label = input.closest('label');
                    var value = label ? label.textContent.replace(/\s+/g, ' ').trim() : 'Contact';

                    input.name = 'preferred_contact_method';
                    input.value = value;
                });

                var fieldMappings = {
                    'wizard_postal-code': 'postal_code',
                    'wizard_location': 'location',
                    'wizard_address': 'address',
                    'wizard_date-from': 'date_from',
                    'wizard_date-to': 'date_to',
                    'current-date-field': 'selected_date',
                    'wizard_name': 'name',
                    'wizard_email': 'email',
                    'wizard_phone': 'phone'
                };

                Object.keys(fieldMappings).forEach(function (id) {
                    var field = document.getElementById(id);

                    if (field && !field.getAttribute('name')) {
                        field.setAttribute('name', fieldMappings[id]);
                    }
                });
            });
        </script>
        @if (session('form_status'))
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var status = @json(session('form_status'));
                    var message = {
                        success: 'Your form was submitted successfully.',
                        empty: 'Please complete the form before submitting.',
                        error: 'Something went wrong while sending your request.'
                    }[status] || 'Form status updated.';

                    document.querySelectorAll('.wpcf7-response-output').forEach(function (output) {
                        output.textContent = message;
                        output.style.display = 'block';
                        output.style.visibility = 'visible';
                        output.style.opacity = '1';
                        output.style.width = '100%';
                        output.style.minHeight = 'auto';
                        output.style.margin = '16px 0 0';
                        output.style.padding = '14px 18px';
                        output.style.borderRadius = '12px';
                        output.style.borderWidth = '1px';
                        output.style.borderStyle = 'solid';
                        output.style.fontSize = '15px';
                        output.style.lineHeight = '1.5';
                        output.style.boxSizing = 'border-box';
                        output.style.backgroundColor = status === 'success' ? '#ecfdf3' : '#fef2f2';
                        output.style.borderColor = status === 'success' ? '#86efac' : '#fca5a5';
                        output.style.color = status === 'success' ? '#166534' : '#991b1b';
                    });

                    document.querySelectorAll('form.wpr-form, form[method="post"]').forEach(function (form) {
                        var inlineAlert = form.querySelector('.site-inline-form-alert');
                        if (!inlineAlert) {
                            return;
                        }

                        inlineAlert.className = 'site-inline-form-alert is-visible ' + status;
                        inlineAlert.textContent = message;
                    });

                    var firstInlineAlert = document.querySelector('.site-inline-form-alert.is-visible');
                    if (firstInlineAlert) {
                        firstInlineAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                });
            </script>
        @endif
    </body>
</html>
