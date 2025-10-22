<!doctype html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <title>تأكيد البريد الإلكتروني - شاهين</title>
    <style>
        /* الأساسيات */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', 'Arial', 'Helvetica', 'Tahoma', sans-serif;
            background: #f8f9fa;
            margin: 0;
            padding: 20px;
            line-height: 1.6;
            color: #333;
            -webkit-font-smoothing: antialiased;
        }

        body,
        .email-wrap {
            direction: rtl !important;
            text-align: right !important;
        }


        .email-wrap {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            border: 1px solid #e0e0e0;
        }

        /* الهيدر */
        .header {
            padding: 30px 20px;
            text-align: center;
            background: #ffffff;
            border-bottom: 1px solid #eaeaea;
        }

        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 10px;
        }

        .logo-img {
            max-height: 50px;
            max-width: 180px;
            height: auto;
        }

        .site-name {
            font-size: 24px;
            font-weight: 700;
            color: #2c3e50;
            letter-spacing: -0.5px;
        }

        .header-tagline {
            font-size: 16px;
            color: #7f8c8d;
            margin-top: 5px;
            font-weight: 500;
        }

        /* المحتوى */
        .content {
            padding: 35px 30px;
            color: #333;
            line-height: 1.7;
        }

        .greeting {
            font-size: 20px;
            margin-bottom: 15px;
            color: #2c3e50;
            font-weight: 600;
        }

        .lead {
            color: #555;
            margin-bottom: 25px;
            font-size: 16px;
        }

        .highlight {
            color: #2c3e50;
            font-weight: 600;
        }

        /* زر التأكيد */
        .btn-wrap {
            text-align: center;
            margin: 30px 0;
        }

        .btn {
            display: inline-block;
            padding: 12px 32px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: #2c3e50;
            color: #fff;
            border: 1px solid #2c3e50;
        }

        .btn-primary:hover {
            background: #34495e;
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
        }

        /* الرابط البديل */
        .note {
            font-size: 14px;
            color: #666;
            margin-top: 25px;
            border-top: 1px solid #eee;
            padding-top: 18px;
        }

        .link-container {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 12px 15px;
            margin-top: 10px;
            word-break: break-all;
            border: 1px solid #eaeaea;
            direction: ltr;
            text-align: left;
        }

        a.link {
            color: #2980b9;
            text-decoration: none;
            font-weight: 500;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }

        a.link:hover {
            text-decoration: underline;
        }

        .warning {
            background: #fff8e1;
            border-right: 4px solid #ffc107;
            padding: 12px 15px;
            margin: 20px 0;
            border-radius: 4px;
            font-size: 14px;
        }

        /* الفوتر */
        .footer {
            text-align: center;
            padding: 25px;
            font-size: 14px;
            color: #7f8c8d;
            background: #f8f9fa;
            border-top: 1px solid #eaeaea;
        }

        .footer a {
            color: #2c3e50;
            text-decoration: none;
            margin: 0 8px;
            font-weight: 500;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        .copyright {
            margin-top: 15px;
            font-size: 13px;
            color: #95a5a6;
        }

        /* تصميم متجاوب */
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }

            .email-wrap {
                margin: 10px auto;
                border-radius: 6px;
            }

            .header {
                padding: 25px 15px;
            }

            .logo-container {
                flex-direction: column;
                gap: 10px;
            }

            .site-name {
                font-size: 22px;
            }

            .content {
                padding: 25px 20px;
            }

            .greeting {
                font-size: 18px;
            }

            .btn {
                padding: 10px 25px;
                font-size: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="email-wrap" role="article" style="direction: rtl; text-align: right;">
        <div class="header">
            <div class="logo-container">
                <img src="{{ url('images/logo.jpg') }}" alt="شاهين" class="logo-img">
                <div class="site-name">شاهين</div>
            </div>
            <div class="header-tagline">منصتك الموثوقة للحلول المتكاملة</div>
        </div>

        <div class="content">
            <div class="greeting">مرحباً {{ $user->name ?? 'صديقنا' }},</div>
            <div class="lead">
                نشكرك على تسجيلك في منصة <span class="highlight">شاهين</span>.
                لتأكيد بريدك الإلكتروني والتمتع بكامل مزايا الحساب، يرجى الضغط على الزر أدناه.
            </div>

            <div class="warning">
                <strong>تنبيه:</strong> يرجى التأكد من أن هذا البريد الإلكتروني موجه إليك قبل المتابعة.
            </div>

            <div class="btn-wrap">
                <a href="{{ $url }}" class="btn btn-primary" target="_blank" rel="noopener">تأكيد البريد
                    الإلكتروني</a>
            </div>

            <div class="note">
                <p>إذا لم يعمل الزر أعلاه، يمكنك نسخ الرابط التالي ولصقه في متصفح الويب:</p>
                <div class="link-container">
                    <a class="link" href="{{ $url }}">{{ $url }}</a>
                </div>
            </div>

            <div class="note">
                <strong>معلومة هامة:</strong> هذا الرابط صالح لمدة 60 دقيقة فقط.
                في حال انتهاء المدة، يمكنك طلب رابط جديد من خلال صفحة إعدادات حسابك.
            </div>
        </div>

        <div class="footer">
            <div>
                <a href="#">الرئيسية</a> |
                <a href="#">اتصل بنا</a> |
                <a href="#">الدعم الفني</a>
            </div>
            <div class="copyright">
                تحياتنا — فريق عمل <strong>شاهين</strong><br>
                © {{ date('Y') }} جميع الحقوق محفوظة
            </div>
        </div>
    </div>
</body>

</html>
