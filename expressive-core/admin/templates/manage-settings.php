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
    if ( isset( $_POST['educator_upgrade_link'] ) ) {
        update_option( 'lms_educator_upgrade_link', esc_url_raw( $_POST['educator_upgrade_link'] ) );
    }
    
    // Referral Product Restrictions
    if ( isset( $_POST['eligible_products'] ) ) {
        update_option( 'lms_eligible_products', sanitize_text_field( $_POST['eligible_products'] ) );
    }
    if ( isset( $_POST['excluded_discount_products'] ) ) {
        update_option( 'lms_excluded_discount_products', sanitize_text_field( $_POST['excluded_discount_products'] ) );
    }
    $all_eligible = isset( $_POST['all_products_eligible'] ) ? 'yes' : 'no';
    update_option( 'lms_all_products_eligible', $all_eligible );

    $enable_woo_log = isset( $_POST['enable_woo_logging'] ) ? 'yes' : 'no';
    update_option( 'lms_enable_woo_logging', $enable_woo_log );

    $show_commissions = isset( $_POST['show_commissions'] ) ? 'yes' : 'no';
    update_option( 'lms_show_commissions', $show_commissions );

    if ( isset( $_POST['commission_percentage'] ) ) {
        update_option( 'lms_commission_percentage', intval( $_POST['commission_percentage'] ) );
    }

    if ( isset( $_POST['required_approval'] ) ) {
        update_option( 'lms_required_approval', sanitize_text_field( $_POST['required_approval'] ) );
    }

    $enable_fallback = isset( $_POST['enable_role_fallback'] ) ? 'yes' : 'no';
    update_option( 'lms_enable_role_fallback', $enable_fallback );

    echo '<div class="updated notice is-dismissible"><p>Configurações salvas com sucesso!</p></div>';
}

// Logic for reset (handled in class but we replicate feedback here if needed)
if ( isset( $_POST['lms_reset_cycle'] ) && check_admin_referer( 'lms_reset_cycle_nonce' ) ) {
    $this->reset_annual_cycle();
    echo '<div class="updated notice is-dismissible"><p>Ciclo resetado! Ranking Marco Zero inicializado.</p></div>';
}

$current_end_date = get_option( 'lms_cycle_end_date', '2026-12-31' );
$educator_link = get_option( 'lms_educator_upgrade_link', '#' );
$eligible_products = get_option( 'lms_eligible_products', '' );
$all_eligible = get_option( 'lms_all_products_eligible', 'yes' );
$enable_woo_log = get_option( 'lms_enable_woo_logging', 'no' );
$show_commissions = get_option( 'lms_show_commissions', 'yes' );
$commission_pct = get_option( 'lms_commission_percentage', 10 );
$required_approval = get_option( 'lms_required_approval', 'none' );
$enable_fallback = get_option( 'lms_enable_role_fallback', 'yes' );
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
        <!-- Cycle & Referral Management Card -->
        <div class="glass p-8 rounded-3xl border border-white/5 relative overflow-hidden group">
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-gold-500/5 rounded-full blur-3xl group-hover:bg-gold-500/10 transition-all"></div>
            
            <h3 class="text-xl font-bold font-serif italic mb-6 flex items-center gap-3" style="color: #D4AF37 !important;">
                <span class="w-2 h-6 bg-gold-500 rounded-full"></span>
                Regras de Negócio e Ciclo
            </h3>
            
            <form method="post" action="" class="space-y-6">
                <?php wp_nonce_field( 'lms_save_cycle_nonce' ); ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label for="cycle_date" class="text-[11px] font-bold uppercase tracking-widest text-gold-400">Data de Encerramento do Ano</label>
                        <input name="cycle_date" type="date" id="cycle_date" value="<?php echo esc_attr( $current_end_date ); ?>" class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-gold-500/50 transition-all outline-none">
                        <p class="text-[9px] text-gray-500 italic mt-1">Limite para contagem do ranking anual.</p>
                    </div>

                    <div class="space-y-2">
                        <label for="all_products_eligible" class="text-[11px] font-bold uppercase tracking-widest text-gold-400 block mb-3">Elegibilidade de Produtos</label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input name="all_products_eligible" type="checkbox" id="all_products_eligible" value="yes" <?php checked( $all_eligible, 'yes' ); ?> class="w-5 h-5 bg-black/40 border border-white/10 rounded text-gold-500 focus:ring-gold-500">
                            <span class="text-sm text-gray-300 group-hover:text-white transition-colors">Qualquer Produto gera Indicação</span>
                        </label>
                        <p class="text-[9px] text-gray-500 italic mt-1">Se desmarcado, apenas os IDs abaixo serão válidos.</p>
                    </div>
                </div>

                <div class="space-y-3 pt-6 border-t border-white/5 <?php echo ($all_eligible === 'yes') ? 'opacity-30 pointer-events-none' : ''; ?>" id="eligible-products-container">
                    <label class="text-[11px] font-bold uppercase tracking-widest text-gold-400">IDs de Produtos Elegíveis</label>
                    <div class="tag-input-wrapper bg-black/40 border border-white/10 rounded-xl p-3 focus-within:border-gold-500/50 transition-all flex flex-wrap gap-2 items-center min-h-[56px]" id="eligible-tags-container">
                        <!-- Tags will be rendered here via JS -->
                        <input type="text" id="eligible-input-trigger" placeholder="Digite ID e Enter..." class="flex-1 bg-transparent border-none outline-none text-white text-sm min-w-[120px] p-1 font-mono">
                    </div>
                    <input name="eligible_products" type="hidden" id="eligible_products" value="<?php echo esc_attr( $eligible_products ); ?>">
                    <p class="text-[9px] text-gray-500 italic mt-1 font-serif">Pressione <strong>Enter</strong> ou <strong>Vírgula</strong> para adicionar. Apenas números.</p>
                </div>

                <!-- NEW: Excluded from Discount -->
                <div class="space-y-3 pt-6 border-t border-white/5">
                    <label class="text-[11px] font-bold uppercase tracking-widest text-gold-400">IDs de Produtos Excluídos do Desconto (30%/40%)</label>
                    <div class="tag-input-wrapper bg-black/40 border border-white/10 rounded-xl p-3 focus-within:border-gold-500/50 transition-all flex flex-wrap gap-2 items-center min-h-[56px]" id="excluded-tags-container">
                        <!-- Tags will be rendered here via JS -->
                        <input type="text" id="excluded-input-trigger" placeholder="Digite ID e Enter..." class="flex-1 bg-transparent border-none outline-none text-white text-sm min-w-[120px] p-1 font-mono">
                    </div>
                    <?php $excluded_ids = get_option( 'lms_excluded_discount_products', '' ); ?>
                    <input name="excluded_discount_products" type="hidden" id="excluded_discount_products" value="<?php echo esc_attr( $excluded_ids ); ?>">
                    <p class="text-[9px] text-gray-500 italic mt-1 font-serif">Os produtos listados aqui não terão desconto Elite aplicado nas regras de checkout.</p>
                </div>

                <div class="space-y-4 pt-4 border-t border-white/5">
                    <label for="educator_upgrade_link" class="text-[11px] font-bold uppercase tracking-widest text-gold-400">Link de Upgrade para Educadora</label>
                    <input name="educator_upgrade_link" type="url" id="educator_upgrade_link" value="<?php echo esc_attr( $educator_link ); ?>" placeholder="https://..." class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-gold-500/50 transition-all outline-none">
                    <p class="text-[11px] text-gray-300 leading-relaxed italic mt-1">Este link será aplicado ao botão "Quero ser Educadora" que aparece para usuários de nível Autoridade no Dashboard.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6 border-t border-white/5">
                    <div class="space-y-4">
                        <label for="show_commissions" class="text-[11px] font-bold uppercase tracking-widest text-gold-400 block mb-3">Visibilidade Financeira (Área de Membros)</label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input name="show_commissions" type="checkbox" id="show_commissions" value="yes" <?php checked( $show_commissions, 'yes' ); ?> class="w-5 h-5 bg-black/40 border border-white/10 rounded text-gold-500 focus:ring-gold-500">
                            <span class="text-sm text-gray-300 group-hover:text-white transition-colors">Exibir Valores e Comissões na Rede</span>
                        </label>
                        <p class="text-[9px] text-gray-500 italic mt-1 font-serif">Se desmarcado, todos os valores de R$, vendas e comissões serão ocultados do Dashboard do aluno.</p>
                    </div>

                    <div class="space-y-2">
                        <label for="commission_percentage" class="text-[11px] font-bold uppercase tracking-widest text-gold-400">Porcentagem de Comissão (%)</label>
                        <div class="relative">
                            <input name="commission_percentage" type="number" id="commission_percentage" value="<?php echo esc_attr( $commission_pct ); ?>" min="0" max="100" class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-gold-500/50 transition-all outline-none font-mono">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gold-500 font-bold opacity-50">%</span>
                        </div>
                        <p class="text-[9px] text-gray-500 italic mt-1 font-serif">Valor aplicado sobre o total de cada venda indicada.</p>
                    </div>
                </div>

                <div class="pt-6 border-t border-white/5 space-y-6">
                    <div class="space-y-4 max-w-md">
                        <label for="required_approval" class="text-[11px] font-bold uppercase tracking-widest text-gold-400 block">Rigor de Cadastro & Aprovação (Roles)</label>
                        <select name="required_approval" id="required_approval" class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-gold-500/50 transition-all outline-none">
                            <option value="none" <?php selected($required_approval, 'none'); ?>>Ninguém (Acesso Livre Imediato)</option>
                            <option value="educadora" <?php selected($required_approval, 'educadora'); ?>>Apenas Educadoras</option>
                            <option value="autoridade" <?php selected($required_approval, 'autoridade'); ?>>Apenas Autoridades</option>
                            <option value="both" <?php selected($required_approval, 'both'); ?>>Ambas Precisam de Aprovação Individual</option>
                        </select>
                    </div>

                    <div class="space-y-4 max-w-md bg-white/[0.02] p-6 rounded-2xl border border-white/5">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input name="enable_role_fallback" type="checkbox" id="enable_role_fallback" value="yes" <?php checked( $enable_fallback, 'yes' ); ?> class="w-5 h-5 bg-black/40 border border-white/10 rounded text-gold-500 focus:ring-gold-500">
                            <span class="text-sm font-bold text-white group-hover:text-gold-500 transition-colors uppercase tracking-tight">Habilitar Fallback (Educadora → Autoridade)</span>
                        </label>
                        <p class="text-[10px] text-gray-500 leading-relaxed italic">
                            <strong>Sim:</strong> Novas Educadoras viram Autoridades na hora e ganham 30% de desconto enquanto esperam sua aprovação manual para o nível de 40%.<br>
                            <strong>Não:</strong> Elas ficam como usuários comuns (sem descontos) até você aprovar.
                        </p>
                    </div>
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
            <h4 class="text-[10px] font-bold uppercase tracking-[0.3em] mb-4" style="color: #D4AF37 !important;">Monitoramento do Ecossistema</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <span class="text-xs text-gray-500">Motor Gamificado</span>
                    <span class="text-[10px] bg-gold-400/10 text-gold-400 px-2 py-1 rounded font-bold uppercase tracking-widest">Ativo</span>
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
    .elite-admin-wrap input[type="url"],
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

    /* Tag Input Styles */
    .tag-chip {
        background: linear-gradient(135deg, rgba(212, 175, 55, 0.1), rgba(242, 212, 128, 0.05));
        border: 1px solid rgba(212, 175, 55, 0.3);
        color: #D4AF37;
        padding: 4px 10px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        font-weight: 700;
        font-family: 'Outfit', sans-serif;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        animation: elite-chip-in 0.2s ease-out;
    }

    @keyframes elite-chip-in { from { opacity: 0; scale: 0.9; transform: translateY(2px); } to { opacity: 1; scale: 1; transform: translateY(0); } }

    .tag-chip .remove-tag {
        width: 16px;
        height: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(212, 175, 55, 0.2);
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 8px;
        color: #D4AF37;
    }

    .tag-chip .remove-tag:hover {
        background: #D4AF37;
        color: #000;
    }

    .tag-input-trigger::placeholder {
        color: rgba(255,255,255,0.2);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. All Products Eligible Checkbox Logic
    const checkbox = document.getElementById('all_products_eligible');
    const container = document.getElementById('eligible-products-container');
    
    if (checkbox && container) {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                container.classList.add('opacity-30', 'pointer-events-none');
            } else {
                container.classList.remove('opacity-30', 'pointer-events-none');
            }
        });
    }

    // 2. Interactive Interactive ID Tag Logic
    const initIDSys = (wrapperId, triggerId, hiddenId) => {
        const wrapper = document.getElementById(wrapperId);
        const trigger = document.getElementById(triggerId);
        const hiddenField = document.getElementById(hiddenId);
        
        if (!wrapper || !trigger || !hiddenField) return;

        let ids = hiddenField.value.split(',').map(s => s.trim()).filter(s => s !== '');

        const renderTags = () => {
            // Clear existing tags but keep trigger input
            wrapper.querySelectorAll('.tag-chip').forEach(t => t.remove());
            
            ids.forEach((id, index) => {
                const chip = document.createElement('div');
                chip.className = 'tag-chip';
                chip.innerHTML = `
                    <span>${id}</span>
                    <span class="remove-tag" onclick="removeEliteID('${hiddenId}', ${index})">✕</span>
                `;
                wrapper.insertBefore(chip, trigger);
            });
            
            hiddenField.value = ids.join(',');
        };

        // Window exposed function for click removal
        window.removeEliteID = (hid, idx) => {
            const hf = document.getElementById(hid);
            if (!hf) return;
            let currentIds = hf.value.split(',').filter(s => s !== '');
            currentIds.splice(idx, 1);
            hf.value = currentIds.join(',');
            // Re-render both to be safe
            initIDSys('eligible-tags-container', 'eligible-input-trigger', 'eligible_products');
            initIDSys('excluded-tags-container', 'excluded-input-trigger', 'excluded_discount_products');
        };

        trigger.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                const val = trigger.value.replace(/[^0-9]/g, '').trim();
                
                if (val && !ids.includes(val)) {
                    ids.push(val);
                    trigger.value = '';
                    renderTags();
                }
            }
            if (e.key === 'Backspace' && trigger.value === '' && ids.length > 0) {
                ids.pop();
                renderTags();
            }
        });

        // Initialize first render
        renderTags();
    };

    initIDSys('eligible-tags-container', 'eligible-input-trigger', 'eligible_products');
    initIDSys('excluded-tags-container', 'excluded-input-trigger', 'excluded_discount_products');
});
</script>
