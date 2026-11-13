<!-- Fecha o conteudo, mostra informacoes do site e carrega os scripts comuns. -->
    </main>


    <div id="modalSobre" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-2xl w-full p-6 sm:p-8 shadow-2xl border border-slate-200 max-h-[90vh] overflow-y-auto transform transition-all animate-scale-up">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div>
                        <h3 class="font-brand font-bold text-2xl text-slate-800">Sobre o sistema</h3>
                        <p class="text-xs text-brand-600 font-semibold">VivalCris • Central de Frequência e Alimentação Escolar</p>
                    </div>
                </div>
                <button onclick="document.getElementById('modalSobre').classList.add('hidden')" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl text-xl">✕</button>
            </div>

            <div class="mt-6 space-y-4 text-sm text-slate-600">
                <div class="p-4 bg-slate-50 rounded-md border border-slate-200">
                    <h4 class="font-bold text-emerald-900 flex items-center gap-2 mb-1">
                        Objetivo
                    </h4>
                    <p>Integrar o controle diário de presença dos estudantes com o planejamento de refeições e o acompanhamento de restrições alimentares.</p>
                </div>

                <div class="p-4 bg-slate-50 rounded-md border border-slate-200">
                    <h4 class="font-bold text-indigo-900 flex items-center gap-2 mb-2">
                        Arquitetura MVC
                    </h4>
                    <ul class="space-y-1.5 list-disc list-inside">
                        <li><strong class="text-indigo-800">Model:</strong> Camada de dados e regras de negócio (<code class="bg-indigo-100 px-1 rounded text-xs">Aluno.php</code>, <code class="bg-indigo-100 px-1 rounded text-xs">Frequencia.php</code>, <code class="bg-indigo-100 px-1 rounded text-xs">Cardapio.php</code>, <code class="bg-indigo-100 px-1 rounded text-xs">Refeicao.php</code>, <code class="bg-indigo-100 px-1 rounded text-xs">Database.php</code>).</li>
                        <li><strong class="text-indigo-800">View:</strong> Interfaces visuais com Tailwind CSS, micro-animações e componentes interativos.</li>
                        <li><strong class="text-indigo-800">Controller:</strong> Intermediários que processam requisições, executam regras e despacham dados para as views.</li>
                        <li><strong class="text-indigo-800">Router:</strong> Roteamento elegante e compatível com servidor PHP embutido ou XAMPP/WAMP.</li>
                    </ul>
                </div>

                <div class="p-4 bg-slate-50 rounded-md border border-slate-200">
                    <h4 class="font-bold text-slate-800 flex items-center gap-2 mb-1">
                        Recursos do sistema
                    </h4>
                    <p>Registro de frequência, histórico diário, envio do censo para a cozinha e gestão de contas por perfil.</p>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 flex justify-end">
                <button onclick="document.getElementById('modalSobre').classList.add('hidden')" class="px-5 py-2.5 bg-emerald-800 hover:bg-emerald-900 text-white font-bold rounded-md transition">
                    Fechar
                </button>
            </div>
        </div>
    </div>


    <div id="toastContainer" class="fixed bottom-6 right-6 z-50 flex flex-col gap-2 pointer-events-none"></div>


    <footer class="bg-white border-t border-slate-200 py-8 mt-12 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center sm:text-left flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-md bg-emerald-800 text-white flex items-center justify-center text-xs font-bold">
                    CE
                </div>
                <div>
                    <p class="font-brand font-bold text-slate-800">VivalCris</p>
                    <p class="text-xs text-slate-600">Frequência e alimentação escolar</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center justify-center gap-4 text-xs font-medium text-slate-600">
                <span>Alimentação escolar</span>
                <span>•</span>
                <span>Frequência</span>
                <span>•</span>
                <span>Gestão de usuários</span>
            </div>
        </div>
    </footer>


    <script>
        const BASE_URL = '<?= base_url() ?>';
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        }
    </script>
    <script src="<?= asset('js/audio.js') ?>"></script>
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
