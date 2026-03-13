
<!DOCTYPE html>
<html>
<head>
    <title>Password Reset</title>
</head>
<body>
    <h2>Reset Your uLife Password</h2>
    <p>Click the button below to reset your password:</p>
    <a href="{{ $resetUrl }}" style="background: #00ffcc; color: #000; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold;">Reset Password</a>
    <p>Or use this link directly: <a href="{{ $resetUrl }}">{{ $resetUrl }}</a></p>
    <p>This link expires in 24 hours.</p>
    <hr>
    <small>If you didn't request this, ignore this email.</small>
</body>
</html>

