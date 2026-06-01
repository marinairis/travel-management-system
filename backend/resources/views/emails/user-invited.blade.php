<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #E0E1DD;
        }
        .email-container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(27, 38, 59, 0.1);
        }
        .email-header {
            background-color: #1B263B;
            color: #E0E1DD;
            padding: 32px;
            text-align: center;
        }
        .email-header h1 {
            margin: 16px 0 0 0;
            font-size: 24px;
            font-weight: 600;
            color: #E0E1DD;
        }
        .email-body {
            padding: 32px;
            color: #415A77;
            line-height: 1.6;
        }
        .email-body p {
            margin: 0 0 16px 0;
        }
        .email-button {
            display: inline-block;
            background-color: #1B263B;
            color: #E0E1DD !important;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 6px;
            font-weight: 600;
            margin: 20px 0;
            text-align: center;
        }
        .email-button:hover {
            background-color: #415A77;
        }
        .email-divider {
            border: none;
            border-top: 1px solid #E0E1DD;
            margin: 28px 0;
        }
        .email-secondary {
            color: #778DA9;
            font-size: 13px;
        }
        .email-footer {
            background-color: #778DA9;
            padding: 20px 32px;
            text-align: center;
            color: #1B263B;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>Voa - Viagens Corporativas</h1>
        </div>
        <div class="email-body">
            <p>Olá!</p>
            <p>Você foi convidado para acessar o Voa - Viagens Corporativas.</p>
            <p>Clique no botão abaixo para criar sua conta e definir sua senha.</p>
            <div style="text-align: center;">
                <a href="{{ $inviteUrl }}" class="email-button">Aceitar Convite</a>
            </div>
            <p>Este convite expira em 7 dias.</p>
            <p>Se você não esperava este convite, pode ignorar este email.</p>

            <hr class="email-divider">

            <div class="email-secondary">
                <p>Hello! You have been invited to access Voa - Travel Management.</p>
                <p>Click the button above to create your account and set your password.</p>
                <p>This invitation expires in 7 days. If you did not expect this invitation, you can ignore this email.</p>
            </div>
        </div>
        <div class="email-footer">
            <p>Voa - Viagens Corporativas &copy; {{ date('Y') }}</p>
        </div>
    </div>
</body>
</html>
