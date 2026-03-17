<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Confirm Your Email Before Sign Up</title>
    </head>
    <body>
        <p>Hello,</p>
        <p>Confirm this email address before creating your account.</p>
        <p><a href="{{ $verificationUrl }}">{{ $verificationUrl }}</a></p>
        <p>This link will expire in 30 minutes.</p>
        <p>Email: {{ $email }}</p>
    </body>
</html>
