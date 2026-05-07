<?php
/**
 * Template Name: Cancellation Survey
 *
 * Standalone page for subscription cancellation feedback.
 */

if ( ! is_user_logged_in() ) {
    wp_redirect( site_url( '/login/' ) );
    exit;
}

$user_id = get_current_user_id();
$user_data = get_userdata( $user_id );
$access = new Expressive_Access();
$is_active = $access->has_active_subscription( $user_id );
$access_state = Expressive_Access::get_user_access_snapshot( $user_id );
$expiry_date = $access_state['access_expires_at'] ?? get_user_meta( $user_id, '_lms_elite_api_expiry', true );

// If already inactive and expired, no need to cancel
if ( empty( $access_state['can_cancel'] ) && ( ! $is_active || ! empty( $access_state['is_lifetime'] ) || ! empty( $access_state['is_manually_blocked'] ) ) ) {
    wp_redirect( site_url( '/area-de-membros/' ) );
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="bg-black">
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Assinatura | Elite LMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;900&family=Playfair+Display:ital,wght@1,700;1,900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .glass { background: rgba(255, 255, 255, 0.03); backdrop-blur: 10px; }
        .gold-gradient { background: linear-gradient(to right, #D4AF37, #F2D480); }
    </style>
</head>
<body class="text-white min-h-screen flex items-center justify-center p-6 md:p-12 relative overflow-hidden">

    <!-- Background Elements -->
    <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-gold-500/10 rounded-full blur-[120px]"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-gold-500/5 rounded-full blur-[120px]"></div>

    <div class="max-w-2xl w-full relative z-10">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-5xl font-serif italic text-gold-500 mb-4">Sentiremos sua falta...</h1>
            <p class="text-zinc-400 text-sm uppercase tracking-[0.3em]">Gestão de Assinatura Elite</p>
        </div>

        <!-- Survey Card -->
        <div class="glass border border-white/10 rounded-[40px] p-8 md:p-12 shadow-2xl">
            <div id="survey-form">
                <p class="text-lg text-zinc-300 mb-8 leading-relaxed">
                    Olá, <strong class="text-white"><?php echo esc_html($user_data->display_name); ?></strong>. Antes de prosseguir com o cancelamento, gostaríamos de entender o que poderíamos ter feito melhor. Seu feedback é fundamental para a nossa evolução.
                </p>

                <div class="space-y-6">
                    <label class="block">
                        <span class="text-[10px] text-gold-400 font-bold uppercase tracking-widest block mb-4">Por que você deseja cancelar hoje?</span>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <label class="flex items-center gap-3 p-4 bg-white/5 border border-white/5 rounded-2xl cursor-pointer hover:bg-white/10 hover:border-gold-500/30 transition-all group">
                                <input type="radio" name="cancel_reason" value="Preço/Financeiro" class="w-4 h-4 accent-gold-500">
                                <span class="text-xs text-zinc-400 group-hover:text-white transition-colors">Questões Financeiras</span>
                            </label>
                            <label class="flex items-center gap-3 p-4 bg-white/5 border border-white/5 rounded-2xl cursor-pointer hover:bg-white/10 hover:border-gold-500/30 transition-all group">
                                <input type="radio" name="cancel_reason" value="Falta de Tempo" class="w-4 h-4 accent-gold-500">
                                <span class="text-xs text-zinc-400 group-hover:text-white transition-colors">Falta de Tempo</span>
                            </label>
                            <label class="flex items-center gap-3 p-4 bg-white/5 border border-white/5 rounded-2xl cursor-pointer hover:bg-white/10 hover:border-gold-500/30 transition-all group">
                                <input type="radio" name="cancel_reason" value="Conteúdo" class="w-4 h-4 accent-gold-500">
                                <span class="text-xs text-zinc-400 group-hover:text-white transition-colors">Conteúdo / Aulas</span>
                            </label>
                            <label class="flex items-center gap-3 p-4 bg-white/5 border border-white/5 rounded-2xl cursor-pointer hover:bg-white/10 hover:border-gold-500/30 transition-all group">
                                <input type="radio" name="cancel_reason" value="Outro" class="w-4 h-4 accent-gold-500">
                                <span class="text-xs text-zinc-400 group-hover:text-white transition-colors">Outros Motivos</span>
                            </label>
                        </div>
                    </label>

                    <div class="space-y-2">
                        <label for="extra_details" class="text-[10px] text-gold-400 font-bold uppercase tracking-widest block">Conte-nos um pouco mais (Opcional)</label>
                        <textarea id="extra_details" rows="4" class="w-full bg-black/40 border border-white/10 rounded-2xl p-4 text-sm text-white focus:border-gold-500/50 outline-none transition-all placeholder-zinc-700" placeholder="Sua opinião sincera nos ajuda muito..."></textarea>
                    </div>

                    <div class="pt-6 flex flex-col md:flex-row items-center gap-4">
                        <button onclick="processCancellation()" id="btn-cancel" class="w-full md:w-auto px-10 py-4 bg-zinc-900 border border-red-500/30 text-red-500 rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-red-500 hover:text-white transition-all shadow-lg hover:shadow-red-500/20 active:scale-95">
                            Confirmar Cancelamento
                        </button>
                        <a href="<?php echo site_url('/area-de-membros/'); ?>" class="text-zinc-500 hover:text-white text-xs font-bold uppercase tracking-widest transition-all">
                            Desistir e Voltar
                        </a>
                    </div>
                </div>
            </div>

            <!-- Success Message (Hidden) -->
            <div id="success-view" class="hidden text-center py-10 space-y-6 animate-fade-in">
                <div class="w-20 h-20 bg-emerald-500/20 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-6 border border-emerald-500/30">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h2 class="text-3xl font-serif italic text-white">Cancelamento Realizado</h2>
                <p class="text-zinc-400 text-sm leading-relaxed max-w-sm mx-auto">
                    Sua assinatura foi cancelada. Seu acesso aos treinamentos e benefícios da rede permanece ativo até <strong class="text-gold-400"><?php echo date_i18n('d/m/Y', strtotime($expiry_date)); ?></strong>.
                </p>
                <div class="pt-6">
                    <a href="<?php echo site_url('/area-de-membros/'); ?>" class="gold-gradient px-10 py-4 rounded-xl text-black text-xs font-black uppercase tracking-widest inline-block shadow-xl shadow-gold-500/20 hover:scale-105 transition-all">Voltar ao Dashboard</a>
                </div>
            </div>

            <!-- Error Message (Hidden) -->
            <div id="error-view" class="hidden text-center py-10 space-y-6">
                <div class="w-20 h-20 bg-red-500/20 text-red-500 rounded-full flex items-center justify-center mx-auto mb-6 border border-red-500/30">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                </div>
                <h2 class="text-3xl font-serif italic text-white">Ops! Algo deu errado.</h2>
                <p id="error-msg" class="text-zinc-400 text-sm leading-relaxed"></p>
                <div class="pt-6">
                    <button onclick="window.location.reload()" class="bg-zinc-800 px-10 py-4 rounded-xl text-white text-xs font-bold uppercase tracking-widest inline-block transition-all">Tentar Novamente</button>
                </div>
            </div>
        </div>

        <p class="text-center text-[10px] text-zinc-600 uppercase tracking-widest mt-10">
            Elite LMS &copy; <?php echo date('Y'); ?> | Sistema de Gestão de Membros Premium
        </p>
    </div>

    <script>
        async function processCancellation() {
            const reasonEl = document.querySelector('input[name="cancel_reason"]:checked');
            const details = document.getElementById('extra_details').value;
            const btn = document.getElementById('btn-cancel');

            if (!reasonEl) {
                alert('Por favor, selecione um motivo para o cancelamento.');
                return;
            }

            const reason = reasonEl.value + (details ? ': ' + details : '');

            if (!confirm('Tem certeza que deseja cancelar sua assinatura Elite? Seu acesso será mantido apenas até o fim da vigência atual.')) {
                return;
            }

            btn.disabled = true;
            btn.innerHTML = 'Processando...';
            btn.style.opacity = '0.5';

            const formData = new FormData();
            formData.append('action', 'lms_cancel_subscription_request');
            formData.append('reason', reason);
            formData.append('nonce', '<?php echo wp_create_nonce("lms_engine_nonce"); ?>');

            try {
                const response = await fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    document.getElementById('survey-form').classList.add('hidden');
                    document.getElementById('success-view').classList.remove('hidden');
                } else {
                    document.getElementById('survey-form').classList.add('hidden');
                    document.getElementById('error-view').classList.remove('hidden');
                    document.getElementById('error-msg').innerText = data.data || 'Erro desconhecido ao processar cancelamento.';
                }
            } catch (e) {
                document.getElementById('survey-form').classList.add('hidden');
                document.getElementById('error-view').classList.remove('hidden');
                document.getElementById('error-msg').innerText = 'Falha na conexão com o servidor. Verifique sua internet.';
            }
        }
    </script>
</body>
</html>
