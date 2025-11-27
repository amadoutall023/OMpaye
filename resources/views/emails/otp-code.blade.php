<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre code de vérification OMPAYE</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }
        .otp-code {
            background-color: #f8f9fa;
            border: 2px solid #007bff;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
            font-size: 32px;
            font-weight: bold;
            color: #007bff;
            letter-spacing: 5px;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            color: #856404;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">OMPAYE</div>
            <h2>Vérification de votre compte</h2>
        </div>

        <p>Bonjour,</p>

        <p>Pour sécuriser votre connexion à OMPAYE, nous avons besoin de vérifier votre identité. Voici votre code de vérification :</p>

        <div class="otp-code">
            {{ $otpCode }}
        </div>

        <div class="warning">
            <strong>⚠️ Important :</strong> Ce code expire dans 10 minutes. Ne partagez jamais ce code avec qui que ce soit.
        </div>

        <p>Si vous n'avez pas demandé ce code, veuillez ignorer cet email. Votre compte reste sécurisé.</p>

        <p>Cordialement,<br>
        L'équipe OMPAYE</p>

        <div class="footer">
            <p>Cet email a été envoyé automatiquement. Merci de ne pas y répondre.</p>
            <p>&copy; 2025 OMPAYE. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>