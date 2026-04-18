@php
    $title = 'New Hero Lead';
    $intro = 'A homepage lead was submitted. The captured contact details are below.';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #eef4ff; font-family: Arial, Helvetica, sans-serif; color: #132238;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #eef4ff; margin: 0; padding: 28px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 760px; background-color: #ffffff; border-radius: 22px; overflow: hidden; box-shadow: 0 18px 45px rgba(24, 66, 132, 0.12);">
                    <tr>
                        <td style="background: linear-gradient(135deg, #0d2b4d 0%, #1185ea 100%); padding: 34px 36px; color: #ffffff;">
                            <p style="margin: 0 0 8px; font-size: 12px; letter-spacing: 1.6px; text-transform: uppercase; color: #d7e9ff;">ast Media</p>
                            <h1 style="margin: 0; font-size: 29px; line-height: 1.2; font-weight: 700;">{{ $title }}</h1>
                            <p style="margin: 12px 0 0; font-size: 15px; line-height: 1.7; color: #eaf4ff;">{{ $intro }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 28px 36px 20px;">
                            <div style="border: 1px solid #dbe8f8; border-radius: 18px; overflow: hidden;">
                                <div style="padding: 14px 18px; background-color: #eff6ff; border-bottom: 1px solid #dbe8f8; font-size: 13px; font-weight: 700; letter-spacing: 0.8px; text-transform: uppercase; color: #1174cf;">Contact Details</div>
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse;">
                                    @if (!empty($fields['name']))
                                        <tr>
                                            <td valign="top" style="width: 240px; padding: 16px 18px; border-bottom: 1px solid #e7eef8; background-color: #fbfdff; font-size: 14px; font-weight: 700; color: #102746;">Name</td>
                                            <td valign="top" style="padding: 16px 18px; border-bottom: 1px solid #e7eef8; font-size: 14px; line-height: 1.7; color: #334155;">{{ $fields['name'] }}</td>
                                        </tr>
                                    @endif
                                    @if (!empty($fields['phone']))
                                        <tr>
                                            <td valign="top" style="width: 240px; padding: 16px 18px; background-color: #fbfdff; font-size: 14px; font-weight: 700; color: #102746;">Phone Number</td>
                                            <td valign="top" style="padding: 16px 18px; font-size: 14px; line-height: 1.7; color: #334155;">{{ $fields['phone'] }}</td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
