<?php
/**
 * Template Name: Cancelar Assinatura
 */
if ( ! is_user_logged_in() ) {
    auth_redirect();
}

$user_id = get_current_user_id();
$user_data = get_userdata( $user_id );
$api_status = get_user_meta($user_id, '_lms_elite_api_status', true);

// If already inactive, redirect to dashboard
if ( $api_status === 'inactive' ) {
    wp_safe_redirect( home_url( '/area-de-membros/?tab=subscription' ) );
    exit;
}

get_header(); 
?>

<div class="elite-cancel-page bg-[#000] text-white min-h-screen flex items-center justify-center p-6 font-sans">
    <div class="glass max-w-xl w-full p-10 md:p-16 rounded-[60px] border border-white/10 text-center relative overflow-hidden">
        <!-- Abstract Background -->
        <div class="absolute -right-20 -top-20 w-64 h-64 bg-red-500/5 rounded-full blur-3xl"></div>
        <div class="absolute -left-20 -bottom-20 w-64 h-64 bg-gold-500/5 rounded-full blur-3xl"></div>

        <div class="relative z-10">
            <div class="w-20 h-20 bg-red-500/10 rounded-3xl flex items-center justify-center text-red-500 mx-auto mb-10 border border-red-500/20">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>

            <h1 class="text-4xl font-serif italic text-white mb-6">Sentiremos sua falta, <?php echo esc_html($user_data->first_name ?: $user_data->display_name); ?>.</h1>
            <p class="text-gray-400 text-sm leading-relaxed mb-10">
                Ao confirmar o cancelamento, sua assinatura deixará de ser renovada automaticamente. 
                No entanto, você manterá **acesso total à plataforma até o final do seu período atual**.
            </p>

            <div class="space-y-6">
                <div class="text-left">
                    <label class="text-[10px] font-bold uppercase tracking-widest text-zinc-500 ml-4 mb-2 block">Por que você deseja cancelar?</label>
                    <textarea id="cancel-reason" placeholder="Ex: Não tenho tempo para estudar, problemas financeiros, etc..." class="w-full bg-white/5 border border-white/10 rounded-3xl p-6 text-white text-sm focus:border-gold-500 outline-none h-32 resize-none transition-all placeholder:text-zinc-700"></textarea>
                </div>

                <div class="flex flex-col gap-4">
                    <button id="confirm-cancel-btn" onclick="executeCancellation()" class="w-full py-5 bg-red-600 hover:bg-red-700 text-white rounded-2xl text-[11px] font-black uppercase tracking-[0.2em] transition-all shadow-xl shadow-red-600/10 active:scale-95">
                        Confirmar Cancelamento
                    </button>
                    <a href="<?php echo home_url('/area-de-membros/'); ?>" class="w-full py-5 bg-white/5 hover:bg-white/10 text-zinc-400 hover:text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] transition-all border border-white/5">
                        Manter meu Acesso Elite
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function executeCancellation() {
        const btn = document.getElementById('confirm-cancel-btn');
        const reason = document.getElementById('cancel-reason').value;
        
        if (btn.disabled) return;
        
        btn.innerHTML = 'Processando...';
        btn.disabled = true;

        const formData = new FormData();
        formData.append('action', 'lms_cancel_subscription_request');
        formData.append('nonce', '<?php echo wp_create_nonce("lms_engine_nonce"); ?>');
        formData.append('reason', reason);

        fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.data);
                window.location.href = '<?php echo home_url("/area-de-membros/?tab=subscription&status=cancelled"); ?>';
            } else {
                alert(data.data || 'Erro ao processar cancelamento.');
                btn.innerHTML = 'Confirmar Cancelamento';
                btn.disabled = false;
            }
        })
        .catch(err => {
            console.error('Cancellation Error:', err);
            alert('Erro de conexão.');
            btn.innerHTML = 'Confirmar Cancelamento';
            btn.disabled = false;
        });
    }
</script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;700;900&family=Playfair+Display:ital,wght@1,700;1,900&display=swap');
    .elite-cancel-page { font-family: 'Outfit', sans-serif !important; }
    .elite-cancel-page h1 { font-family: 'Playfair Display', serif !important; }
    .glass { background: rgba(255, 255, 255, 0.02); backdrop-filter: blur(20px); }
    #wpadminbar { display: none !important; }
    html { margin-top: 0 !important; }
</style>

<?php get_footer(); ?>
