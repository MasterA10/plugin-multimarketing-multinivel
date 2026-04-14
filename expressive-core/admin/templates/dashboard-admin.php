<?php
/**
 * Template Name: Admin Hub Dashboard
 * 
 * Premium admin dashboard for the Elite LMS.
 */
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Acesso negado.' );
}

// Data Logic for Admin
global $wpdb;

// Handle Form Submission for Manual Linking
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'manual_link_authority' ) {
    $auth_id = intval($_POST['authority_id']);
    $edu_id = intval($_POST['educator_id']);
    
    if ( $auth_id && $edu_id && $auth_id !== $edu_id ) {
        $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}lms_referrals WHERE authority_id = %d", $auth_id));
        $edu_user = get_userdata($edu_id);
        $referred_role = Expressive_Referral::is_educator($auth_id) ? 'educadora' : 'autoridade';
        
        if ( ! $exists ) {
            $wpdb->insert(
                "{$wpdb->prefix}lms_referrals",
                array(
                    'educator_id'       => $edu_id,
                    'authority_id'      => $auth_id,
                    'order_id'          => 0,
                    'order_total'       => 0.00,
                    'commission_amount' => 0.00,
                    'referred_role'     => $referred_role
                ),
                array( '%d', '%d', '%d', '%f', '%f', '%s' )
            );
        } else {
            $wpdb->update(
                "{$wpdb->prefix}lms_referrals", 
                array('educator_id' => $edu_id, 'referred_role' => $referred_role), 
                array('authority_id' => $auth_id)
            );
        }
        
        update_user_meta( $auth_id, '_exp_referred_by', $edu_user->user_login );
        
        // Synchronize past orders if any
        if ( function_exists('wc_get_orders') ) {
            $orders = wc_get_orders( array('customer' => $auth_id, 'status' => array('wc-completed', 'wc-processing') ) );
            foreach( $orders as $order ) {
                $order->update_meta_data( '_exp_referred_by', $edu_user->user_login );
                $order->save();
            }
        }
        echo '<div style="background:#00a32a; color:#fff; padding:15px; text-align:center; font-weight:bold; border-radius:10px; margin-bottom: 20px;">Vínculo realizado com sucesso para '.$edu_user->user_login.'!</div>';
    } elseif ( isset($_POST['authority_id']) && $_POST['authority_id'] === $_POST['educator_id'] ) {
        echo '<div style="background:#d63638; color:#fff; padding:15px; text-align:center; font-weight:bold; border-radius:10px; margin-bottom: 20px;">Erro: Um usuário não pode indicar a si mesmo.</div>';
    }
}

// Handle Referral Deletion
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_referral' ) {
    $ref_id = intval($_POST['ref_id']);
    $ref_info = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}lms_referrals WHERE id = %d", $ref_id));
    if ($ref_info) {
        $wpdb->delete("{$wpdb->prefix}lms_referrals", array('id' => $ref_id));
        delete_user_meta($ref_info->authority_id, '_exp_referred_by');
        echo '<div style="background:#d63638; color:#fff; padding:15px; text-align:center; font-weight:bold; border-radius:10px; margin-bottom: 20px;">Vínculo removido com sucesso.</div>';
    }
}

// Contagem de Educadores (por cargo WordPress + meta do plugin)
$edu_meta_query = array(
    'relation' => 'OR',
    array('key' => '_lms_is_educator', 'value' => 'yes', 'compare' => '='),
    array('key' => '_lms_is_educator', 'value' => '1', 'compare' => '=')
);
$educators_by_meta = get_users(array('meta_query' => $edu_meta_query, 'fields' => 'ID'));
$educators_by_role = get_users(array('role__in' => array('educadora', 'administrator'), 'fields' => 'ID'));
$unique_educator_ids = array_unique(array_merge($educators_by_meta, $educators_by_role));
$total_educators = count($unique_educator_ids);

// Contagem de Autoridades (por cargo WordPress, independente de indicação)
$total_authorities = count(get_users(array('role' => 'autoridade', 'fields' => 'ID')));

// Indicações registradas na tabela (para o card separado)
$total_referrals = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}lms_referrals");

$total_courses = wp_count_posts('lms_course')->publish;
$total_lessons = wp_count_posts('lms_lesson')->publish;
?>

<div class="elite-admin-wrap bg-[#111] text-white min-h-screen p-8 rounded-xl shadow-2xl mr-4 mt-4 font-sans">
    
    <!-- Header -->
    <header class="flex justify-between items-center mb-12 border-b border-white/5 pb-8">
        <div>
            <h1 class="font-serif italic text-4xl mb-1 leading-tight" style="color: #D4AF37 !important;">Elite LMS: Hub de Comando</h1>
            <p class="text-xs uppercase tracking-[0.25em] font-medium" style="color: rgba(242, 212, 128, 0.6) !important;">Gestão de Autoridade & Performance Educacional</p>
        </div>
        <div class="flex items-center gap-4">
            <span class="px-4 py-2 bg-gold-500/10 border border-gold-500/20 text-gold-500 rounded-full text-[10px] font-bold uppercase tracking-widest animate-pulse">Servidor Ativo</span>
        </div>
    </header>

    <!-- Stat Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
        <div class="bg-white/5 p-6 rounded-2xl border border-white/10 hover:border-gold-500/30 transition-all group">
            <div class="text-gold-500 mb-2 opacity-50 group-hover:opacity-100 transition-all">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
            <span class="text-[10px] text-gold-400/70 font-bold uppercase tracking-widest">Membros da Comunidade</span>
            <div class="text-2xl font-bold mt-1 text-white">
                <?php echo $total_educators; ?> <span class="text-[10px] opacity-40 font-light uppercase">Educadoras</span> 
                <span class="mx-1 opacity-20">|</span> 
                <?php echo $total_authorities; ?> <span class="text-[10px] opacity-40 font-light uppercase">Autoridades</span>
            </div>
        </div>

        <div class="bg-white/5 p-6 rounded-2xl border border-white/10 hover:border-gold-500/30 transition-all group">
            <div class="text-gold-500 mb-2 opacity-50 group-hover:opacity-100 transition-all">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <span class="text-[10px] text-gold-400/70 font-bold uppercase tracking-widest">Indicações Totais</span>
            <div class="text-3xl font-bold mt-1 text-white"><?php echo $total_referrals; ?></div>
        </div>

        <div class="bg-white/5 p-6 rounded-2xl border border-white/10 hover:border-gold-500/30 transition-all group">
            <div class="text-gold-500 mb-2 opacity-50 group-hover:opacity-100 transition-all">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.168 0.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332 0.477 4.5 1.253v13C19.832 18 18.246 17.5 16.5 17.5c-1.747 0-3.168 0.477-4.5 1.253"></path></svg>
            </div>
            <span class="text-[10px] text-gold-400/70 font-bold uppercase tracking-widest">Total de Cursos</span>
            <div class="text-3xl font-bold mt-1 text-white"><?php echo $total_courses; ?></div>
        </div>

        <div class="bg-white/5 p-6 rounded-2xl border border-white/10 hover:border-gold-500/30 transition-all group">
            <div class="text-gold-500 mb-2 opacity-50 group-hover:opacity-100 transition-all">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            </div>
            <span class="text-[10px] text-gold-400/70 font-bold uppercase tracking-widest">Aulas Publicadas</span>
            <div class="text-3xl font-bold mt-1 text-white"><?php echo $total_lessons; ?></div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
        
        <!-- Action Cards -->
        <div class="space-y-6">
            <h3 class="text-xl font-bold font-serif italic mb-4" style="color: #D4AF37 !important;">Gestão Estratégica</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a href="<?php echo admin_url('admin.php?page=elite-content'); ?>" class="bg-white/5 p-6 rounded-2xl border border-white/10 hover:bg-gold-500/10 hover:border-gold-500/50 transition-all flex flex-col items-center text-center">
                    <span class="text-gold-400 mb-2">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </span>
                    <span class="text-sm font-semibold uppercase tracking-wider">Gerenciar Cursos</span>
                </a>

                <a href="<?php echo admin_url('admin.php?page=elite-calendar'); ?>" class="bg-white/5 p-6 rounded-2xl border border-white/10 hover:bg-gold-500/10 hover:border-gold-500/50 transition-all flex flex-col items-center text-center">
                    <span class="text-gold-400 mb-2">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </span>
                    <span class="text-sm font-semibold uppercase tracking-wider">Mentorias & Lives</span>
                </a>

                <a href="<?php echo admin_url('admin.php?page=elite-settings'); ?>" class="bg-white/5 p-6 rounded-2xl border border-white/10 hover:bg-gold-500/10 hover:border-gold-500/50 transition-all flex flex-col items-center text-center">
                    <span class="text-gold-400 mb-2">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    </span>
                    <span class="text-sm font-semibold uppercase tracking-wider">Marco Zero</span>
                </a>

                <a href="<?php echo site_url('/area-de-membros/'); ?>" target="_blank" class="bg-white/5 p-6 rounded-2xl border border-white/10 hover:bg-gold-500/10 hover:border-gold-500/50 transition-all flex flex-col items-center text-center">
                    <span class="text-gold-400 mb-2">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </span>
                    <span class="text-sm font-semibold uppercase tracking-wider">Ver Site (Live)</span>
                </a>
            </div>
        </div>

        <!-- System Intelligence / Recent Logs -->
        <div class="bg-white/5 p-8 rounded-3xl border border-white/10">
            <h3 class="text-xl font-bold font-serif italic mb-6" style="color: #D4AF37 !important;">Status do Motor MLM</h3>
            <ul class="space-y-4">
                <li class="flex items-center justify-between p-4 bg-black/40 rounded-xl border border-white/5 font-medium">
                    <span class="text-xs text-gray-200 uppercase tracking-widest">Database Sync</span>
                    <span class="text-xs font-bold text-green-500">Otimizado</span>
                </li>
                <li class="flex items-center justify-between p-4 bg-black/40 rounded-xl border border-white/5 font-medium">
                    <span class="text-xs text-gray-200 uppercase tracking-widest">Referral Tracker</span>
                    <span class="text-xs font-bold text-gold-400">Ativo (Cookie Enabled)</span>
                </li>
                <li class="flex items-center justify-between p-4 bg-black/40 rounded-xl border border-white/5 font-medium">
                    <span class="text-xs text-gray-200 uppercase tracking-widest">Gamificação</span>
                    <span class="text-xs font-bold text-gold-400">Regra dos 10 Ativa</span>
                </li>
            </ul>
            <div class="mt-10 p-6 bg-gold-500/10 border border-gold-500/20 rounded-2xl transition-all">
                <h4 class="font-bold text-sm mb-2 uppercase tracking-tight" style="color: #D4AF37 !important;">Lembrete de Admin</h4>
                <p class="text-xs text-gray-200 leading-relaxed font-medium">O reset do ciclo (Marco Zero) deve ser feito manualmente após o evento de gala anual para limpar os rankings sem afetar o histórico financeiro.</p>
            </div>
        </div>

    </div>
    
    <!-- Phase 25: Elite Ecosystem Directory -->
    <div class="mt-12 bg-white/5 p-10 rounded-3xl border border-white/10 shadow-2xl">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-6">
            <div>
                <h3 class="text-2xl font-serif italic text-gold-500 mb-1">Diretório do Ecossistema</h3>
                <p class="text-[10px] text-zinc-500 uppercase tracking-[0.2em] font-bold">Gestão Transparente de Membros & Liderança</p>
            </div>
            
            <div class="flex bg-black/40 p-1 rounded-xl border border-white/5" id="elite-tabs">
                <button onclick="switchAdminTab('educadores')" class="admin-tab-btn active px-6 py-2.5 rounded-lg text-[10px] font-bold uppercase tracking-widest transition-all bg-gold-500/10 border border-gold-500/50 text-gold-500">Educadores</button>
                <button onclick="switchAdminTab('autoridades')" class="admin-tab-btn px-6 py-2.5 rounded-lg text-[10px] font-bold uppercase tracking-widest transition-all text-zinc-500">Rede de Indicações</button>
                <button onclick="switchAdminTab('alunos')" class="admin-tab-btn px-6 py-2.5 rounded-lg text-[10px] font-bold uppercase tracking-widest transition-all text-zinc-500">Alunos</button>
            </div>
        </div>

        <!-- Manual Binding Tool -->
        <div class="mt-6 mb-8 bg-[#1a1a1a] p-6 rounded-2xl border border-gold-500/20">
            <h4 class="text-lg font-serif italic text-gold-500 mb-4">Vincular Membro Manualmente</h4>
            <form method="POST" class="flex flex-col md:flex-row gap-4 items-end">
                <input type="hidden" name="action" value="manual_link_authority">
                <div class="flex-1">
                    <label class="block text-[10px] uppercase text-zinc-400 mb-1">1. Qual Membro?</label>
                    <select name="authority_id" required class="w-full bg-black border border-white/10 rounded-lg text-white p-3 text-sm focus:border-gold-500 outline-none">
                        <option value="">-- Selecione o Membro --</option>
                        <?php 
                        $all_users = get_users();
                        foreach($all_users as $u) {
                            echo '<option value="'.$u->ID.'">'.esc_html($u->display_name).' ('.$u->user_email.')</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="flex-1">
                    <label class="block text-[10px] uppercase text-zinc-400 mb-1">2. Quem a indicou? (Educadora)</label>
                    <select name="educator_id" required class="w-full bg-black border border-white/10 rounded-lg text-white p-3 text-sm focus:border-gold-500 outline-none">
                        <option value="">-- Selecione a Educadora --</option>
                        <?php 
                        $educators_list = get_users(array(
                            'role__in' => array('educadora', 'administrator')
                        ));
                        foreach($educators_list as $edu) {
                            echo '<option value="'.$edu->ID.'">'.esc_html($edu->display_name).'</option>';
                        }
                        ?>
                    </select>
                </div>
                <button type="submit" class="px-8 py-3 bg-gold-500 text-black font-bold uppercase tracking-widest text-[10px] rounded-lg hover:bg-white transition-all shadow-lg" style="background-color: #D4AF37 !important; color: #000 !important;">Vincular e Sincronizar</button>
            </form>
        </div>

        <!-- TAB: Educadores -->
        <div id="admin-tab-educadores" class="admin-tab-content active transition-all">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-white/10">
                            <th class="py-4 px-4 text-[9px] uppercase tracking-widest text-zinc-500">Educador</th>
                            <th class="py-4 px-4 text-[9px] uppercase tracking-widest text-zinc-500 text-center">Origem</th>
                            <th class="py-4 px-4 text-[9px] uppercase tracking-widest text-zinc-500 text-center">Rank</th>
                            <th class="py-4 px-4 text-[9px] uppercase tracking-widest text-zinc-500 text-center">Indicações</th>
                            <th class="py-4 px-4 text-[9px] uppercase tracking-widest text-zinc-500 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php 
                        $educators = get_users(array(
                            'role__in' => array('educadora', 'administrator')
                        ));
                        foreach ($educators as $edu): 
                            $rank = get_user_meta($edu->ID, '_lms_rank_name', true) ?: 'Bronze';
                            
                            // Source / Referral Logic
                            $referrer_id = $wpdb->get_var($wpdb->prepare("SELECT educator_id FROM {$wpdb->prefix}lms_referrals WHERE authority_id = %d", $edu->ID));
                            $referrer = $referrer_id ? get_userdata($referrer_id) : null;
                            
                            // Count real referrals from table
                            $ref_count_raw = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}lms_referrals WHERE educator_id = %d", $edu->ID));
                        ?>
                        <tr class="hover:bg-white/[0.02] transition-all group">
                            <td class="py-4 px-4 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full border border-gold-500/20 p-0.5"><?php echo get_avatar($edu->ID, 40, '', '', array('class' =>'rounded-full')); ?></div>
                                <div>
                                    <div class="text-sm font-semibold text-white"><?php echo esc_html($edu->display_name); ?></div>
                                    <div class="text-[9px] text-zinc-600"><?php echo esc_html($edu->user_email); ?></div>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <?php if ($referrer): ?>
                                    <div class="flex flex-col items-center justify-center group/ref">
                                        <span class="text-[7px] font-bold text-gold-500/60 uppercase tracking-[0.1em]">Indicado por</span>
                                        <span class="text-[10px] text-white font-medium border-b border-gold-500/20 group-hover/ref:border-gold-500 transition-all cursor-default" title="<?php echo esc_attr($referrer->user_email); ?>"><?php echo esc_html($referrer->display_name); ?></span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-[8px] text-zinc-600 font-bold uppercase tracking-widest opacity-40 italic">Orgânico</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <span class="px-3 py-1 bg-gold-500/10 border border-gold-500/20 text-gold-500 text-[8px] font-bold rounded-full uppercase"><?php echo esc_html($rank); ?></span>
                            </td>
                            <td class="py-4 px-4 text-center font-bold text-lg"><?php echo $ref_count_raw; ?></td>
                            <td class="py-4 px-4 text-right">
                                <a href="<?php echo get_edit_user_link($edu->ID); ?>" class="text-[9px] font-bold uppercase tracking-widest text-gold-500 hover:text-white transition-all">Ver Perfil</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB: Autoridades -->
        <div id="admin-tab-autoridades" class="admin-tab-content hidden h-0 opacity-0 overflow-hidden transition-all">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-white/10">
                            <th class="py-4 px-4 text-[9px] uppercase tracking-widest text-zinc-500">Líder Emergente</th>
                            <th class="py-4 px-4 text-[9px] uppercase tracking-widest text-zinc-500">Educador de Origem</th>
                            <th class="py-4 px-4 text-[9px] uppercase tracking-widest text-zinc-500">Conectado em</th>
                            <th class="py-4 px-4 text-[9px] uppercase tracking-widest text-zinc-500 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php 
                        $authorities_log = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}lms_referrals ORDER BY created_at DESC LIMIT 50");
                        foreach ($authorities_log as $auth): 
                            $auth_user = get_userdata($auth->authority_id);
                            $edu_user = get_userdata($auth->educator_id);
                            if(!$auth_user) continue;
                        ?>
                        <tr class="hover:bg-white/[0.02] transition-all">
                            <td class="py-4 px-4">
                                <div class="text-sm font-semibold text-white"><?php echo esc_html($auth_user->display_name); ?></div>
                                <div class="text-[9px] text-zinc-600"><?php echo esc_html($auth_user->user_email); ?></div>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full border border-gold-500/20 overflow-hidden"><?php echo get_avatar($auth->educator_id, 24); ?></div>
                                    <span class="text-xs text-zinc-400 font-medium"><?php echo $edu_user ? esc_html($edu_user->display_name) : '---'; ?></span>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-[10px] text-zinc-600 font-mono italic">
                                <?php echo date('d/m/Y H:i', strtotime($auth->created_at)); ?>
                            </td>
                            <td class="py-4 px-4 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <button onclick="editReferralBind(<?php echo $auth->authority_id; ?>, <?php echo $auth->educator_id; ?>)" class="text-gold-500 hover:text-white transition-all transform hover:scale-110" title="Trocar Indicador">
                                        <span class="dashicons dashicons-edit text-sm"></span>
                                    </button>
                                    <form method="POST" onsubmit="return confirm('Tem certeza que deseja desvincular este membro da rede?')" class="inline">
                                        <input type="hidden" name="action" value="delete_referral">
                                        <input type="hidden" name="ref_id" value="<?php echo $auth->id; ?>">
                                        <button type="submit" class="text-zinc-600 hover:text-red-500 transition-all transform hover:scale-110" title="Excluir Vínculo">
                                            <span class="dashicons dashicons-trash text-sm"></span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB: Alunos -->
        <div id="admin-tab-alunos" class="admin-tab-content hidden h-0 opacity-0 overflow-hidden transition-all">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <?php 
                $students = get_users(array('number' => 40, 'orderby' => 'registered', 'order' => 'DESC'));
                foreach ($students as $stu): 
                    if (Expressive_Referral::is_educator($stu->ID)) continue;
                    $access_checker = new Expressive_Access();
                    $is_active = $access_checker->has_active_subscription($stu->ID);
                ?>
                <div class="bg-black/20 p-4 rounded-2xl border border-white/5 hover:border-gold-500/30 transition-all relative">
                    <div class="absolute top-4 right-4 w-2 h-2 rounded-full <?php echo $is_active ? 'bg-green-500 shadow-[0_0_8px_#22c55e]' : 'bg-red-500 shadow-[0_0_8px_#ef4444]'; ?>" title="<?php echo $is_active ? 'Acesso Ativo' : 'Acesso Suspenso'; ?>"></div>
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-full overflow-hidden border border-white/10"><?php echo get_avatar($stu->ID, 40); ?></div>
                        <div class="truncate">
                            <div class="text-xs font-bold text-white truncate"><?php echo esc_html($stu->display_name); ?></div>
                            <div class="text-[9px] text-zinc-600">Membro desde <?php echo date('M/Y', strtotime($stu->user_registered)); ?></div>
                        </div>
                    </div>
                    <a href="<?php echo get_edit_user_link($stu->ID); ?>" class="w-full block py-2 bg-white/5 text-center rounded-lg text-[8px] font-bold uppercase tracking-widest hover:bg-gold-500 hover:text-black transition-all">Gerenciar Acesso</a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

</div>

<script>
    function switchAdminTab(tabId) {
        document.querySelectorAll('.admin-tab-btn').forEach(btn => {
            btn.classList.remove('active', 'bg-gold-500/10', 'border-gold-500/50', 'text-gold-500');
            btn.classList.add('text-zinc-500');
        });
        document.querySelectorAll('.admin-tab-content').forEach(content => {
            content.classList.add('hidden', 'h-0', 'opacity-0');
        });
        
        const activeBtn = event.currentTarget || document.querySelector(`button[onclick="switchAdminTab('${tabId}')"]`);
        if (activeBtn) {
            activeBtn.classList.add('active', 'bg-gold-500/10', 'border-gold-500/50', 'text-gold-500');
            activeBtn.classList.remove('text-zinc-500');
        }
        
        const activeTab = document.getElementById('admin-tab-' + tabId);
        activeTab.classList.remove('hidden', 'h-0', 'opacity-0');
    }

    function editReferralBind(referredId, referrerId) {
        const form = document.querySelector('form[method="POST"]');
        const selectMember = form.querySelector('select[name="authority_id"]');
        const selectEducator = form.querySelector('select[name="educator_id"]');
        
        if (selectMember && selectEducator) {
            selectMember.value = referredId;
            selectEducator.value = referrerId;
            
            window.scrollTo({
                top: (form.getBoundingClientRect().top + window.pageYOffset) - 100,
                behavior: 'smooth'
            });
            
            form.classList.add('outline', 'outline-gold-500/50', 'outline-offset-8', 'ring-4', 'ring-gold-500/20');
            setTimeout(() => {
                form.classList.remove('outline', 'outline-gold-500/50', 'outline-offset-8', 'ring-4', 'ring-gold-500/20');
            }, 3000);
        }
    }
</script>

<style>
    /* Scoped Fixes for WP Admin Interference */
    #wpcontent { background: #000 !important; }
    .elite-admin-wrap { font-family: 'Outfit', sans-serif !important; }
    #wpbody-content form select { background-color: #111 !important; color: #fff !important; }
    .elite-admin-wrap h1, .elite-admin-wrap h2, .elite-admin-wrap h3, .elite-admin-wrap h4 { 
        font-family: 'Playfair Display', serif !important; 
        color: #D4AF37 !important; 
    }
    .admin-tab-btn.active { box-shadow: 0 4px 12px rgba(212, 175, 55, 0.2); }
    .admin-tab-content { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
</style>
