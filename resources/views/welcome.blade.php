<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>Gestor de Documentos</title>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    <!-- Hero -->
    <section class="flex-grow flex items-center justify-center">
        <div class="text-center">
            <img src="{{ asset('img/logo.png') }}" class="h-24 mx-auto mb-6" alt="Logo">

            <h1 class="text-4xl font-extrabold mb-4">
                Seguridad e Integridad de Documentos Digitales
            </h1>
            <p class="text-gray-600 max-w-xl mx-auto mb-8">
                Firma, verifica y gestiona tus archivos PDF de forma sencilla y segura.
            </p>

            <div class="space-x-4">
                <a href="{{ route('login') }}"
                   class="px-6 py-3 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                    Iniciar sesión
                </a>
                <a href="{{ route('register') }}"
                   class="px-6 py-3 border border-indigo-600 text-indigo-600 rounded hover:bg-indigo-50">
                    Crear cuenta
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="text-center text-sm text-gray-500 pb-4">
        © {{ date('Y') }} – Proyecto de Integración
    </footer>
</body>
</html>
