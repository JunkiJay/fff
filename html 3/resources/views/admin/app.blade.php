<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <link rel="shortcut icon" href="/assets/image/logo.png">
    <title>{{ $settings->title }}</title>
    <meta name="description" content="{{ $settings->description }}">
    <meta name="keywords" content="{{ $settings->keywords }}">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/wheel.css') }}">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"
        integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/fingerprintjs2/1/fingerprint2.min.js"></script>
    <script src="/js/snow.js"></script>
</head>

<body>
    <noscript>You need to enable JavaScript to run this app.</noscript>
    <div id="root"></div>
    <div class="errors">{{ session('error') }}</div>
</body>

<script src="/js/app.js"></script>
<script src="/js/theme.js"></script>

<div id="embedim--snow">
    <style>
        #embedim--snow {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
            z-index: 9999999;
            pointer-events: none
        }

        .embedim-snow {
            position: absolute;
            width: 10px;
            height: 10px;
            background: white;
            border-radius: 50%;
            margin-top: -10px
        }

        .embedim-snow:nth-child(1) {
            opacity: 0.67;
            transform: translate(57.20vw, -10px) scale(0.08);
            animation: fall-1 10s -4s linear infinite
        }

        @keyframes fall-1 {
            50.00% {
                transform: translate(61.29vw, 50.00vh) scale(0.08)
            }

            to {
                transform: translate(59.25vw, 105vh) scale(0.08)
            }
        }

        .embedim-snow:nth-child(2) {
            opacity: 0.61;
            transform: translate(61.56vw, -10px) scale(0.75);
            animation: fall-2 28s -20s linear infinite
        }

        @keyframes fall-2 {
            70.00% {
                transform: translate(70.74vw, 70.00vh) scale(0.75)
            }

            to {
                transform: translate(66.15vw, 105vh) scale(0.75)
            }
        }

        .embedim-snow:nth-child(3) {
            opacity: 0.46;
            transform: translate(48.36vw, -10px) scale(0.04);
            animation: fall-3 27s -19s linear infinite
        }

        @keyframes fall-3 {
            60.00% {
                transform: translate(43.38vw, 60.00vh) scale(0.04)
            }

            to {
                transform: translate(45.87vw, 105vh) scale(0.04)
            }
        }

        .embedim-snow:nth-child(4) {
            opacity: 0.13;
            transform: translate(86.52vw, -10px) scale(0.48);
            animation: fall-4 27s -10s linear infinite
        }

        @keyframes fall-4 {
            70.00% {
                transform: translate(92.33vw, 70.00vh) scale(0.48)
            }

            to {
                transform: translate(89.42vw, 105vh) scale(0.48)
            }
        }

        .embedim-snow:nth-child(5) {
            opacity: 0.77;
            transform: translate(95.87vw, -10px) scale(0.37);
            animation: fall-5 18s -16s linear infinite
        }

        @keyframes fall-5 {
            70.00% {
                transform: translate(94.75vw, 70.00vh) scale(0.37)
            }

            to {
                transform: translate(95.31vw, 105vh) scale(0.37)
            }
        }

        .embedim-snow:nth-child(6) {
            opacity: 0.97;
            transform: translate(67.26vw, -10px) scale(0.99);
            animation: fall-6 26s -19s linear infinite
        }

        @keyframes fall-6 {
            40.00% {
                transform: translate(61.11vw, 40.00vh) scale(0.99)
            }

            to {
                transform: translate(64.19vw, 105vh) scale(0.99)
            }
        }

        .embedim-snow:nth-child(7) {
            opacity: 0.81;
            transform: translate(39.59vw, -10px) scale(0.99);
            animation: fall-7 25s -12s linear infinite
        }

        @keyframes fall-7 {
            70.00% {
                transform: translate(33.04vw, 70.00vh) scale(0.99)
            }

            to {
                transform: translate(36.32vw, 105vh) scale(0.99)
            }
        }

        .embedim-snow:nth-child(8) {
            opacity: 0.48;
            transform: translate(29.09vw, -10px) scale(0.54);
            animation: fall-8 11s -9s linear infinite
        }

        @keyframes fall-8 {
            40.00% {
                transform: translate(23.02vw, 40.00vh) scale(0.54)
            }

            to {
                transform: translate(26.05vw, 105vh) scale(0.54)
            }
        }

        .embedim-snow:nth-child(9) {
            opacity: 0.67;
            transform: translate(1.14vw, -10px) scale(0.50);
            animation: fall-9 18s -0s linear infinite
        }

        @keyframes fall-9 {
            70.00% {
                transform: translate(10.48vw, 70.00vh) scale(0.50)
            }

            to {
                transform: translate(5.81vw, 105vh) scale(0.50)
            }
        }

        .embedim-snow:nth-child(10) {
            opacity: 0.83;
            transform: translate(37.47vw, -10px) scale(0.81);
            animation: fall-10 11s -23s linear infinite
        }

        @keyframes fall-10 {
            80.00% {
                transform: translate(42.56vw, 80.00vh) scale(0.81)
            }

            to {
                transform: translate(40.02vw, 105vh) scale(0.81)
            }
        }

        .embedim-snow:nth-child(11) {
            opacity: 0.16;
            transform: translate(56.36vw, -10px) scale(0.26);
            animation: fall-11 29s -6s linear infinite
        }

        @keyframes fall-11 {
            80.00% {
                transform: translate(48.44vw, 80.00vh) scale(0.26)
            }

            to {
                transform: translate(52.40vw, 105vh) scale(0.26)
            }
        }

        .embedim-snow:nth-child(12) {
            opacity: 0.69;
            transform: translate(43.66vw, -10px) scale(0.24);
            animation: fall-12 27s -24s linear infinite
        }

        @keyframes fall-12 {
            60.00% {
                transform: translate(37.89vw, 60.00vh) scale(0.24)
            }

            to {
                transform: translate(40.77vw, 105vh) scale(0.24)
            }
        }

        .embedim-snow:nth-child(13) {
            opacity: 0.14;
            transform: translate(65.00vw, -10px) scale(0.35);
            animation: fall-13 29s -28s linear infinite
        }

        @keyframes fall-13 {
            30.00% {
                transform: translate(59.52vw, 30.00vh) scale(0.35)
            }

            to {
                transform: translate(62.26vw, 105vh) scale(0.35)
            }
        }

        .embedim-snow:nth-child(14) {
            opacity: 0.56;
            transform: translate(25.96vw, -10px) scale(0.20);
            animation: fall-14 22s -28s linear infinite
        }

        @keyframes fall-14 {
            60.00% {
                transform: translate(33.00vw, 60.00vh) scale(0.20)
            }

            to {
                transform: translate(29.48vw, 105vh) scale(0.20)
            }
        }

        .embedim-snow:nth-child(15) {
            opacity: 0.11;
            transform: translate(98.60vw, -10px) scale(0.67);
            animation: fall-15 10s -18s linear infinite
        }

        @keyframes fall-15 {
            50.00% {
                transform: translate(95.13vw, 50.00vh) scale(0.67)
            }

            to {
                transform: translate(96.87vw, 105vh) scale(0.67)
            }
        }

        .embedim-snow:nth-child(16) {
            opacity: 0.42;
            transform: translate(58.07vw, -10px) scale(0.36);
            animation: fall-16 27s -11s linear infinite
        }

        @keyframes fall-16 {
            50.00% {
                transform: translate(62.28vw, 50.00vh) scale(0.36)
            }

            to {
                transform: translate(60.17vw, 105vh) scale(0.36)
            }
        }

        .embedim-snow:nth-child(17) {
            opacity: 0.95;
            transform: translate(13.70vw, -10px) scale(0.95);
            animation: fall-17 15s -19s linear infinite
        }

        @keyframes fall-17 {
            70.00% {
                transform: translate(18.99vw, 70.00vh) scale(0.95)
            }

            to {
                transform: translate(16.34vw, 105vh) scale(0.95)
            }
        }

        .embedim-snow:nth-child(18) {
            opacity: 0.65;
            transform: translate(8.38vw, -10px) scale(0.38);
            animation: fall-18 19s -30s linear infinite
        }

        @keyframes fall-18 {
            80.00% {
                transform: translate(12.92vw, 80.00vh) scale(0.38)
            }

            to {
                transform: translate(10.65vw, 105vh) scale(0.38)
            }
        }

        .embedim-snow:nth-child(19) {
            opacity: 0.69;
            transform: translate(59.51vw, -10px) scale(0.31);
            animation: fall-19 24s -18s linear infinite
        }

        @keyframes fall-19 {
            60.00% {
                transform: translate(52.93vw, 60.00vh) scale(0.31)
            }

            to {
                transform: translate(56.22vw, 105vh) scale(0.31)
            }
        }

        .embedim-snow:nth-child(20) {
            opacity: 0.96;
            transform: translate(93.05vw, -10px) scale(0.63);
            animation: fall-20 23s -2s linear infinite
        }

        @keyframes fall-20 {
            70.00% {
                transform: translate(90.30vw, 70.00vh) scale(0.63)
            }

            to {
                transform: translate(91.68vw, 105vh) scale(0.63)
            }
        }

        .embedim-snow:nth-child(21) {
            opacity: 0.33;
            transform: translate(74.27vw, -10px) scale(0.19);
            animation: fall-21 25s -18s linear infinite
        }

        @keyframes fall-21 {
            30.00% {
                transform: translate(64.37vw, 30.00vh) scale(0.19)
            }

            to {
                transform: translate(69.32vw, 105vh) scale(0.19)
            }
        }

        .embedim-snow:nth-child(22) {
            opacity: 0.62;
            transform: translate(58.07vw, -10px) scale(0.99);
            animation: fall-22 23s -27s linear infinite
        }

        @keyframes fall-22 {
            60.00% {
                transform: translate(52.45vw, 60.00vh) scale(0.99)
            }

            to {
                transform: translate(55.26vw, 105vh) scale(0.99)
            }
        }

        .embedim-snow:nth-child(23) {
            opacity: 0.36;
            transform: translate(33.05vw, -10px) scale(0.50);
            animation: fall-23 23s -8s linear infinite
        }

        @keyframes fall-23 {
            50.00% {
                transform: translate(38.36vw, 50.00vh) scale(0.50)
            }

            to {
                transform: translate(35.71vw, 105vh) scale(0.50)
            }
        }

        .embedim-snow:nth-child(24) {
            opacity: 0.58;
            transform: translate(72.59vw, -10px) scale(0.61);
            animation: fall-24 20s -12s linear infinite
        }

        @keyframes fall-24 {
            30.00% {
                transform: translate(63.28vw, 30.00vh) scale(0.61)
            }

            to {
                transform: translate(67.93vw, 105vh) scale(0.61)
            }
        }

        .embedim-snow:nth-child(25) {
            opacity: 0.08;
            transform: translate(3.81vw, -10px) scale(0.03);
            animation: fall-25 10s -26s linear infinite
        }

        @keyframes fall-25 {
            60.00% {
                transform: translate(4.68vw, 60.00vh) scale(0.03)
            }

            to {
                transform: translate(4.25vw, 105vh) scale(0.03)
            }
        }

        .embedim-snow:nth-child(26) {
            opacity: 0.64;
            transform: translate(5.11vw, -10px) scale(0.49);
            animation: fall-26 11s -7s linear infinite
        }

        @keyframes fall-26 {
            50.00% {
                transform: translate(10.79vw, 50.00vh) scale(0.49)
            }

            to {
                transform: translate(7.95vw, 105vh) scale(0.49)
            }
        }

        .embedim-snow:nth-child(27) {
            opacity: 0.76;
            transform: translate(22.40vw, -10px) scale(0.10);
            animation: fall-27 18s -17s linear infinite
        }

        @keyframes fall-27 {
            80.00% {
                transform: translate(17.93vw, 80.00vh) scale(0.10)
            }

            to {
                transform: translate(20.17vw, 105vh) scale(0.10)
            }
        }

        .embedim-snow:nth-child(28) {
            opacity: 0.35;
            transform: translate(20.99vw, -10px) scale(0.13);
            animation: fall-28 15s -6s linear infinite
        }

        @keyframes fall-28 {
            50.00% {
                transform: translate(27.60vw, 50.00vh) scale(0.13)
            }

            to {
                transform: translate(24.29vw, 105vh) scale(0.13)
            }
        }

        .embedim-snow:nth-child(29) {
            opacity: 0.49;
            transform: translate(95.15vw, -10px) scale(0.97);
            animation: fall-29 15s -0s linear infinite
        }

        @keyframes fall-29 {
            80.00% {
                transform: translate(91.66vw, 80.00vh) scale(0.97)
            }

            to {
                transform: translate(93.40vw, 105vh) scale(0.97)
            }
        }

        .embedim-snow:nth-child(30) {
            opacity: 0.00;
            transform: translate(15.63vw, -10px) scale(0.83);
            animation: fall-30 28s -14s linear infinite
        }

        @keyframes fall-30 {
            80.00% {
                transform: translate(19.78vw, 80.00vh) scale(0.83)
            }

            to {
                transform: translate(17.71vw, 105vh) scale(0.83)
            }
        }

        .embedim-snow:nth-child(31) {
            opacity: 0.80;
            transform: translate(19.78vw, -10px) scale(0.57);
            animation: fall-31 28s -20s linear infinite
        }

        @keyframes fall-31 {
            60.00% {
                transform: translate(15.00vw, 60.00vh) scale(0.57)
            }

            to {
                transform: translate(17.39vw, 105vh) scale(0.57)
            }
        }

        .embedim-snow:nth-child(32) {
            opacity: 0.91;
            transform: translate(30.10vw, -10px) scale(0.26);
            animation: fall-32 17s -28s linear infinite
        }

        @keyframes fall-32 {
            50.00% {
                transform: translate(27.14vw, 50.00vh) scale(0.26)
            }

            to {
                transform: translate(28.62vw, 105vh) scale(0.26)
            }
        }

        .embedim-snow:nth-child(33) {
            opacity: 0.33;
            transform: translate(36.48vw, -10px) scale(0.27);
            animation: fall-33 20s -10s linear infinite
        }

        @keyframes fall-33 {
            60.00% {
                transform: translate(40.88vw, 60.00vh) scale(0.27)
            }

            to {
                transform: translate(38.68vw, 105vh) scale(0.27)
            }
        }

        .embedim-snow:nth-child(34) {
            opacity: 0.37;
            transform: translate(2.07vw, -10px) scale(0.78);
            animation: fall-34 23s -5s linear infinite
        }

        @keyframes fall-34 {
            70.00% {
                transform: translate(7.71vw, 70.00vh) scale(0.78)
            }

            to {
                transform: translate(4.89vw, 105vh) scale(0.78)
            }
        }

        .embedim-snow:nth-child(35) {
            opacity: 0.59;
            transform: translate(53.48vw, -10px) scale(0.92);
            animation: fall-35 24s -11s linear infinite
        }

        @keyframes fall-35 {
            60.00% {
                transform: translate(53.26vw, 60.00vh) scale(0.92)
            }

            to {
                transform: translate(53.37vw, 105vh) scale(0.92)
            }
        }

        .embedim-snow:nth-child(36) {
            opacity: 0.41;
            transform: translate(14.24vw, -10px) scale(0.69);
            animation: fall-36 27s -3s linear infinite
        }

        @keyframes fall-36 {
            80.00% {
                transform: translate(20.67vw, 80.00vh) scale(0.69)
            }

            to {
                transform: translate(17.45vw, 105vh) scale(0.69)
            }
        }

        .embedim-snow:nth-child(37) {
            opacity: 0.25;
            transform: translate(92.55vw, -10px) scale(0.94);
            animation: fall-37 25s -9s linear infinite
        }

        @keyframes fall-37 {
            30.00% {
                transform: translate(97.39vw, 30.00vh) scale(0.94)
            }

            to {
                transform: translate(94.97vw, 105vh) scale(0.94)
            }
        }

        .embedim-snow:nth-child(38) {
            opacity: 0.65;
            transform: translate(72.75vw, -10px) scale(0.55);
            animation: fall-38 10s -5s linear infinite
        }

        @keyframes fall-38 {
            40.00% {
                transform: translate(65.90vw, 40.00vh) scale(0.55)
            }

            to {
                transform: translate(69.32vw, 105vh) scale(0.55)
            }
        }

        .embedim-snow:nth-child(39) {
            opacity: 0.44;
            transform: translate(60.32vw, -10px) scale(0.88);
            animation: fall-39 26s -5s linear infinite
        }

        @keyframes fall-39 {
            50.00% {
                transform: translate(62.02vw, 50.00vh) scale(0.88)
            }

            to {
                transform: translate(61.17vw, 105vh) scale(0.88)
            }
        }

        .embedim-snow:nth-child(40) {
            opacity: 0.63;
            transform: translate(93.79vw, -10px) scale(0.18);
            animation: fall-40 23s -6s linear infinite
        }

        @keyframes fall-40 {
            70.00% {
                transform: translate(92.49vw, 70.00vh) scale(0.18)
            }

            to {
                transform: translate(93.14vw, 105vh) scale(0.18)
            }
        }

        .embedim-snow:nth-child(41) {
            opacity: 0.35;
            transform: translate(6.32vw, -10px) scale(0.04);
            animation: fall-41 25s -4s linear infinite
        }

        @keyframes fall-41 {
            30.00% {
                transform: translate(2.05vw, 30.00vh) scale(0.04)
            }

            to {
                transform: translate(4.19vw, 105vh) scale(0.04)
            }
        }

        .embedim-snow:nth-child(42) {
            opacity: 0.68;
            transform: translate(93.58vw, -10px) scale(0.21);
            animation: fall-42 26s -20s linear infinite
        }

        @keyframes fall-42 {
            40.00% {
                transform: translate(98.63vw, 40.00vh) scale(0.21)
            }

            to {
                transform: translate(96.10vw, 105vh) scale(0.21)
            }
        }

        .embedim-snow:nth-child(43) {
            opacity: 0.39;
            transform: translate(54.63vw, -10px) scale(0.59);
            animation: fall-43 16s -9s linear infinite
        }

        @keyframes fall-43 {
            40.00% {
                transform: translate(51.81vw, 40.00vh) scale(0.59)
            }

            to {
                transform: translate(53.22vw, 105vh) scale(0.59)
            }
        }

        .embedim-snow:nth-child(44) {
            opacity: 0.12;
            transform: translate(60.25vw, -10px) scale(0.65);
            animation: fall-44 22s -12s linear infinite
        }

        @keyframes fall-44 {
            40.00% {
                transform: translate(51.45vw, 40.00vh) scale(0.65)
            }

            to {
                transform: translate(55.85vw, 105vh) scale(0.65)
            }
        }

        .embedim-snow:nth-child(45) {
            opacity: 0.29;
            transform: translate(62.55vw, -10px) scale(0.83);
            animation: fall-45 13s -8s linear infinite
        }

        @keyframes fall-45 {
            70.00% {
                transform: translate(65.11vw, 70.00vh) scale(0.83)
            }

            to {
                transform: translate(63.83vw, 105vh) scale(0.83)
            }
        }

        .embedim-snow:nth-child(46) {
            opacity: 0.49;
            transform: translate(15.26vw, -10px) scale(0.82);
            animation: fall-46 25s -18s linear infinite
        }

        @keyframes fall-46 {
            60.00% {
                transform: translate(17.40vw, 60.00vh) scale(0.82)
            }

            to {
                transform: translate(16.33vw, 105vh) scale(0.82)
            }
        }

        .embedim-snow:nth-child(47) {
            opacity: 0.99;
            transform: translate(42.75vw, -10px) scale(0.14);
            animation: fall-47 18s -14s linear infinite
        }

        @keyframes fall-47 {
            30.00% {
                transform: translate(32.81vw, 30.00vh) scale(0.14)
            }

            to {
                transform: translate(37.78vw, 105vh) scale(0.14)
            }
        }

        .embedim-snow:nth-child(48) {
            opacity: 0.43;
            transform: translate(40.80vw, -10px) scale(0.40);
            animation: fall-48 28s -8s linear infinite
        }

        @keyframes fall-48 {
            60.00% {
                transform: translate(31.66vw, 60.00vh) scale(0.40)
            }

            to {
                transform: translate(36.23vw, 105vh) scale(0.40)
            }
        }

        .embedim-snow:nth-child(49) {
            opacity: 0.44;
            transform: translate(81.59vw, -10px) scale(0.43);
            animation: fall-49 26s -1s linear infinite
        }

        @keyframes fall-49 {
            80.00% {
                transform: translate(89.66vw, 80.00vh) scale(0.43)
            }

            to {
                transform: translate(85.63vw, 105vh) scale(0.43)
            }
        }

        .embedim-snow:nth-child(50) {
            opacity: 0.94;
            transform: translate(78.60vw, -10px) scale(0.22);
            animation: fall-50 12s -27s linear infinite
        }

        @keyframes fall-50 {
            30.00% {
                transform: translate(76.33vw, 30.00vh) scale(0.22)
            }

            to {
                transform: translate(77.47vw, 105vh) scale(0.22)
            }
        }

        .embedim-snow:nth-child(51) {
            opacity: 0.18;
            transform: translate(6.54vw, -10px) scale(0.59);
            animation: fall-51 19s -1s linear infinite
        }

        @keyframes fall-51 {
            60.00% {
                transform: translate(5.66vw, 60.00vh) scale(0.59)
            }

            to {
                transform: translate(6.10vw, 105vh) scale(0.59)
            }
        }

        .embedim-snow:nth-child(52) {
            opacity: 0.36;
            transform: translate(75.92vw, -10px) scale(0.75);
            animation: fall-52 24s -2s linear infinite
        }

        @keyframes fall-52 {
            40.00% {
                transform: translate(79.55vw, 40.00vh) scale(0.75)
            }

            to {
                transform: translate(77.74vw, 105vh) scale(0.75)
            }
        }

        .embedim-snow:nth-child(53) {
            opacity: 0.97;
            transform: translate(61.03vw, -10px) scale(0.04);
            animation: fall-53 28s -7s linear infinite
        }

        @keyframes fall-53 {
            40.00% {
                transform: translate(61.97vw, 40.00vh) scale(0.04)
            }

            to {
                transform: translate(61.50vw, 105vh) scale(0.04)
            }
        }

        .embedim-snow:nth-child(54) {
            opacity: 0.73;
            transform: translate(1.38vw, -10px) scale(0.81);
            animation: fall-54 22s -25s linear infinite
        }

        @keyframes fall-54 {
            30.00% {
                transform: translate(-4.85vw, 30.00vh) scale(0.81)
            }

            to {
                transform: translate(-1.74vw, 105vh) scale(0.81)
            }
        }

        .embedim-snow:nth-child(55) {
            opacity: 0.57;
            transform: translate(24.99vw, -10px) scale(0.79);
            animation: fall-55 10s -12s linear infinite
        }

        @keyframes fall-55 {
            60.00% {
                transform: translate(27.46vw, 60.00vh) scale(0.79)
            }

            to {
                transform: translate(26.22vw, 105vh) scale(0.79)
            }
        }

        .embedim-snow:nth-child(56) {
            opacity: 0.14;
            transform: translate(42.10vw, -10px) scale(0.26);
            animation: fall-56 29s -27s linear infinite
        }

        @keyframes fall-56 {
            40.00% {
                transform: translate(34.94vw, 40.00vh) scale(0.26)
            }

            to {
                transform: translate(38.52vw, 105vh) scale(0.26)
            }
        }

        .embedim-snow:nth-child(57) {
            opacity: 0.47;
            transform: translate(44.86vw, -10px) scale(0.77);
            animation: fall-57 25s -24s linear infinite
        }

        @keyframes fall-57 {
            40.00% {
                transform: translate(51.38vw, 40.00vh) scale(0.77)
            }

            to {
                transform: translate(48.12vw, 105vh) scale(0.77)
            }
        }

        .embedim-snow:nth-child(58) {
            opacity: 0.15;
            transform: translate(51.84vw, -10px) scale(0.50);
            animation: fall-58 17s -25s linear infinite
        }

        @keyframes fall-58 {
            80.00% {
                transform: translate(47.84vw, 80.00vh) scale(0.50)
            }

            to {
                transform: translate(49.84vw, 105vh) scale(0.50)
            }
        }

        .embedim-snow:nth-child(59) {
            opacity: 0.65;
            transform: translate(55.61vw, -10px) scale(0.10);
            animation: fall-59 23s -16s linear infinite
        }

        @keyframes fall-59 {
            70.00% {
                transform: translate(58.39vw, 70.00vh) scale(0.10)
            }

            to {
                transform: translate(57.00vw, 105vh) scale(0.10)
            }
        }

        .embedim-snow:nth-child(60) {
            opacity: 0.13;
            transform: translate(31.32vw, -10px) scale(0.41);
            animation: fall-60 16s -25s linear infinite
        }

        @keyframes fall-60 {
            70.00% {
                transform: translate(30.59vw, 70.00vh) scale(0.41)
            }

            to {
                transform: translate(30.96vw, 105vh) scale(0.41)
            }
        }

        .embedim-snow:nth-child(61) {
            opacity: 0.81;
            transform: translate(90.23vw, -10px) scale(0.51);
            animation: fall-61 18s -23s linear infinite
        }

        @keyframes fall-61 {
            30.00% {
                transform: translate(90.90vw, 30.00vh) scale(0.51)
            }

            to {
                transform: translate(90.56vw, 105vh) scale(0.51)
            }
        }

        .embedim-snow:nth-child(62) {
            opacity: 0.83;
            transform: translate(17.29vw, -10px) scale(0.39);
            animation: fall-62 24s -0s linear infinite
        }

        @keyframes fall-62 {
            80.00% {
                transform: translate(21.41vw, 80.00vh) scale(0.39)
            }

            to {
                transform: translate(19.35vw, 105vh) scale(0.39)
            }
        }

        .embedim-snow:nth-child(63) {
            opacity: 0.37;
            transform: translate(78.04vw, -10px) scale(0.46);
            animation: fall-63 19s -29s linear infinite
        }

        @keyframes fall-63 {
            40.00% {
                transform: translate(80.76vw, 40.00vh) scale(0.46)
            }

            to {
                transform: translate(79.40vw, 105vh) scale(0.46)
            }
        }

        .embedim-snow:nth-child(64) {
            opacity: 0.26;
            transform: translate(16.59vw, -10px) scale(0.91);
            animation: fall-64 25s -25s linear infinite
        }

        @keyframes fall-64 {
            50.00% {
                transform: translate(20.52vw, 50.00vh) scale(0.91)
            }

            to {
                transform: translate(18.55vw, 105vh) scale(0.91)
            }
        }

        .embedim-snow:nth-child(65) {
            opacity: 0.21;
            transform: translate(42.58vw, -10px) scale(0.16);
            animation: fall-65 17s -8s linear infinite
        }

        @keyframes fall-65 {
            40.00% {
                transform: translate(52.01vw, 40.00vh) scale(0.16)
            }

            to {
                transform: translate(47.30vw, 105vh) scale(0.16)
            }
        }

        .embedim-snow:nth-child(66) {
            opacity: 0.69;
            transform: translate(3.72vw, -10px) scale(0.24);
            animation: fall-66 23s -8s linear infinite
        }

        @keyframes fall-66 {
            40.00% {
                transform: translate(11.85vw, 40.00vh) scale(0.24)
            }

            to {
                transform: translate(7.78vw, 105vh) scale(0.24)
            }
        }

        .embedim-snow:nth-child(67) {
            opacity: 0.34;
            transform: translate(7.98vw, -10px) scale(0.16);
            animation: fall-67 18s -6s linear infinite
        }

        @keyframes fall-67 {
            80.00% {
                transform: translate(7.20vw, 80.00vh) scale(0.16)
            }

            to {
                transform: translate(7.59vw, 105vh) scale(0.16)
            }
        }

        .embedim-snow:nth-child(68) {
            opacity: 0.88;
            transform: translate(63.33vw, -10px) scale(0.85);
            animation: fall-68 22s -5s linear infinite
        }

        @keyframes fall-68 {
            70.00% {
                transform: translate(56.65vw, 70.00vh) scale(0.85)
            }

            to {
                transform: translate(59.99vw, 105vh) scale(0.85)
            }
        }

        .embedim-snow:nth-child(69) {
            opacity: 0.84;
            transform: translate(17.46vw, -10px) scale(0.65);
            animation: fall-69 21s -26s linear infinite
        }

        @keyframes fall-69 {
            30.00% {
                transform: translate(13.26vw, 30.00vh) scale(0.65)
            }

            to {
                transform: translate(15.36vw, 105vh) scale(0.65)
            }
        }

        .embedim-snow:nth-child(70) {
            opacity: 0.68;
            transform: translate(11.98vw, -10px) scale(0.11);
            animation: fall-70 30s -22s linear infinite
        }

        @keyframes fall-70 {
            80.00% {
                transform: translate(7.41vw, 80.00vh) scale(0.11)
            }

            to {
                transform: translate(9.69vw, 105vh) scale(0.11)
            }
        }

        .embedim-snow:nth-child(71) {
            opacity: 0.71;
            transform: translate(9.65vw, -10px) scale(0.92);
            animation: fall-71 23s -19s linear infinite
        }

        @keyframes fall-71 {
            60.00% {
                transform: translate(5.06vw, 60.00vh) scale(0.92)
            }

            to {
                transform: translate(7.35vw, 105vh) scale(0.92)
            }
        }

        .embedim-snow:nth-child(72) {
            opacity: 0.13;
            transform: translate(7.11vw, -10px) scale(0.25);
            animation: fall-72 13s -10s linear infinite
        }

        @keyframes fall-72 {
            40.00% {
                transform: translate(9.48vw, 40.00vh) scale(0.25)
            }

            to {
                transform: translate(8.30vw, 105vh) scale(0.25)
            }
        }

        .embedim-snow:nth-child(73) {
            opacity: 0.64;
            transform: translate(64.71vw, -10px) scale(0.32);
            animation: fall-73 27s -18s linear infinite
        }

        @keyframes fall-73 {
            80.00% {
                transform: translate(69.34vw, 80.00vh) scale(0.32)
            }

            to {
                transform: translate(67.03vw, 105vh) scale(0.32)
            }
        }

        .embedim-snow:nth-child(74) {
            opacity: 0.86;
            transform: translate(0.43vw, -10px) scale(0.39);
            animation: fall-74 14s -12s linear infinite
        }

        @keyframes fall-74 {
            80.00% {
                transform: translate(-0.78vw, 80.00vh) scale(0.39)
            }

            to {
                transform: translate(-0.18vw, 105vh) scale(0.39)
            }
        }

        .embedim-snow:nth-child(75) {
            opacity: 0.18;
            transform: translate(33.23vw, -10px) scale(0.51);
            animation: fall-75 30s -6s linear infinite
        }

        @keyframes fall-75 {
            40.00% {
                transform: translate(28.47vw, 40.00vh) scale(0.51)
            }

            to {
                transform: translate(30.85vw, 105vh) scale(0.51)
            }
        }

        .embedim-snow:nth-child(76) {
            opacity: 0.36;
            transform: translate(50.35vw, -10px) scale(0.52);
            animation: fall-76 13s -5s linear infinite
        }

        @keyframes fall-76 {
            70.00% {
                transform: translate(48.96vw, 70.00vh) scale(0.52)
            }

            to {
                transform: translate(49.65vw, 105vh) scale(0.52)
            }
        }

        .embedim-snow:nth-child(77) {
            opacity: 0.59;
            transform: translate(15.40vw, -10px) scale(0.26);
            animation: fall-77 12s -2s linear infinite
        }

        @keyframes fall-77 {
            70.00% {
                transform: translate(15.62vw, 70.00vh) scale(0.26)
            }

            to {
                transform: translate(15.51vw, 105vh) scale(0.26)
            }
        }

        .embedim-snow:nth-child(78) {
            opacity: 0.01;
            transform: translate(96.48vw, -10px) scale(0.99);
            animation: fall-78 10s -4s linear infinite
        }

        @keyframes fall-78 {
            70.00% {
                transform: translate(93.71vw, 70.00vh) scale(0.99)
            }

            to {
                transform: translate(95.09vw, 105vh) scale(0.99)
            }
        }

        .embedim-snow:nth-child(79) {
            opacity: 0.85;
            transform: translate(13.29vw, -10px) scale(0.83);
            animation: fall-79 22s -15s linear infinite
        }

        @keyframes fall-79 {
            40.00% {
                transform: translate(5.65vw, 40.00vh) scale(0.83)
            }

            to {
                transform: translate(9.47vw, 105vh) scale(0.83)
            }
        }
    </style><i class="embedim-snow"></i><i class="embedim-snow"></i><i class="embedim-snow"></i><i
        class="embedim-snow"></i><i class="embedim-snow"></i><i class="embedim-snow"></i><i class="embedim-snow"></i><i
        class="embedim-snow"></i><i class="embedim-snow"></i><i class="embedim-snow"></i><i class="embedim-snow"></i><i
        class="embedim-snow"></i><i class="embedim-snow"></i><i class="embedim-snow"></i><i class="embedim-snow"></i><i
        class="embedim-snow"></i><i class="embedim-snow"></i><i class="embedim-snow"></i><i class="embedim-snow"></i><i
        class="embedim-snow"></i><i class="embedim-snow"></i><i class="embedim-snow"></i><i class="embedim-snow"></i><i
        class="embedim-snow"></i><i class="embedim-snow"></i><i class="embedim-snow"></i><i class="embedim-snow"></i><i
        class="embedim-snow"></i><i class="embedim-snow"></i><i class="embedim-snow"></i><i class="embedim-snow"></i><i
        class="embedim-snow"></i><i class="embedim-snow"></i><i class="embedim-snow"></i><i class="embedim-snow"></i><i
        class="embedim-snow"></i><i class="embedim-snow"></i><i class="embedim-snow"></i><i class="embedim-snow"></i><i
        class="embedim-snow"></i><i class="embedim-snow"></i><i class="embedim-snow"></i><i class="embedim-snow"></i><i
        class="embedim-snow"></i><i class="embedim-snow"></i><i class="embedim-snow"></i><i class="embedim-snow"></i><i
        class="embedim-snow"></i><i class="embedim-snow"></i><i class="embedim-snow"></i><i class="embedim-snow"></i><i
        class="embedim-snow"></i><i class="embedim-snow"></i><i class="embedim-snow"></i><i class="embedim-snow"></i><i
        class="embedim-snow"></i><i class="embedim-snow"></i><i class="embedim-snow"></i><i class="embedim-snow"></i><i
        class="embedim-snow"></i><i class="embedim-snow"></i><i class="embedim-snow"></i><i class="embedim-snow"></i><i
        class="embedim-snow"></i><i class="embedim-snow"></i><i class="embedim-snow"></i><i class="embedim-snow"></i><i
        class="embedim-snow"></i><i class="embedim-snow"></i><i class="embedim-snow"></i><i
        class="embedim-snow"></i><i class="embedim-snow"></i><i class="embedim-snow"></i><i
        class="embedim-snow"></i><i class="embedim-snow"></i><i class="embedim-snow"></i><i
        class="embedim-snow"></i><i class="embedim-snow"></i><i class="embedim-snow"></i>
</div>

</html>
