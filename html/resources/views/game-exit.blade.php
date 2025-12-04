<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Возврат в лобби</title>
    <script>
        // Если мы открыты в iframe — выходим в верхнее окно на слоты
        (function () {
            var target = '{{ config('app.url') }}/slots';
            try {
                if (window.top && window.top !== window) {
                    window.top.location.href = target;
                } else {
                    window.location.href = target;
                }
            } catch (e) {
                window.location.href = target;
            }
        })();
    </script>
</head>
<body style="background:#000;color:#fff;display:flex;align-items:center;justify-content:center;height:100vh;">
<div>Возврат в лобби...</div>
</body>
</html>


