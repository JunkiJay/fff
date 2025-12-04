<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подтверждение оплаты</title>
</head>
<body>
    <form method="GET" action="https://payou.pro/sci/v1/">
        <input type="hidden" name="id" value="{{ $merchantId }}">
        <input type="hidden" name="sistems" value="{{ $sistems }}">
        <input type="hidden" name="summ" value="{{ $amount }}">
        <input type="hidden" name="order_id" value="{{ $orderId }}">
        <input type="hidden" name="Coment" value="{{ $comment }}">
        <input type="hidden" name="user_code" value="{{ $userCode }}">
        <input type="hidden" name="user_email" value="{{ $userEmail }}">
        <input type="hidden" name="hash" value="{{ $hash }}">
        <button type="submit">Перейти к оплате</button>
    </form>
</body>
</html>