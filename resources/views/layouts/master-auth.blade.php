<!doctype html>
<html lang="en" data-bs-theme="light" data-footer="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="SISDATO - Sistema dado para todos" name="description">
    <meta content="Themesbrand" name="author">
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ URL::asset('build/images/favicon.ico') }}">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>https://www.motoferias.com - SISDATO</title>
    <meta name="description" content="Moto Ferias">
    <link rel="canonical" href="https://www.motoferias.com">
    <meta property="og:title" content="https://www.motoferias.com - SISDATO">
    <meta property="og:description" content=" Moto Ferias">
    <meta property="og:type" content="WebPage">
    <meta property="og:image" content="https://motoferias.com/build/images/logo.png">
    <meta property="og:url" content="https://motoferias.com">

    <meta name="twitter:title" content="https://www.motoferias.com - SISDATO ">
    <meta name="twitter:description" content=" Moto Ferias ">
    <meta name="twitter:site" content="@motoferias">
    <script type="application/ld+json">{"@context":"https://schema.org","@type":"WebPage","name":"Moto Ferias","description":" "}</script>
    <script
        src="https://code.jquery.com/jquery-3.7.0.min.js"
        integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g="
        crossorigin="anonymous"></script>
    <!-- head css -->
    @include('layouts.head-css')
</head>

<body>

    <section
        class="auth-page-wrapper position-relative bg-light min-vh-100 d-flex align-items-center justify-content-between">


        <!--content here-->
        @yield('content')
    </section>

    <!--script-->
    @include('layouts.vendor-scripts')
</body>

</html>
