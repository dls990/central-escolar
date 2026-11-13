<!-- Exibe a pagina de rota nao encontrada. -->
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Não Encontrada | 404</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-brand { font-family: 'Fredoka', cursive; }
    </style>
</head>
<body class="bg-amber-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-3xl p-8 text-center shadow-xl border-4 border-amber-200">
        <div class="text-7xl mb-4 animate-bounce">🥪</div>
        <h1 class="text-4xl font-brand font-bold text-amber-900 mb-2">Ops! 404</h1>
        <p class="text-gray-600 mb-6">Parece que alguém comeu a página que você estava procurando no lanche!</p>
        <a href="<?= url('/') ?>" class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-3 px-6 rounded-2xl transition transform hover:scale-105 shadow-md">
            <span>🏠</span> Voltar ao Início
        </a>
    </div>
</body>
</html>
