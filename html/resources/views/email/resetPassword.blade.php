<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сброс пароля</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            border: 1px solid #dddddd;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            padding: 10px;
            background-color: #007bff;
            color: #ffffff;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }
        .content {
            padding: 20px;
            text-align: center;
        }
        .footer {
            text-align: center;
            padding: 10px;
            background-color: #f1f1f1;
            border-bottom-left-radius: 10px;
            border-bottom-right-radius: 10px;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h2>Сброс пароля</h2>
    </div>
    <div class="content">
        <p>Вы получили это письмо, потому что мы получили запрос на сброс пароля для вашего аккаунта.</p>
        Вы можете сбросить пароль по ссылке ниже:        
        <p><a href="{{ route('reset.password', $token) }}">Сброс пароля</a></p>
        <p>Мы рады приветствовать вас и надеемся, что вы найдете наш сервис полезным и удобным.</p>
    </div>
    <div class="footer">
        <p>Если вы не запрашивали сброс пароля, никаких дальнейших действий не требуется.</p>
        <p>Это письмо отправлено автоматически, пожалуйста, не отвечайте на него.</p>
    </div>
</div>
</body>
</html>



