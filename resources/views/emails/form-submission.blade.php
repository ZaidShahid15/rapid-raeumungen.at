<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Website Form Submission</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f7fb; font-family: Arial, Helvetica, sans-serif; color: #1f2937;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f4f7fb; margin: 0; padding: 24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 720px; background-color: #ffffff; border-radius: 18px; overflow: hidden; box-shadow: 0 12px 35px rgba(15, 23, 42, 0.08);">
                    <tr>
                        <td style="background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 100%); padding: 32px 36px; color: #ffffff;">
                            <p style="margin: 0 0 8px; font-size: 12px; letter-spacing: 1.4px; text-transform: uppercase; color: #bfdbfe;">
                                Rapid Raeumungen
                            </p>
                            <h1 style="margin: 0; font-size: 28px; line-height: 1.2; font-weight: 700;">
                                New Website Form Submission
                            </h1>
                            <p style="margin: 12px 0 0; font-size: 15px; line-height: 1.7; color: #dbeafe;">
                                A new enquiry has been submitted through the website and is ready for review.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 28px 36px 10px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding: 0 12px 16px 0; width: 50%;">
                                        <div style="background-color: #f8fafc; border: 1px solid #e5e7eb; border-radius: 14px; padding: 16px 18px;">
                                            <p style="margin: 0 0 6px; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: #64748b;">Page</p>
                                            <p style="margin: 0; font-size: 15px; font-weight: 600; color: #0f172a;">{{ $pagePath }}</p>
                                        </div>
                                    </td>
                                    <td style="padding: 0 0 16px 12px; width: 50%;">
                                        <div style="background-color: #f8fafc; border: 1px solid #e5e7eb; border-radius: 14px; padding: 16px 18px;">
                                            <p style="margin: 0 0 6px; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: #64748b;">Submitted</p>
                                            <p style="margin: 0; font-size: 15px; font-weight: 600; color: #0f172a;">{{ $submittedAt }}</p>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="padding: 0 0 18px;">
                                        <div style="background-color: #f8fafc; border: 1px solid #e5e7eb; border-radius: 14px; padding: 16px 18px;">
                                            <p style="margin: 0 0 6px; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: #64748b;">IP Address</p>
                                            <p style="margin: 0; font-size: 15px; font-weight: 600; color: #0f172a;">{{ $ipAddress }}</p>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0 36px 36px;">
                            <div style="border: 1px solid #e5e7eb; border-radius: 18px; overflow: hidden;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse;">
                                    <thead>
                                        <tr style="background-color: #eff6ff;">
                                            <th align="left" style="padding: 16px 18px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.8px; color: #1d4ed8; border-bottom: 1px solid #dbeafe;">
                                                Field
                                            </th>
                                            <th align="left" style="padding: 16px 18px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.8px; color: #1d4ed8; border-bottom: 1px solid #dbeafe;">
                                                Details
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($payload as $item)
                                            <tr>
                                                <td valign="top" style="width: 220px; padding: 16px 18px; border-bottom: 1px solid #e5e7eb; background-color: #fcfdff; font-size: 14px; font-weight: 700; color: #0f172a;">
                                                    {{ $item['label'] }}
                                                </td>
                                                <td valign="top" style="padding: 16px 18px; border-bottom: 1px solid #e5e7eb; font-size: 14px; line-height: 1.7; color: #334155;">
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
                            <p style="margin: 0; font-size: 13px; line-height: 1.7; color: #64748b;">
                                This message was generated automatically from the website contact form.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
