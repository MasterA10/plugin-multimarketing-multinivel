<?php
/**
 * Template: Visualizador de Logs de Debug
 * Interface Premium para análise de logs do sistema Elite LMS.
 */
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Acesso negado.' );
}

// Logic for saving log settings
if ( isset( $_POST['lms_save_log_settings'] ) && check_admin_referer( 'lms_save_log_nonce' ) ) {
    $enable_woo_log = isset( $_POST['enable_woo_logging'] ) ? 'yes' : 'no';
    $force_completed = isset( $_POST['force_orders_completed'] ) ? 'yes' : 'no';
    
    update_option( 'lms_enable_woo_logging', $enable_woo_log );
    update_option( 'lms_force_orders_completed', $force_completed );
    
    echo '<div class="updated notice is-dismissible" style="background:#111; border-left:4px solid #D4AF37; margin:10px 0 20px 0;"><p style="color:#D4AF37;">Configurações de Auditoria atualizadas!</p></div>';
}

$enable_woo_log = get_option( 'lms_enable_woo_logging', 'no' );
$force_completed = get_option( 'lms_force_orders_completed', 'no' );
$log_contents = Expressive_Logger::get_log_contents( 500 );
$log_size = Expressive_Logger::get_log_size();
$log_path = Expressive_Logger::get_log_path();

// Parse log lines for rendering
$log_lines = array_filter( explode( "\n", $log_contents ) );
$log_lines = array_reverse( $log_lines ); // Most recent first

// Filter support
$filter_level = isset( $_GET['level'] ) ? sanitize_text_field( $_GET['level'] ) : '';
$filter_search = isset( $_GET['search'] ) ? sanitize_text_field( $_GET['search'] ) : '';
?>

<div class="elite-log-wrap bg-[#0a0a0a] text-white min-h-screen p-8 rounded-xl shadow-2xl mr-4 mt-4 font-sans">
    
    <!-- Header -->
    <header class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-8 border-b border-white/5 pb-8 gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-gold-500/10 rounded-xl flex items-center justify-center text-gold-500 border border-gold-500/20">
                <span class="dashicons dashicons-media-text" style="font-size: 24px; width: 24px; height: 24px;"></span>
            </div>
            <div>
                <h1 class="font-serif italic text-3xl mb-1 leading-tight" style="color: #D4AF37 !important;">Logs de Debug</h1>
                <p class="text-[10px] uppercase tracking-[0.25em] font-medium" style="color: rgba(242, 212, 128, 0.6) !important;">Central de Auditoria & Diagnóstico</p>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <!-- Log Stats -->
            <div class="flex items-center gap-3 bg-white/5 px-4 py-2 rounded-xl border border-white/10">
                <span class="text-[9px] text-zinc-500 uppercase tracking-widest">Tamanho</span>
                <span class="text-xs font-bold font-mono <?php echo $log_size > 4194304 ? 'text-red-400' : 'text-gold-400'; ?>"><?php echo size_format( $log_size ); ?></span>
            </div>
            <div class="flex items-center gap-3 bg-white/5 px-4 py-2 rounded-xl border border-white/10">
                <span class="text-[9px] text-zinc-500 uppercase tracking-widest">Entradas</span>
                <span class="text-xs font-bold font-mono text-gold-400"><?php echo count( $log_lines ); ?></span>
            </div>
        </div>
    </header>

    <!-- Logging Settings Bar -->
    <section class="mb-10 bg-white/5 border border-white/10 rounded-2xl p-6 flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 bg-gold-500/10 rounded-lg flex items-center justify-center text-gold-500 border border-gold-500/10">
                <span class="dashicons dashicons-visibility" style="font-size: 18px; width: 18px; height: 18px;"></span>
            </div>
            <div>
                <h3 class="text-sm font-bold text-gray-200">Rastreamento de Ecossistema</h3>
                <p class="text-[10px] text-zinc-500 italic">Determine quais eventos externos serão capturados pelo auditor.</p>
            </div>
        </div>
        
        <form method="post" action="" class="flex flex-wrap items-center gap-8">
            <?php wp_nonce_field( 'lms_save_log_nonce' ); ?>
            <div class="flex items-center gap-4">
                <span class="text-[11px] font-bold uppercase tracking-widest text-zinc-400">Auditoria WooCommerce</span>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="enable_woo_logging" value="yes" <?php checked( $enable_woo_log, 'yes' ); ?> class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-gold-500"></div>
                </label>
            </div>

            <div class="flex items-center gap-4 border-l border-white/5 pl-8">
                <span class="text-[11px] font-bold uppercase tracking-widest text-zinc-400">Forçar Status Concluído (Debug)</span>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="force_orders_completed" value="yes" <?php checked( $force_completed, 'yes' ); ?> class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                </label>
            </div>
            
            <button type="submit" name="lms_save_log_settings" class="bg-gold-500/10 hover:bg-gold-500/20 text-gold-400 px-6 py-2 rounded-xl text-[10px] font-bold uppercase tracking-widest transition-all border border-gold-500/20 ml-auto">
                Salvar Configurações
            </button>
        </form>
    </section>

    <!-- Filters -->
    <div class="flex flex-wrap items-center gap-3 mb-6">
        <form method="get" class="flex flex-wrap items-center gap-3">
            <input type="hidden" name="page" value="elite-logs">
            
            <!-- Level filter -->
            <select name="level" class="bg-black/60 border border-white/10 rounded-lg px-3 py-2 text-xs text-white outline-none focus:border-gold-500/50">
                <option value="">Todos os Níveis</option>
                <option value="INFO" <?php selected( $filter_level, 'INFO' ); ?>>ℹ️ INFO</option>
                <option value="WARNING" <?php selected( $filter_level, 'WARNING' ); ?>>⚠️ WARNING</option>
                <option value="ERROR" <?php selected( $filter_level, 'ERROR' ); ?>>🔴 ERROR</option>
                <option value="DEBUG" <?php selected( $filter_level, 'DEBUG' ); ?>>🔧 DEBUG</option>
            </select>

            <!-- Search -->
            <input type="text" name="search" value="<?php echo esc_attr( $filter_search ); ?>" placeholder="Buscar nos logs..." class="bg-black/60 border border-white/10 rounded-lg px-4 py-2 text-xs text-white outline-none focus:border-gold-500/50 w-64 placeholder-zinc-600">

            <button type="submit" class="bg-gold-500/10 hover:bg-gold-500/20 text-gold-400 px-4 py-2 rounded-lg text-[10px] font-bold uppercase tracking-widest transition-all border border-gold-500/20">
                Filtrar
            </button>

            <?php if ( $filter_level || $filter_search ) : ?>
                <a href="<?php echo admin_url( 'admin.php?page=elite-logs' ); ?>" class="text-zinc-500 hover:text-white text-[10px] uppercase tracking-widest transition-colors">✕ Limpar Filtros</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Log Viewer -->
    <div class="bg-black/60 rounded-2xl border border-white/5 overflow-hidden">
        <div class="px-6 py-3 bg-white/5 border-b border-white/5 flex items-center justify-between">
            <span class="text-[9px] text-zinc-500 uppercase tracking-widest font-bold">Saída do Log (mais recentes primeiro)</span>
            <span class="text-[9px] text-zinc-600 font-mono"><?php echo esc_html( $log_path ); ?></span>
        </div>
        <div class="p-4 max-h-[70vh] overflow-y-auto font-mono text-[11px] leading-relaxed space-y-0.5" id="log-output">
            <?php if ( empty( $log_lines ) ) : ?>
                <div class="text-center py-20">
                    <p class="text-zinc-600 text-sm">Nenhum log registrado ainda.</p>
                    <p class="text-zinc-700 text-[10px] mt-2">Os logs aparecerão aqui conforme o sistema opera.</p>
                </div>
            <?php else : ?>
                <?php foreach ( $log_lines as $line ) : 
                    $line = trim( $line );
                    if ( empty( $line ) ) continue;

                    // Apply filters
                    if ( $filter_level && strpos( $line, "[$filter_level]" ) === false ) continue;
                    if ( $filter_search && stripos( $line, $filter_search ) === false ) continue;

                    // Color coding
                    $line_class = 'text-zinc-400';
                    if ( strpos( $line, '[ERROR]' ) !== false ) {
                        $line_class = 'text-red-400 bg-red-900/10';
                    } elseif ( strpos( $line, '[WARNING]' ) !== false ) {
                        $line_class = 'text-amber-400 bg-amber-900/5';
                    } elseif ( strpos( $line, '[INFO]' ) !== false ) {
                        $line_class = 'text-emerald-400/80';
                    } elseif ( strpos( $line, '[DEBUG]' ) !== false ) {
                        $line_class = 'text-blue-400/60';
                    }

                    // Highlight categories
                    $line_html = esc_html( $line );
                    $line_html = preg_replace( '/\[(ACCESS|ENGINE|REFERRAL|API|GAMIFY|AUTH|CERT|CORE|DISCOUNT)\]/', '<span class="text-gold-400 font-bold">[$1]</span>', $line_html );
                    $line_html = preg_replace( '/\[(INFO|WARNING|ERROR|DEBUG)\]/', '<span class="font-bold">[$1]</span>', $line_html );
                    // Highlight JSON context
                    $line_html = preg_replace( '/\| (\{.*\})$/', '| <span class="text-zinc-600">$1</span>', $line_html );
                ?>
                    <div class="<?php echo $line_class; ?> px-3 py-1 rounded hover:bg-white/5 transition-colors whitespace-nowrap"><?php echo $line_html; ?></div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Auto-refresh toggle -->
    <div class="flex items-center justify-between mt-4">
        <label class="flex items-center gap-3 cursor-pointer group">
            <input type="checkbox" id="auto-refresh-toggle" class="w-4 h-4 rounded">
            <span class="text-[10px] text-zinc-500 uppercase tracking-widest group-hover:text-white transition-colors">Auto-refresh (10s)</span>
        </label>
        <button onclick="window.location.reload();" class="text-zinc-600 hover:text-gold-400 text-[10px] uppercase tracking-widest transition-colors">
            🔄 Atualizar Agora
        </button>
    </div>
</div>

<style>
    #wpcontent { background: #000 !important; padding-left: 0 !important; }
    #wpbody-content { padding-bottom: 0 !important; }
    .elite-log-wrap { font-family: 'Outfit', sans-serif !important; }
    .elite-log-wrap h1 { font-family: 'Playfair Display', serif !important; }

    .elite-log-wrap input[type="text"],
    .elite-log-wrap select {
        color: white !important;
        background-color: rgba(0,0,0,0.6) !important;
        border: 1px solid rgba(255,255,255,0.1) !important;
    }
    .elite-log-wrap select option {
        background-color: #111 !important;
        color: white !important;
    }

    /* Custom scrollbar for log output */
    #log-output::-webkit-scrollbar { width: 6px; }
    #log-output::-webkit-scrollbar-track { background: transparent; }
    #log-output::-webkit-scrollbar-thumb { background: rgba(212, 175, 55, 0.2); border-radius: 3px; }
    #log-output::-webkit-scrollbar-thumb:hover { background: rgba(212, 175, 55, 0.4); }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let refreshInterval = null;
    const toggle = document.getElementById('auto-refresh-toggle');

    if (toggle) {
        toggle.addEventListener('change', function() {
            if (this.checked) {
                refreshInterval = setInterval(function() {
                    window.location.reload();
                }, 10000);
            } else {
                if (refreshInterval) clearInterval(refreshInterval);
            }
        });
    }
});
</script>
