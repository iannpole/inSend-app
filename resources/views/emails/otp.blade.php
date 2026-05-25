<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Verifikasi Email InSend App</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            padding: 20px 40px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
        }
        .header {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
        }
        .otp-code {
            font-size: 36px;
            font-weight: bold;
            letter-spacing: 5px;
            color: #FF5A5F;
            background: #f9f9f9;
            padding: 15px 30px;
            display: inline-block;
            border-radius: 8px;
            margin: 20px 0;
        }
        .message {
            font-size: 16px;
            color: #555;
            line-height: 1.5;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">Halo, {{ $userName }}!</div>
        <div class="message">
            Terima kasih telah mendaftar di InSend App.<br>
            Untuk memverifikasi email Anda dan menyelesaikan pendaftaran, silakan masukkan kode OTP berikut:
        </div>
        
        <div class="otp-code">{{ $otpCode }}</div>
        
        <div class="message">
            Kode ini berlaku selama 10 menit. Jangan berikan kode ini kepada siapa pun.
        </div>
        
        <div class="footer">
            Jika Anda tidak merasa melakukan pendaftaran ini, silakan abaikan email ini.<br>
            &copy; {{ date('Y') }} InSend App. All rights reserved.
        </div>
    </div>
</body>
</html>
