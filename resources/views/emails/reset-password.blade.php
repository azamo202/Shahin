<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعادة تعيين كلمة المرور</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f4f8;
            margin: 0;
            padding: 0;
            line-height: 1.8;
            color: #333;
            direction: rtl;
            text-align: right;
        }
        .email-container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .email-header {
            background: linear-gradient(135deg, #3490dc 0%, #2779bd 100%);
            color: #fff;
            text-align: center;
            padding: 25px 20px;
        }
        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        .logo {
            width: 60px;
            height: 60px;
            background-color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .logo-icon {
            color: #3490dc;
            font-size: 28px;
            font-weight: bold;
        }
        .app-name {
            font-size: 26px;
            font-weight: bold;
            letter-spacing: -0.5px;
        }
        .email-body {
            padding: 35px 30px;
            color: #333;
        }
        .greeting {
            font-size: 20px;
            margin-bottom: 20px;
            color: #2d3748;
        }
        .message {
            margin-bottom: 25px;
            font-size: 16px;
            color: #4a5568;
        }
        .action-container {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            padding: 14px 35px;
            background: linear-gradient(135deg, #3490dc 0%, #2779bd 100%);
            color: #fff !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 16px;
            font-family: 'Cairo', sans-serif;
            box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            text-align: center;
        }
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08);
        }
        .note {
            background-color: #f8fafc;
            border-right: 4px solid #3490dc;
            padding: 15px;
            border-radius: 6px;
            margin-top: 25px;
            font-size: 14px;
            color: #4a5568;
        }
        .email-footer {
            background-color: #f0f4f8;
            text-align: center;
            padding: 20px;
            font-size: 13px;
            color: #718096;
            border-top: 1px solid #e2e8f0;
        }
        .footer-links {
            margin-top: 10px;
        }
        .footer-links a {
            color: #3490dc;
            text-decoration: none;
            margin: 0 10px;
        }
        .footer-links a:hover {
            text-decoration: underline;
        }
        @media (max-width: 600px) {
            .email-container {
                margin: 20px auto;
                border-radius: 0;
            }
            .email-body {
                padding: 25px 20px;
            }
            .logo {
                width: 50px;
                height: 50px;
            }
            .logo-icon {
                font-size: 24px;
            }
            .app-name {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <div class="logo-container">
                <div class="logo">
                    <div class="logo-icon">ش</div>
                </div>
                <div class="app-name">شاهين</div>
            </div>
            <div style="font-size: 18px; opacity: 0.9;">إعادة تعيين كلمة المرور</div>
        </div>
        <div class="email-body">
            <div class="greeting">مرحباً {{ $user->name }},</div>
            
            <div class="message">
                <p>لقد تلقينا طلباً لإعادة تعيين كلمة المرور الخاصة بحسابك في منصة شاهين.</p>
                <p>لإكمال عملية إعادة التعيين، يرجى النقر على الزر أدناه:</p>
            </div>
            
            <div class="action-container">
                <a href="{{ $url }}" class="button">إعادة تعيين كلمة المرور</a>
            </div>
            
            <div class="message">
                <p>سيؤدي النقر على الزر أعلاه إلى نقلك إلى صفحة آمنة حيث يمكنك تعيين كلمة مرور جديدة لحسابك.</p>
            </div>
            
            <div class="note">
                <p><strong>ملاحظة:</strong> هذا الرابط صالح لمدة 60 دقيقة فقط. إذا انتهت صلاحية الرابط، يمكنك طلب رابط جديد من خلال صفحة تسجيل الدخول.</p>
                <p>إذا لم تطلب إعادة تعيين كلمة المرور، يمكنك تجاهل هذا البريد الإلكتروني بأمان.</p>
            </div>
        </div>
        <div class="email-footer">
            <div>&copy; {{ date('Y') }} شاهين. جميع الحقوق محفوظة.</div>
            <div class="footer-links">
                <a href="#">الدعم الفني</a>
                <a href="#">سياسة الخصوصية</a>
                <a href="#">الشروط والأحكام</a>
            </div>
        </div>
    </div>
</body>
</html>
