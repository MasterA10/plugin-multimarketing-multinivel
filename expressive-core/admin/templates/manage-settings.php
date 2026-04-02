<?php
/**
 * Template Name: Admin Settings Manager
 * 
 * Custom management interface for Gamification and System Settings.
 */
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Acesso negado.' );
}

// Logic for saving settings
if ( isset( $_POST['lms_save_settings'] ) && check_admin_referer( 'lms_save_cycle_nonce' ) ) {
    update_option( 'lms_cycle_end_date', sanitize_text_field( $_POST['cycle_date'] ) );
    echo '<div class="updated notice is-dismissible"><p>Configurações salvas com sucesso!</p></div>';
}

// Logic for reset (handled in class but we replicate feedback here if needed)
if ( isset( $_POST['lms_reset_cycle'] ) && check_admin_referer( 'lms_reset_cycle_nonce' ) ) {
    $this->reset_annual_cycle();
    echo '<div class="updated notice is-dismissible"><p>Ciclo resetado! Ranking Marco Zero inicializado.</p></div>';
}

$current_end_date = get_option( 'lms_cycle_end_date', '2026-12-31' );
?>

<div class="elite-admin-wrap bg-[#111] text-white min-h-screen p-8 rounded-xl shadow-2xl mr-4 mt-4 font-sans max-w-4xl">
    
    <!-- Header -->
    <header class="flex justify-between items-center mb-10 border-b border-white/5 pb-8">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-gold-500/10 rounded-xl flex items-center justify-center text-gold-500 border border-gold-500/20">
                <span class="dashicons dashicons-admin-settings" style="font-size: 24px; width: 24px; height: 24px;"></span>
            </div>
            <div>
                <h1 class="font-serif italic text-3xl mb-1 leading-tight" style="color: #D4AF37 !important;">Configurações de Elite</h1>
                <p class="text-[10px] uppercase tracking-[0.25em] font-medium" style="color: rgba(242, 212, 128, 0.6) !important;">Gestão de Ciclo & Regras de Negócio</p>
            </div>
        </div>
    </header>

    <div class="space-y-8">
        <!-- Cycle Management Card -->
        <div class="glass p-8 rounded-3xl border border-white/5 relative overflow-hidden group">
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-gold-500/5 rounded-full blur-3xl group-hover:bg-gold-500/10 transition-all"></div>
            
            <h3 class="text-xl font-bold font-serif italic mb-6 flex items-center gap-3" style="color: #D4AF37 !important;">
                <span class="w-2 h-6 bg-gold-500 rounded-full"></span>
                Programação do Ciclo (Marco Zero)
            </h3>
            
            <form method="post" action="" class="space-y-6">
                <?php wp_nonce_field( 'lms_save_cycle_nonce' ); ?>
                
                <div class="space-y-2">
                    <label for="cycle_date" class="text-[11px] font-bold uppercase tracking-widest text-gold-400">Data de Encerramento do Ano</label>
                    <input name="cycle_date" type="date" id="cycle_date" value="<?php echo esc_attr( $current_end_date ); ?>" class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-gold-500/50 transition-all outline-none">
                    <p class="text-[11px] text-gray-300 leading-relaxed italic mt-2">Esta data define o limite para a contagem do ranking anual. Os resultados serão congelados para a premiação Top 3.</p>
                </div>

                <div class="pt-4">
                    <button type="submit" name="lms_save_settings" class="px-10 py-4 rounded-xl text-xs font-bold uppercase tracking-widest transition-all shadow-lg hover:shadow-gold-500/20 transform hover:scale-[1.02]" style="background: linear-gradient(to right, #D4AF37, #F2D480) !important; color: #000 !important; border: none;">
                        Salvar Configurações
                    </button>
                </div>
            </form>
        </div>

        <!-- Critical Actions Card -->
        <div class="bg-red-900/5 p-8 rounded-3xl border border-red-900/20 relative overflow-hidden group">
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-red-900/5 rounded-full blur-3xl group-hover:bg-red-900/10 transition-all"></div>
            
            <h3 class="text-xl font-bold font-serif italic text-red-500 mb-6 flex items-center gap-3">
                <span class="w-2 h-6 bg-red-500 rounded-full animate-pulse"></span>
                Ações de Reset de Rede
            </h3>
            
            <p class="text-[13px] text-gray-200 leading-relaxed mb-8 max-w-2xl">
                O reset do ciclo é uma ação irreversível. Ao realizar o **Marco Zero**, todas as autoridades vinculadas no ciclo atual serão zeradas para todos os educadores, permitindo que a nova jornada anual comece com igualdade de condições.
            </p>

            <form method="post" action="" onsubmit="return confirm('ATENÇÃO: Você está prestes a resetar o Ciclo Anual. Todos os rankings de autoridades serão zerados. Deseja prosseguir?');">
                <?php wp_nonce_field( 'lms_reset_cycle_nonce' ); ?>
                <button type="submit" name="lms_reset_cycle" class="bg-transparent hover:bg-red-900/20 px-8 py-3 rounded-xl text-xs font-bold uppercase tracking-widest transition-all" style="color: #ef4444 !important; border: 1px solid rgba(239, 68, 68, 0.5) !important;">
                    Executar Marco Zero (Reset Geral)
                </button>
            </form>
        </div>

        <!-- System Info Card -->
        <div class="glass p-8 rounded-3xl border border-white/5">
            <h4 class="text-[10px] font-bold uppercase tracking-[0.3em] mb-4" style="color: #D4AF37 !important;">Motor de Inteligência Gamificada</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-black/40 p-4 rounded-xl border border-white/5 flex items-center justify-between">
                    <span class="text-xs text-gray-500">Log de Vendas</span>
                    <span class="text-[10px] bg-green-500/10 text-green-500 px-2 py-1 rounded font-bold">SINCRONIZADO</span>
                </div>
                <div class="bg-black/40 p-4 rounded-xl border border-white/5 flex items-center justify-between">
                    <span class="text-xs text-gray-500">Cálculo de Ranking</span>
                    <span class="text-[10px] bg-gold-400/10 text-gold-400 px-2 py-1 rounded font-bold">ATIVO</span>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
    #wpcontent { background: #000 !important; padding-left: 0 !important; }
    #wpbody-content { padding-bottom: 0 !important; }
    .elite-admin-wrap { font-family: 'Outfit', sans-serif !important; }
    .elite-admin-wrap h1, .elite-admin-wrap h3 { font-family: 'Playfair Display', serif !important; }
    .glass { background: rgba(255, 255, 255, 0.02); }
    input[type="date"]::-webkit-calendar-picker-indicator {
        filter: invert(1);
        cursor: pointer;
    }

    /* Override inherited WP Admin Gray Styles */
    .elite-admin-wrap input[type="date"], 
    .elite-admin-wrap input[type="text"],
    .elite-admin-wrap select {
        color: white !important;
        background-color: rgba(0,0,0,0.6) !important;
        border: 1px solid rgba(255,255,255,0.1) !important;
        padding-top: 12px !important;
        padding-bottom: 12px !important;
    }

    .elite-admin-wrap select option {
        background-color: #111 !important;
        color: white !important;
    }
</style>
