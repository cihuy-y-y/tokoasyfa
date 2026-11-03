<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Selamat Datang - Toko Asyfa</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            overflow: hidden;
            background: #fbe3e9;
        }

        /* LANDING PAGE */
        .landing-page {
            position: relative;
            width: 100%;
            height: 100vh;
            height: 100dvh;
            overflow: hidden;
            background: #fbe3e9;
        }

        /* GAMBAR BACKGROUND */
        .landing-image {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: fill;
            display: block;
        }

        /* TOMBOL LOGIN */
        .login-button {
            position: absolute;

            /* Posisi mengikuti tombol pada gambar */
            left: 15.4%;
            top: 77.4%;
            width: 26.3%;
            height: 8%;

            display: flex;
            align-items: center;
            justify-content: center;

            text-decoration: none;
            border-radius: 50px;

            color: transparent;
            background: transparent;

            transition: all 0.3s ease;
            cursor: pointer;
        }

        .login-button:hover {
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 5px 20px rgba(194, 69, 110, 0.15);
        }

        .login-button:active {
            transform: scale(0.97);
        }

        /* AKSESIBILITAS */
        .login-button:focus-visible {
            outline: 3px solid #c2456e;
            outline-offset: 4px;
        }

        /* RESPONSIVE MOBILE */
        @media screen and (max-width: 768px) {
            .landing-page {
                height: 100dvh;
            }

            .landing-image {
                object-fit: fill;
            }

            .login-button {
                left: 15.4%;
                top: 77.4%;
                width: 26.3%;
                height: 8%;
            }
        }
    </style>
</head>

<body>

    <main class="landing-page">

        <!-- Background Landing Page -->
        <img
            src="{{ asset('images/selamat.png') }}"
            alt="Selamat Datang di Toko Kosmetik Asyfa"
            class="landing-image"
        >

        <!-- Tombol Login -->
        <a href="{{ route('login') }}"
           class="login-button"
           aria-label="Silakan Login untuk Melanjutkan"
           title="Login">
        </a>

    </main>

</body>
</html>
