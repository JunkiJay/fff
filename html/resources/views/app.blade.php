<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <link rel="shortcut icon" href="/assets/image/logo.png">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"
        integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script> --}}
    {{-- <script src="https://cdn.jsdelivr.net/fingerprintjs2/1/fingerprint2.min.js"></script> --}}
    <link rel="sitemap" type="application/xml" title="Sitemap" href="/sitemap.xml">
    <meta name="robots" content="index, follow">
    <link rel="alternate" type="text/plain" href="/robots.txt" title="Robots File">
    <link rel="preload"  href="/assets/banner/banner3.webp" as="image">
</head>

<body class="dark">
    <noscript>You need to enable JavaScript to run this app.</noscript>
    <div id="root"></div>
    <div class="errors">{{ session('error') }}</div>
</body>

@php
    // if (isset($_GET['invite'])) {
    //     session_start();
    //     $_SESSION['ref'] = $_GET['invite'];
    // }
@endphp

<script>
    // const gl = document.createElement("canvas").getContext("webgl");
    // const ext = gl.getExtension("WEBGL_debug_renderer_info");

    // if (ext) {
    //     $.post('/user/videocard', {
    //         video: gl.getParameter(ext.UNMASKED_RENDERER_WEBGL)
    //     })
    // }

    // new Fingerprint2().get(function(result, components) {
    //     var print = result;
    //     $.post('/user/fingerprint', {
    //         finger: print
    //     })
    // });
</script>

@vite(['resources/js/app.js'])

<style>

</style>

</html>

