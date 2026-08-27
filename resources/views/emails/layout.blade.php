<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="format-detection" content="telephone=no, date=no, address=no, email=no" />
    <title>@yield('title', 'Notificación — Chambapp')</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style type="text/css">
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        body { height: 100% !important; margin: 0 !important; padding: 0 !important; width: 100% !important; background-color: #f1f5f9; font-family: Arial, Helvetica, sans-serif; color: #1e293b; }
        .wrapper-table { width: 100%; background-color: #f1f5f9; padding: 32px 12px; }
        .main-card { max-width: 580px; width: 100%; margin: 0 auto; background-color: #ffffff; border-radius: 24px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08); }
        .header-bg { background-color: #0f172a; padding: 32px 20px 24px 20px; text-align: center; }
        .header-curve { background-color: #0284c7; height: 6px; width: 100%; }
        .logo-text { font-size: 28px; font-weight: 800; color: #ffffff !important; text-decoration: none; letter-spacing: -0.5px; }
        .logo-accent { color: #38bdf8 !important; }
        .content-cell { padding: 36px 32px 28px 32px; text-align: center; }
        .hero-badge-wrap { text-align: center; margin-bottom: 24px; }
        .hero-badge { display: inline-block; width: 76px; height: 76px; line-height: 76px; border-radius: 50%; background-color: #e0f2fe; text-align: center; font-size: 34px; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.12); }
        .email-title { font-size: 26px; font-weight: 800; color: #0f172a; margin: 0 0 14px 0; line-height: 1.3; text-align: center; }
        .title-accent { color: #0284c7 !important; }
        .email-lead { font-size: 15px; line-height: 1.65; color: #475569; margin: 0 auto 24px auto; max-width: 480px; text-align: center; }
        .info-card { width: 100%; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; margin: 24px 0; border-collapse: separate; text-align: left; }
        .info-card-header { padding: 16px 20px 10px 20px; font-size: 15px; font-weight: 700; color: #0f172a; border-bottom: 1px solid #edf2f7; }
        .info-row td { padding: 12px 20px; font-size: 14px; border-bottom: 1px solid #edf2f7; }
        .info-row:last-child td { border-bottom: none; }
        .info-label { color: #64748b; font-size: 13px; font-weight: 500; vertical-align: top; width: 38%; }
        .info-value { color: #0f172a; font-weight: 700; font-size: 14px; text-align: right; vertical-align: top; }
        .info-highlight { color: #0284c7 !important; }
        .feature-item td { padding: 12px 18px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #334155; }
        .feature-item:last-child td { border-bottom: none; }
        .feature-icon-cell { width: 36px; vertical-align: middle; }
        .feature-icon { display: inline-block; width: 28px; height: 28px; line-height: 28px; border-radius: 50%; background-color: #e0f2fe; color: #0284c7; font-size: 14px; text-align: center; font-weight: bold; }
        .btn-container { padding: 24px 0 20px 0; text-align: center; }
        .btn-main { display: inline-block; background-color: #0284c7; color: #ffffff !important; font-size: 16px; font-weight: 700; text-decoration: none; padding: 15px 36px; border-radius: 12px; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.35); text-align: center; }
        .help-card { width: 100%; background-color: #f0f9ff; border: 1px solid #bae6fd; border-radius: 14px; margin: 20px 0 28px 0; }
        .help-card td { padding: 14px 18px; font-size: 13px; color: #0369a1; text-align: left; }
        .help-card a { color: #0284c7; font-weight: 700; text-decoration: underline; }
        .signoff-box { padding: 16px 0 0 0; text-align: center; font-size: 14px; color: #64748b; line-height: 1.5; }
        .footer-bg { background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 28px 24px; text-align: center; }
        .footer-logo { font-size: 20px; font-weight: 800; color: #0f172a !important; text-decoration: none; }
        .footer-tagline { font-size: 13px; color: #64748b; margin: 6px 0 16px 0; }
        .footer-legal { font-size: 12px; color: #94a3b8; line-height: 1.5; margin: 12px 0 0 0; }
        .social-pill { display: inline-block; width: 32px; height: 32px; line-height: 32px; border-radius: 50%; background-color: #0f172a; color: #ffffff !important; font-size: 14px; text-align: center; margin: 0 4px; text-decoration: none; }
        @media only screen and (max-width: 600px) {
            .content-cell { padding: 26px 18px !important; }
            .email-title { font-size: 22px !important; }
            .btn-main { display: block !important; width: auto !important; padding: 15px 20px !important; }
            .info-row td { padding: 10px 14px !important; }
        }
    </style>
</head>
<body>
    <table class="wrapper-table" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td align="center">
                <table class="main-card" border="0" cellpadding="0" cellspacing="0" width="100%">
                    <!-- HEADER -->
                    <tr>
                        <td class="header-bg">
                            <a href="{{ config('app.url') }}" class="logo-text" target="_blank">
                                🧰 Chamb<span class="logo-accent">app</span>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td class="header-curve"></td>
                    </tr>
                    <!-- BODY -->
                    <tr>
                        <td class="content-cell">
                            <!-- HERO ICON -->
                            <div class="hero-badge-wrap">
                                <span class="hero-badge">@yield('hero_icon', '🔔')</span>
                            </div>

                            @yield('content')

                            <!-- HELP SECTION -->
                            <table class="help-card" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td width="36" align="center" style="font-size: 22px;">🎧</td>
                                    <td>
                                        <strong>¿Necesitas ayuda?</strong><br />
                                        Visita nuestro <a href="{{ route('home') }}" target="_blank">Centro de Ayuda</a> o contáctanos.
                                    </td>
                                </tr>
                            </table>

                            <!-- SIGNOFF -->
                            <div class="signoff-box">
                                Saludos,<br />
                                <strong style="color: #0f172a;">Equipo Chambapp</strong>
                            </div>
                        </td>
                    </tr>
                    <!-- FOOTER -->
                    <tr>
                        <td class="footer-bg">
                            <a href="{{ config('app.url') }}" class="footer-logo" target="_blank">
                                🧰 Chamb<span class="logo-accent">app</span>
                            </a>
                            <p class="footer-tagline">Chambapp México &bull; Tu plataforma para crecer</p>

                            <!-- SOCIAL PILLS -->
                            <div style="margin: 12px 0;">
                                <a href="https://facebook.com" class="social-pill" target="_blank">f</a>
                                <a href="https://instagram.com" class="social-pill" target="_blank">in</a>
                                <a href="https://linkedin.com" class="social-pill" target="_blank">li</a>
                                <a href="https://whatsapp.com" class="social-pill" target="_blank">wa</a>
                            </div>

                            <p class="footer-legal">
                                Este correo fue enviado automáticamente por Chambapp.<br />
                                Por favor, no respondas directamente a este mensaje.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
