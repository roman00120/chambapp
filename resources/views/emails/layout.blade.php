<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Notificación de Chambapp')</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            width: 100%;
            background-color: #f8fafc;
            padding: 32px 16px;
        }
        .container {
            max-width: 580px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .header {
            background-color: #0f172a;
            padding: 28px 32px;
            text-align: center;
        }
        .logo {
            font-size: 24px;
            font-weight: 800;
            color: #ffffff;
            text-decoration: none;
            letter-spacing: -0.5px;
        }
        .logo span {
            color: #38bdf8;
        }
        .content {
            padding: 32px;
        }
        h1 {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 0;
            margin-bottom: 16px;
            line-height: 1.3;
        }
        p {
            font-size: 15px;
            line-height: 1.6;
            color: #475569;
            margin-top: 0;
            margin-bottom: 16px;
        }
        .card {
            background-color: #f1f5f9;
            border-radius: 12px;
            padding: 20px;
            margin: 24px 0;
            border: 1px solid #e2e8f0;
        }
        .card-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 14px;
        }
        .card-label {
            color: #64748b;
            font-weight: 500;
        }
        .card-value {
            color: #0f172a;
            font-weight: 600;
            text-align: right;
        }
        .divider {
            border-top: 1px dashed #cbd5e1;
            margin: 12px 0;
        }
        .total-row {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
        }
        .total-row .card-value {
            color: #0284c7;
        }
        .button-wrapper {
            text-align: center;
            margin: 32px 0 16px;
        }
        .button {
            display: inline-block;
            background-color: #0284c7;
            color: #ffffff !important;
            font-weight: 600;
            font-size: 15px;
            text-decoration: none;
            padding: 12px 28px;
            border-radius: 10px;
            text-align: center;
        }
        .footer {
            background-color: #f8fafc;
            padding: 24px 32px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        .footer p {
            font-size: 12px;
            color: #94a3b8;
            margin-bottom: 8px;
        }
        .footer a {
            color: #64748b;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <a href="{{ config('app.url', 'https://chambapp.com.mx') }}" class="logo">
                    Chamb<span>app</span>
                </a>
            </div>
            <div class="content">
                @yield('content')
            </div>
            <div class="footer">
                <p>Este es un correo automático enviado por Chambapp. Por favor, no respondas directamente a este mensaje.</p>
                <p>&copy; {{ date('Y') }} Chambapp. Todos los derechos reservados.</p>
            </div>
        </div>
    </div>
</body>
</html>
