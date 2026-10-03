<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Operasional - Matcha Indonesia</title>
    <style>
        body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #2F593E; /* Hijau Matcha */
            color: white;
            font-family: Arial, sans-serif;
            margin: 0;
        }
        .loader {
            border: 6px solid #f3f3f3;
            border-top: 6px solid #89C74A; /* Hijau terang */
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div class="loader"></div>
    <h2 id="boot-text">{{ $message ?? 'Memuat Sistem...' }}</h2>

    <script>
        // Simulasi booting selama 2.5 detik lalu pindah ke URL tujuan
        setTimeout(() => {
            window.location.href = "{{ $redirect_url }}";
        }, 2500);
    </script>
</body>
</html>