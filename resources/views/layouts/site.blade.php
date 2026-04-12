<!DOCTYPE html>
<html @yield('html_attributes')>
    <head>
        <meta name="robots" content="noindex, follow">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @yield('head')
    </head>
    <body @yield('body_attributes')>
        @yield('content')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                document.querySelectorAll('form[method="post"]').forEach(function (form) {
                    if (csrfToken && !form.querySelector('input[name="_token"]')) {
                        var tokenInput = document.createElement('input');
                        tokenInput.type = 'hidden';
                        tokenInput.name = '_token';
                        tokenInput.value = csrfToken;
                        form.prepend(tokenInput);
                    }
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
                    });
                });
            </script>
        @endif
    </body>
</html>
