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
        body { height: 100% !important; margin: 0 !important; padding: 0 !important; width: 100% !important; background-color: #fffaf0; font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; color: #3b2415; }
        .wrapper-table { width: 100%; background-color: #fffaf0; padding: 32px 12px; }
        .main-card { max-width: 580px; width: 100%; margin: 0 auto; background-color: #ffffff; border-radius: 20px; overflow: hidden; border: 1px solid #f0dcc5; box-shadow: 0 8px 24px rgba(154, 52, 18, 0.08); }
        .brand-top-bar { height: 5px; width: 100%; background-color: #f97316; }
        .header-bg { background-color: #fffaf0; padding: 28px 20px 22px 20px; text-align: center; border-bottom: 1px solid #f0dcc5; }
        .logo-img { display: inline-block; max-width: 130px; height: auto; vertical-align: middle; }
        .logo-text { font-size: 26px; font-weight: 800; color: #3b2415 !important; text-decoration: none; letter-spacing: -0.5px; vertical-align: middle; display: inline-block; }
        .logo-accent { color: #f97316 !important; }
        .content-cell { padding: 34px 30px 26px 30px; text-align: center; }
        .hero-badge-wrap { text-align: center; margin-bottom: 22px; }
        .hero-badge { display: inline-block; width: 72px; height: 72px; line-height: 72px; border-radius: 50%; background-color: #fff1cc; border: 2px solid #fed7aa; text-align: center; font-size: 32px; box-shadow: 0 4px 14px rgba(249, 115, 22, 0.12); }
        .email-title { font-size: 24px; font-weight: 800; color: #3b2415; margin: 0 0 12px 0; line-height: 1.3; text-align: center; letter-spacing: -0.4px; }
        .title-accent { color: #f97316 !important; }
        .email-lead { font-size: 15px; line-height: 1.6; color: #786252; margin: 0 auto 22px auto; max-width: 480px; text-align: center; }
        .info-card { width: 100%; background-color: #fffaf0; border: 1px solid #f0dcc5; border-radius: 14px; margin: 22px 0; border-collapse: separate; text-align: left; overflow: hidden; }
        .info-card-header { padding: 14px 18px 10px 18px; font-size: 14px; font-weight: 800; color: #3b2415; border-bottom: 1px solid #f0dcc5; letter-spacing: -0.2px; }
        .info-row td { padding: 11px 18px; font-size: 14px; border-bottom: 1px solid #f5e8d9; }
        .info-row:last-child td { border-bottom: none; }
        .info-label { color: #786252; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; vertical-align: top; width: 38%; }
        .info-value { color: #3b2415; font-weight: 700; font-size: 14px; text-align: right; vertical-align: top; }
        .info-highlight { color: #f97316 !important; font-weight: 800; }
        .feature-item td { padding: 11px 16px; border-bottom: 1px solid #f5e8d9; font-size: 14px; color: #3b2415; }
        .feature-item:last-child td { border-bottom: none; }
        .feature-icon-cell { width: 34px; vertical-align: middle; }
        .feature-icon { display: inline-block; width: 26px; height: 26px; line-height: 26px; border-radius: 50%; background-color: #fff1cc; color: #c2410c; font-size: 13px; text-align: center; font-weight: 800; }
        .btn-container { padding: 22px 0 18px 0; text-align: center; }
        .btn-main { display: inline-block; background-color: #f97316; color: #ffffff !important; font-size: 15px; font-weight: 700; text-decoration: none; padding: 14px 34px; border-radius: 10px; box-shadow: 0 4px 14px rgba(249, 115, 22, 0.28); text-align: center; }
        .callout-box { background-color: #fffaf0; border-left: 4px solid #f97316; border-radius: 8px; padding: 13px 16px; margin: 18px 0; text-align: left; font-size: 14px; color: #786252; font-style: italic; border-top: 1px solid #f0dcc5; border-right: 1px solid #f0dcc5; border-bottom: 1px solid #f0dcc5; }
        .help-card { width: 100%; background-color: #fff7ed; border: 1px solid #fed7aa; border-radius: 12px; margin: 20px 0 24px 0; }
        .help-card td { padding: 13px 16px; font-size: 13px; color: #9a3412; text-align: left; }
        .help-card a { color: #f97316; font-weight: 700; text-decoration: underline; }
        .signoff-box { padding: 14px 0 0 0; text-align: center; font-size: 14px; color: #786252; line-height: 1.5; }
        .footer-bg { background-color: #fffaf0; border-top: 1px solid #f0dcc5; padding: 26px 20px; text-align: center; }
        .footer-logo { font-size: 20px; font-weight: 800; color: #3b2415 !important; text-decoration: none; }
        .footer-tagline { font-size: 13px; color: #786252; margin: 6px 0 10px 0; }
        .footer-trust { font-size: 12px; font-weight: 700; color: #2d8a62; margin: 0 0 14px 0; }
        .footer-legal { font-size: 12px; color: #786252; line-height: 1.5; margin: 10px 0 0 0; }
        .social-pill { display: inline-block; width: 30px; height: 30px; line-height: 30px; border-radius: 50%; background-color: #3b2415; color: #ffffff !important; font-size: 13px; text-align: center; margin: 0 3px; text-decoration: none; }
        @media only screen and (max-width: 600px) {
            .wrapper-table { padding: 16px 8px !important; }
            .content-cell { padding: 24px 16px !important; }
            .email-title { font-size: 21px !important; }
            .btn-main { display: block !important; width: auto !important; padding: 14px 18px !important; }
            .info-row td { padding: 9px 12px !important; }
        }
    </style>
</head>
<body>
    <table class="wrapper-table" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td align="center">
                <table class="main-card" border="0" cellpadding="0" cellspacing="0" width="100%">
                    <!-- BRAND ACCENT BAR -->
                    <tr>
                        <td class="brand-top-bar"></td>
                    </tr>
                    <!-- HEADER -->
                    <tr>
                        <td class="header-bg">
                            <a href="{{ config('app.url') }}" class="logo-text" target="_blank">
                                <img src="{{ config('app.url') }}/images/chambapp-logo.png" alt="Chambapp" class="logo-img" style="vertical-align: middle; margin-right: 6px;" height="36" />
                                Chamb<span class="logo-accent">app</span>
                            </a>
                        </td>
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
                                <strong style="color: #3b2415;">Equipo Chambapp</strong>
                            </div>
                        </td>
                    </tr>
                    <!-- FOOTER -->
                    <tr>
                        <td class="footer-bg">
                            <a href="{{ config('app.url') }}" class="footer-logo" target="_blank">
                                Chamb<span class="logo-accent">app</span>
                            </a>
                            <p class="footer-tagline">Chambapp México &bull; Tu plataforma para crecer</p>
                            <p class="footer-trust">🛡️ Pagos 100% protegidos en custodia</p>

                            <!-- SOCIAL PILLS -->
                            <div style="margin: 10px 0;">
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
