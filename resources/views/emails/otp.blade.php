<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>رمز التحقق</title>
</head>
<body>
    <h2>مرحباً {{ $userName }}</h2>
    <p>شكراً لتسجيلك في نظام Blood Finder.</p>
    <p>رمز التحقق الخاص بك هو:</p>

    <h1 style="text-align:center; letter-spacing:4px;">
        {{ $code }}
    </h1>

    <p>الرمز صالح لفترة محدودة، يرجى إدخاله في التطبيق لإكمال تفعيل الحساب.</p>
</body>
</html>
