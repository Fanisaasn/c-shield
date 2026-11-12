<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subjectLine }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:Arial,Helvetica,sans-serif;color:#1e293b;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f1f5f9;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background-color:#ffffff;border-radius:12px;overflow:hidden;">
                    <tr>
                        <td style="background-color:#0b2545;padding:20px 28px;">
                            <p style="margin:0;font-size:18px;font-weight:bold;color:#ffffff;">C-SHIELD</p>
                            <p style="margin:4px 0 0;font-size:12px;color:#4fc7d6;">Diskominfo Kota Cimahi</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;font-size:14px;line-height:1.7;color:#1e293b;">
                            {{-- Teks pesan dari admin: di-escape dulu, lalu URL dijadikan tautan --}}
                            {!! preg_replace(
                                '~(https?://[^\s<]+)~i',
                                '<a href="$1" style="color:#1a3f73;text-decoration:underline;word-break:break-all;">$1</a>',
                                nl2br(e($body))
                            ) !!}
                        </td>
                    </tr>
                    @if ($meetingLink)
                        <tr>
                            <td align="center" style="padding:0 28px 28px;">
                                <a href="{{ $meetingLink }}" target="_blank"
                                   style="display:inline-block;background-color:#2ab7ca;color:#050d1a;font-size:14px;font-weight:bold;text-decoration:none;padding:12px 28px;border-radius:8px;">
                                    Gabung Webinar
                                </a>
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td style="border-top:1px solid #e2e8f0;padding:16px 28px;font-size:11px;color:#94a3b8;">
                            Email ini dikirim karena Anda terdaftar sebagai peserta webinar
                            &ldquo;{{ $webinar->title }}&rdquo;.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
