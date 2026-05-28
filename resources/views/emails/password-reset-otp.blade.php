<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
</head>
<body style="margin:0; padding:0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f5f5f5;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto; background-color: #ffffff;">
        <tr>
            <td style="background: linear-gradient(135deg, #00473B 0%, #006B57 100%); padding: 40px 30px; text-align: center;">
                <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: 700;">InSend</h1>
                <p style="color: #a7d5ca; margin: 8px 0 0; font-size: 14px;">Reset Password</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 40px 30px;">
                <h2 style="color: #1a1a1a; margin: 0 0 16px; font-size: 22px;">Halo, {{ $userName }}!</h2>
                <p style="color: #555; font-size: 15px; line-height: 1.6; margin: 0 0 24px;">
                    Kami menerima permintaan untuk mereset password akun InSend Anda. Gunakan kode OTP di bawah ini untuk melanjutkan:
                </p>
                <div style="background: linear-gradient(135deg, #f0faf7 0%, #e6f5f0 100%); border: 2px dashed #00473B; border-radius: 12px; padding: 24px; text-align: center; margin: 0 0 24px;">
                    <p style="color: #888; font-size: 12px; margin: 0 0 8px; text-transform: uppercase; letter-spacing: 2px;">Kode Reset Password</p>
                    <p style="color: #00473B; font-size: 36px; font-weight: 800; letter-spacing: 8px; margin: 0;">{{ $otpCode }}</p>
                </div>
                <p style="color: #888; font-size: 13px; line-height: 1.5; margin: 0 0 8px;">
                    ⏱ Kode ini berlaku selama <strong>15 menit</strong>.
                </p>
                <p style="color: #888; font-size: 13px; line-height: 1.5; margin: 0;">
                    🔒 Jika Anda tidak meminta reset password, abaikan email ini. Password Anda tidak akan berubah.
                </p>
            </td>
        </tr>
        <tr>
            <td style="background-color: #f9f9f9; padding: 24px 30px; text-align: center; border-top: 1px solid #eee;">
                <p style="color: #999; font-size: 12px; margin: 0;">
                    &copy; {{ date('Y') }} InSend App. All rights reserved.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
