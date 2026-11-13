<?php
// Prepara a identidade visual, menus por perfil, mensagens e abertura do conteudo.

$usuarioLogado = usuario_logado();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tituloPagina ?? 'VivalCris - Central de Frequência e Alimentação Escolar' ?></title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            accent: '#f59e0b',
                            coral: '#f43f5e',
                            violet: '#8b5cf6'
                        }
                    },
                    fontFamily: {
                        brand: ['Manrope', 'sans-serif'],
                        sans: ['Source Sans 3', 'sans-serif']
                    }
                }
            }
        }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">


    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="app-body min-h-screen flex flex-col text-slate-800 antialiased selection:bg-brand-200 selection:text-slate-900">


    <div class="bg-slate-900 text-slate-200 text-xs py-1.5 px-4">
        <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="font-bold text-white">Feira Tecnológica Escolar</span>
                <span class="hidden sm:inline text-slate-400">|</span>
                <span class="hidden sm:inline text-slate-300">VivalCris • Central de Frequência e Alimentação Escolar</span>
            </div>

            <div class="flex items-center gap-3">
                <?php if ($usuarioLogado): ?>
                    <span class="text-xs text-slate-300">Sessão autenticada: <strong class="text-white"><?= htmlspecialchars($usuarioLogado['nome']) ?></strong></span>
                <?php else: ?>
                    <a href="<?= url('login') ?>" class="text-xs font-semibold text-slate-200 hover:text-white">
                        Acesso da equipe
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>


    <header class="sticky top-0 z-40 bg-white border-b border-slate-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">


                <a href="<?= url('/') ?>" class="flex items-center gap-3 group focus:outline-none">
                    <div class="w-11 h-11 rounded-lg bg-emerald-800 flex items-center justify-center text-sm font-bold tracking-wide text-white">
                        CE
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-brand font-bold text-2xl text-slate-800 tracking-tight">VivalCris</span>
                        </div>
                        <p class="text-xs text-slate-600 hidden sm:block">Central de Frequência & Alimentação Escolar</p>
                    </div>
                </a>


                <nav class="hidden lg:flex items-center gap-1 xl:gap-2">
                    <a href="<?= url('/') ?>" class="nav-item <?= isActiveRoute('/') ?>">Início</a>


                    <?php if (!$usuarioLogado || tem_perfil(['inspetora', 'admin'])): ?>
                        <a href="<?= url('frequencia') ?>" class="nav-item <?= isActiveRoute('frequencia') ?>">Frequência</a>
                        <a href="<?= url('frequencia/historico') ?>" class="nav-item <?= isActiveRoute('frequencia/historico') ?>">Histórico</a>
                    <?php endif; ?>


                    <?php if (!$usuarioLogado || tem_perfil(['nutricionista', 'admin'])): ?>
                        <a href="<?= url('cozinha') ?>" class="nav-item <?= isActiveRoute('cozinha') ?>">Cozinha</a>
                    <?php endif; ?>

                    <a href="<?= url('alimentacao/cardapio') ?>" class="nav-item <?= isActiveRoute('alimentacao/cardapio') ?>">Cardápio</a>

                    <a href="<?= url('alunos') ?>" class="nav-item <?= isActiveRoute('alunos') ?>">Alunos</a>


                    <?php if (tem_perfil('admin')): ?>
                        <a href="<?= url('admin') ?>" class="nav-item <?= isActiveRoute('admin') ?>">Administração</a>
                        <a href="<?= url('admin/usuarios') ?>" class="nav-item <?= isActiveRoute('admin/usuarios') ?>">Usuários</a>
                    <?php endif; ?>

                    <a href="<?= url('relatorios') ?>" class="nav-item <?= isActiveRoute('relatorios') ?>">Relatórios</a>
                </nav>


                <div class="flex items-center gap-2">



                    <button onclick="document.getElementById('modalSobre').classList.remove('hidden')"
                            class="hidden xl:inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
                        Sobre
                    </button>


                    <?php if ($usuarioLogado): ?>
                        <div class="hidden sm:flex items-center gap-2 pl-2 border-l border-slate-200">
                            <span class="text-2xl select-none"><?= $usuarioLogado['avatar'] ?? '👤' ?></span>
                            <div class="text-left">
                                <span class="font-bold text-xs text-slate-800 block leading-tight">
                                    <?= htmlspecialchars($usuarioLogado['nome']) ?>
                                </span>
                                <span class="inline-block text-[10px] font-bold px-1.5 py-0.2 rounded
                                    <?= ($usuarioLogado['perfil'] === 'admin') ? 'bg-indigo-100 text-indigo-700' : (($usuarioLogado['perfil'] === 'nutricionista') ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800') ?>">
                                    <?= ucfirst($usuarioLogado['perfil']) ?>
                                </span>
                            </div>
                                     <a href="<?= url('logout') ?>"
                                         class="ml-1 px-2 py-1 text-slate-600 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition text-xs font-bold"
                                         title="Sair do Sistema">
                                          Sair
                            </a>
                        </div>
                    <?php else: ?>
                        <a href="<?= url('login') ?>"
                           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-sm">
                            Entrar
                        </a>
                    <?php endif; ?>


                    <button onclick="toggleMobileMenu()" class="lg:hidden p-2 rounded-xl text-slate-600 hover:bg-slate-100">
                        <span class="text-2xl">☰</span>
                    </button>
                </div>
            </div>
        </div>


        <div id="mobileMenu" class="hidden lg:hidden border-t border-slate-100 bg-white/95 px-4 pt-2 pb-6 space-y-1">
            <?php if ($usuarioLogado): ?>
                <div class="p-3 bg-emerald-50 rounded-xl flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl"><?= $usuarioLogado['avatar'] ?></span>
                        <div>
                            <span class="font-bold text-xs text-slate-800 block"><?= htmlspecialchars($usuarioLogado['nome']) ?></span>
                            <span class="text-[10px] text-emerald-700 font-semibold"><?= htmlspecialchars($usuarioLogado['cargo']) ?></span>
                        </div>
                    </div>
                    <a href="<?= url('logout') ?>" class="text-xs font-bold text-rose-600">Sair</a>
                </div>
            <?php endif; ?>

            <a href="<?= url('/') ?>" class="mobile-nav-item">Início</a>
            <a href="<?= url('frequencia') ?>" class="mobile-nav-item">Frequência</a>
            <a href="<?= url('frequencia/historico') ?>" class="mobile-nav-item">Histórico</a>
            <a href="<?= url('cozinha') ?>" class="mobile-nav-item">Cozinha</a>
            <a href="<?= url('alimentacao/cardapio') ?>" class="mobile-nav-item">Cardápio</a>
            <a href="<?= url('alunos') ?>" class="mobile-nav-item">Alunos</a>
            <?php if (tem_perfil('admin')): ?>
                <a href="<?= url('admin') ?>" class="mobile-nav-item">Administração</a>
                <a href="<?= url('admin/usuarios') ?>" class="mobile-nav-item">Usuários</a>
            <?php endif; ?>
            <a href="<?= url('relatorios') ?>" class="mobile-nav-item">Relatórios</a>
        </div>
    </header>


    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 w-full">
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="flash-alert bg-emerald-500 text-white p-4 rounded-2xl shadow-lg shadow-emerald-500/20 flex items-center justify-between mb-4 animate-bounce-short">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">🎉</span>
                    <p class="font-medium text-sm"><?= htmlspecialchars($_SESSION['flash_success']) ?></p>
                </div>
                <button onclick="this.parentElement.remove()" class="text-white hover:opacity-75 text-xl font-bold">×</button>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="flash-alert bg-rose-500 text-white p-4 rounded-2xl shadow-lg shadow-rose-500/20 flex items-center justify-between mb-4 animate-bounce-short">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">⚠️</span>
                    <p class="font-medium text-sm"><?= htmlspecialchars($_SESSION['flash_error']) ?></p>
                </div>
                <button onclick="this.parentElement.remove()" class="text-white hover:opacity-75 text-xl font-bold">×</button>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>
    </div>


    <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
