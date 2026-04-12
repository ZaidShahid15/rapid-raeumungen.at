<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Website Form Submission</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.6;">
    <h2 style="margin-bottom: 8px;">New Website Form Submission</h2>
    <p style="margin: 0 0 16px;">
        <strong>Page:</strong> {{ $pagePath }}<br>
        <strong>Submitted:</strong> {{ $submittedAt }}<br>
        <strong>IP Address:</strong> {{ $ipAddress }}
    </p>

    <table cellpadding="8" cellspacing="0" border="1" style="border-collapse: collapse; width: 100%; max-width: 720px;">
        <thead>
            <tr style="background: #f3f4f6;">
                <th align="left">Field</th>
                <th align="left">Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($payload as $item)
                <tr>
                    <td style="width: 220px;"><strong>{{ $item['label'] }}</strong></td>
                    <td>{!! nl2br(e($item['value'])) !!}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
