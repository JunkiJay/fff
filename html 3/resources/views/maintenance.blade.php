<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Технические работы</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
            font-size: 32px;
        }
        p {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .form-group {
            margin-bottom: 20px;
        }
        input[type="password"] {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
        }
        button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s;
        }
        button:hover {
            transform: translateY(-2px);
        }
        .error {
            color: #e74c3c;
            margin-top: 10px;
            display: none;
        }
        .error.show {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Технические работы</h1>
        <p>Сайт временно недоступен. Ведутся технические работы.</p>
        <p style="font-size: 14px; color: #999;">Если у вас есть пароль доступа, введите его ниже:</p>
        
        <form method="POST" action="/maintenance/verify" id="maintenanceForm">
            @csrf
            <div class="form-group">
                <input type="password" name="password" placeholder="Введите пароль" required autofocus>
            </div>
            <button type="submit">Войти</button>
            <div class="error" id="errorMessage">Неверный пароль</div>
        </form>
    </div>

    <script>
        // Показываем ошибку если есть параметр error в URL
        if (window.location.search.includes('error=1')) {
            document.getElementById('errorMessage').classList.add('show');
        }
        
        // Фокус на поле ввода
        document.querySelector('input[name="password"]').focus();
    </script>
</body>
</html>




