<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapid Raeumungen Lead</title>
</head>
<body style="margin: 0; padding: 0; background-color: #eef4ff; font-family: Arial, Helvetica, sans-serif; color: #132238;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #eef4ff; margin: 0; padding: 28px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 760px; background-color: #ffffff; border-radius: 22px; overflow: hidden; box-shadow: 0 18px 45px rgba(24, 66, 132, 0.12);">
                    <tr>
                        <td style="background: linear-gradient(135deg, #0d2b4d 0%, #1185ea 100%); padding: 34px 36px; color: #ffffff;">
                            <p style="margin: 0 0 8px; font-size: 12px; letter-spacing: 1.6px; text-transform: uppercase; color: #d7e9ff;">
                                Rapid Raeumungen
                            </p>
                            <h1 style="margin: 0; font-size: 29px; line-height: 1.2; font-weight: 700;">
                                New Form Submission
                            </h1>
                            <p style="margin: 12px 0 0; font-size: 15px; line-height: 1.7; color: #eaf4ff;">
                                A visitor submitted a form on the website. Their details are included below.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 28px 36px 12px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding: 0 12px 16px 0; width: 50%;">
                                        <div style="background-color: #f8fbff; border: 1px solid #dbe8f8; border-radius: 16px; padding: 16px 18px;">
                                            <p style="margin: 0 0 6px; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: #5f7391;">Page</p>
                                            <p style="margin: 0; font-size: 15px; font-weight: 700; color: #102746;">{{ $pagePath }}</p>
                                        </div>
                                    </td>
                                    <td style="padding: 0 0 16px 12px; width: 50%;">
                                        <div style="background-color: #f8fbff; border: 1px solid #dbe8f8; border-radius: 16px; padding: 16px 18px;">
                                            <p style="margin: 0 0 6px; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: #5f7391;">Submitted</p>
                                            <p style="margin: 0; font-size: 15px; font-weight: 700; color: #102746;">{{ $submittedAt }}</p>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="padding: 0 0 18px;">
                                        <div style="background-color: #f8fbff; border: 1px solid #dbe8f8; border-radius: 16px; padding: 16px 18px;">
                                            <p style="margin: 0 0 6px; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: #5f7391;">IP Address</p>
                                            <p style="margin: 0; font-size: 15px; font-weight: 700; color: #102746;">{{ $ipAddress }}</p>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0 36px 36px;">
                            <div style="border: 1px solid #dbe8f8; border-radius: 18px; overflow: hidden;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse;">
                                    <thead>
                                        <tr style="background-color: #eff6ff;">
                                            <th align="left" style="padding: 16px 18px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.8px; color: #1174cf; border-bottom: 1px solid #dbe8f8;">
                                                Field
                                            </th>
                                            <th align="left" style="padding: 16px 18px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.8px; color: #1174cf; border-bottom: 1px solid #dbe8f8;">
                                                Value
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($payload as $item)
                                            <tr>
                                                <td valign="top" style="width: 220px; padding: 16px 18px; border-bottom: 1px solid #e7eef8; background-color: #fbfdff; font-size: 14px; font-weight: 700; color: #102746;">
                                                    {{ $item['label'] }}
                                                </td>
                                                <td valign="top" style="padding: 16px 18px; border-bottom: 1px solid #e7eef8; font-size: 14px; line-height: 1.7; color: #334155;">
                                                    {!! nl2br(e($item['value'])) !!}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0 36px 30px;">
                            <p style="margin: 0; font-size: 13px; line-height: 1.7; color: #60748f;">
                                This email was sent automatically from the Rapid Raeumungen website form handler.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
