<?php
global $wpdb;
$table_access = $wpdb->prefix . 'elite_subscription_access';
$table_events = $wpdb->prefix . 'elite_subscription_events';

// Logic for saving API settings
if ( isset( $_POST['lms_save_api_settings'] ) && check_admin_referer( 'lms_save_api_nonce' ) ) {
    if ( isset( $_POST['api_url'] ) ) {
        update_option( 'lms_external_api_url', esc_url_raw( $_POST['api_url'] ) );
    }
    if ( isset( $_POST['api_token'] ) ) {
        update_option( 'lms_external_api_token', sanitize_text_field( $_POST['api_token'] ) );
    }

    if ( isset( $_POST['lms_grace_period_days'] ) ) {
        update_option( 'lms_grace_period_days', intval( $_POST['lms_grace_period_days'] ) );
    }
    if ( isset( $_POST['lms_hard_fallback_days'] ) ) {
        update_option( 'lms_hard_fallback_days', intval( $_POST['lms_hard_fallback_days'] ) );
    }

    // Clear individual overrides to ensure unified endpoint is used
    delete_option( 'lms_external_api_url_status' );
    delete_option( 'lms_external_api_url_sync' );
    delete_option( 'lms_external_api_url_cancel' );

    echo '<div class="updated notice is-dismissible" style="background: rgba(212, 175, 55, 0.1); border-left: 4px solid #D4AF37; color: #D4AF37; padding: 1px 15px; margin: 10px 20px 0 0; border-radius: 8px; font-weight: bold;"><p>Configurações de acesso salvas com sucesso.</p></div>';
}

// Logic for manual actions (from subscriptions dashboard)
if ( isset( $_GET['action_type'] ) && check_admin_referer( 'lms_subscription_action' ) ) {
    $email = isset( $_GET['email'] ) ? sanitize_email( wp_unslash( $_GET['email'] ) ) : '';

    if ( ! $email ) {
        echo '<div class="error notice is-dismissible"><p>E-mail inválido para a ação solicitada.</p></div>';
    } elseif ( $_GET['action_type'] === 'sync' ) {
        $user = get_user_by( 'email', $email );
        if ( $user ) {
            Expressive_External_API::check_user_status( $user->ID );
            echo '<div class="updated notice is-dismissible" style="background: rgba(34, 197, 94, 0.1); border-left: 4px solid #22c55e; color: #22c55e; padding: 10px; margin: 10px 20px 10px 0; border-radius: 8px;"><p>Sincronização concluída para ' . esc_html($email) . '</p></div>';
        }
    } elseif ( $_GET['action_type'] === 'expire' ) {
        $user = get_user_by( 'email', $email );
        Expressive_External_API::update_local_access( $email, array(
            'status'            => 'cancelled',
            'access_expires_at' => current_time( 'mysql' ),
            'cancel_reason'     => 'Acesso revogado manualmente no Elite API Manager.',
        ) );
        Expressive_External_API::record_access_event(
            $email,
            'admin_revoke_access',
            'unknown',
            'cancelled',
            'Acesso revogado manualmente no Elite API Manager.',
            'WP_ADMIN',
            'SUCCESS'
        );
        if ( $user ) {
            update_user_meta( $user->ID, '_lms_subscription_status', 'suspended' );
        }
        echo '<div class="updated notice is-dismissible" style="background: rgba(239, 68, 68, 0.1); border-left: 4px solid #ef4444; color: #ef4444; padding: 10px; margin: 10px 20px 10px 0; border-radius: 8px;"><p>Acesso encerrado para ' . esc_html($email) . '</p></div>';
    }
}

$api_url = get_option( 'lms_external_api_url', '' );
$api_token = get_option( 'lms_external_api_token', '' );
$last_api_log = get_option( 'lms_api_last_log', array() );

// Fetch Stats
$total_active = $wpdb->get_var( "SELECT COUNT(*) FROM $table_access WHERE status = 'active'" );
$total_grace  = $wpdb->get_var( "SELECT COUNT(*) FROM $table_access WHERE status = 'grace_period'" );

// Fetch Subscriptions
$subscriptions = $wpdb->get_results( "SELECT * FROM $table_access ORDER BY last_sync_at DESC LIMIT 100" );

// Fetch Recent Events
$events = $wpdb->get_results( "SELECT * FROM $table_events ORDER BY created_at DESC LIMIT 15" );
?>

<div class="elite-admin-wrap bg-[#000] text-white min-h-screen p-4 sm:p-8 rounded-xl shadow-2xl mr-4 mt-4 font-sans max-w-full overflow-x-hidden">

    <!-- HEADER -->
    <header class="mb-10 text-center md:text-left">
        <div class="flex flex-col md:flex-row justify-between items-center gap-6 border-b border-white/5 pb-8">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gold-500/10 rounded-2xl flex items-center justify-center text-gold-500 border border-gold-500/20 shadow-lg shadow-gold-500/5">
                    <span class="dashicons dashicons-rest-api" style="font-size: 30px; width: 30px; height: 30px; color: #D4AF37;"></span>
                </div>
                <div>
                    <h1 class="font-serif italic text-4xl mb-1 leading-tight text-white tracking-tight">Elite <span style="color: #D4AF37;">API</span> Manager</h1>
                    <p class="text-[10px] uppercase tracking-[0.3em] text-zinc-500 font-bold">Gestão Unificada de Acesso e Gateway</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-4">
                <div class="flex gap-2">
                    <div class="bg-white/5 border border-white/10 px-6 py-2 rounded-2xl">
	                        <span class="text-[7px] uppercase font-black tracking-widest text-zinc-500 block">Gateway Ativos</span>
                        <span class="text-sm font-bold text-green-500"><?php echo $total_active; ?></span>
                    </div>
                    <div class="bg-white/5 border border-white/10 px-6 py-2 rounded-2xl">
	                        <span class="text-[7px] uppercase font-black tracking-widest text-zinc-500 block">Carência</span>
                        <span class="text-sm font-bold text-gold-500"><?php echo $total_grace; ?></span>
                    </div>
                </div>
                <button onclick="syncGlobalMembers(this)" class="px-8 py-4 bg-gold-500 hover:bg-white text-black rounded-2xl text-[11px] font-black uppercase tracking-[0.1em] transition-all shadow-xl shadow-gold-600/20 flex items-center gap-3 active:scale-95" style="background-color: #D4AF37 !important; color: #000 !important;">
                    <span class="dashicons dashicons-update" style="font-size: 16px; width: 16px; height: 16px;"></span>
                    Sincronização Global
                </button>
            </div>
        </div>
    </header>

    <!-- CONFIG & MONITOR -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-8 mb-12">
        <!-- Configuration Card -->
        <div class="xl:col-span-8 glass p-10 rounded-[40px] border border-white/10 relative overflow-hidden group shadow-2xl">
            <h3 class="text-xl font-bold font-serif italic mb-8 text-white flex items-center gap-3">
                <span class="w-1.5 h-6 bg-gold-500 rounded-full shadow-[0_0_15px_#D4AF37]" style="background-color: #D4AF37;"></span>
                Configuração Estrutural Unificada
            </h3>

            <form method="post" action="" class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
                <?php wp_nonce_field( 'lms_save_api_nonce' ); ?>
                <div class="md:col-span-6 space-y-3">
                    <label class="text-[9px] font-black uppercase tracking-[0.3em] text-zinc-600 ml-1">Endpoint Gateway (Supabase/Edge)</label>
                    <input name="api_url" type="url" value="<?php echo esc_attr( $api_url ); ?>" placeholder="https://..." class="w-full bg-black/80 border border-white/5 rounded-2xl px-6 py-4 text-sm focus:border-gold-500/50 outline-none transition-all text-white border-l-2 border-l-gold-500/20">
                </div>
                <div class="md:col-span-4 space-y-3">
                    <label class="text-[9px] font-black uppercase tracking-[0.3em] text-zinc-600 ml-1">Bearer Token / Chave Secreta</label>
                    <input name="api_token" type="password" value="<?php echo esc_attr( $api_token ); ?>" placeholder="••••••••••••" class="w-full bg-black/80 border border-white/5 rounded-2xl px-6 py-4 text-sm focus:border-gold-500/50 outline-none transition-all text-white">
                </div>
                <div class="md:col-span-2">
                    <button type="submit" name="lms_save_api_settings" class="w-full py-4 bg-gold-500 hover:bg-white text-black rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] transition-all active:scale-95 shadow-lg shadow-gold-500/20" style="background-color: #D4AF37 !important; color: #000 !important;">
                        Salvar
                    </button>
                </div>
            </form>
        </div>

        <!-- Monitor Card -->
        <div class="xl:col-span-4 glass p-8 rounded-[40px] border border-white/10 relative overflow-hidden bg-white/[0.01]">
            <h3 class="text-lg font-bold font-serif italic text-white flex items-center gap-3 mb-6">
                <span class="w-1.5 h-6 bg-zinc-800 rounded-full"></span>
                Tráfego
            </h3>

            <?php if ( ! empty( $last_api_log ) ) : ?>
                <div class="flex gap-4 mb-4">
                    <div class="flex-1 bg-black/60 p-3 rounded-2xl border border-white/5">
                        <span class="text-[7px] text-zinc-700 uppercase font-black block mb-1 tracking-widest">HTTP</span>
                        <span class="text-xs font-mono <?php echo $last_api_log['code'] < 400 ? 'text-green-500' : 'text-red-500'; ?> font-bold"><?php echo $last_api_log['code']; ?></span>
                    </div>
                    <div class="flex-1 bg-black/60 p-3 rounded-2xl border border-white/5">
                        <span class="text-[7px] text-zinc-700 uppercase font-black block mb-1 tracking-widest">Hora</span>
                        <span class="text-[10px] font-mono text-zinc-500"><?php echo date('H:i:s', strtotime($last_api_log['timestamp'])); ?></span>
                    </div>
                </div>
                <div class="bg-black/80 p-4 rounded-2xl border border-white/5">
                    <div class="max-h-12 overflow-y-auto custom-scrollbar">
                        <pre class="text-[9px] font-mono text-gold-500/50 leading-tight"><?php echo esc_html( $last_api_log['response'] ); ?></pre>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- MAIN SUBSCRIPTION MANAGEMENT -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
        <!-- Members List -->
        <div class="xl:col-span-9 glass rounded-[48px] border border-white/5 overflow-hidden shadow-2xl relative">
            <div class="p-8 border-b border-white/5 flex flex-col md:flex-row justify-between items-center bg-white/[0.01] gap-4">
                <div>
                    <h3 class="text-2xl font-bold font-serif italic text-white flex items-center gap-3">Gestão de Membros & Acesso</h3>
                    <p class="text-[10px] text-zinc-600 mt-1 uppercase tracking-[0.2em] font-black">Sincronização Local vs Gateway Asaas</p>
                </div>
                <div class="flex items-center gap-2">
                     <span class="px-4 py-2 bg-white/5 rounded-xl text-[10px] font-black text-white border border-white/10 uppercase tracking-widest">
                        <?php echo count($subscriptions); ?> Registros
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-[10px] uppercase tracking-[0.25em] text-zinc-700 bg-white/[0.01]">
                            <th class="px-8 py-5">Membro</th>
                            <th class="px-8 py-5 text-center">Status Local</th>
                            <th class="px-8 py-5 text-center">Expira em</th>
                            <th class="px-8 py-5 text-center">Sincronizado</th>
                            <th class="px-8 py-5 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php foreach ( $subscriptions as $sub ) :
                            $target_user = get_user_by('email', $sub->email);
                            $access_snapshot = $target_user ? Expressive_Access::get_user_access_snapshot( $target_user->ID ) : null;
                            $manual_status = $access_snapshot ? $access_snapshot['manual_status'] : 'none';
                            $is_manual = ($manual_status !== 'none' && $manual_status !== '');
                        ?>
                        <tr class="hover:bg-white/[0.03] transition-all group <?php echo $is_manual ? 'bg-gold-500/[0.02]' : ''; ?>">
                            <td class="px-8 py-5">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-white"><?php echo esc_html($sub->email); ?></span>
                                    <span class="text-[9px] text-zinc-600 uppercase font-bold tracking-tighter">
                                        <?php
	                                        if ($manual_status === 'unblocked') echo '<span class="text-gold-500">Acesso Vitalício</span>';
	                                        elseif ($manual_status === 'blocked') echo '<span class="text-red-500">Bloqueio Manual</span>';
	                                        else echo esc_html( $access_snapshot ? $access_snapshot['plan_name'] : ( $sub->plan_name ?: 'Elite Member' ) );
	                                        ?>
                                    </span>
                                </div>
                            </td>
                            <td class="px-8 py-5 text-center">
                                <?php
	                                    $display_status = $access_snapshot ? $access_snapshot['status_label'] : $sub->status;
	                                    $status_class = 'bg-zinc-800 text-zinc-400';

	                                    if ($access_snapshot && $access_snapshot['effective_status'] === 'lifetime') {
	                                        $status_class = 'bg-gold-500/20 text-gold-500 border border-gold-500/30 shadow-[0_0_15px_rgba(212,175,55,0.1)]';
	                                    } elseif ($access_snapshot && $access_snapshot['effective_status'] === 'blocked') {
	                                        $status_class = 'bg-red-600/20 text-red-500 border border-red-600/30';
	                                    } elseif ($access_snapshot && $access_snapshot['has_access']) {
	                                        if ($access_snapshot['effective_status'] === 'grace_period' || $access_snapshot['effective_status'] === 'cancelled_with_access') $status_class = 'bg-gold-500/10 text-gold-500 border border-gold-500/20 shadow-[0_0_15px_rgba(212,175,55,0.1)]';
	                                        else $status_class = 'bg-green-500/10 text-green-500 border border-green-500/20 shadow-[0_0_15px_rgba(34,197,94,0.1)]';
	                                    } else {
	                                        if($sub->status === 'active') $status_class = 'bg-green-500/10 text-green-500 border border-green-500/20 shadow-[0_0_15px_rgba(34,197,94,0.1)]';
	                                        if($sub->status === 'grace_period') $status_class = 'bg-gold-500/10 text-gold-500 border border-gold-500/20 shadow-[0_0_15px_rgba(212,175,55,0.1)]';
	                                        if($sub->status === 'cancelled') $status_class = 'bg-red-500/10 text-red-500 border border-red-500/20';
	                                        if($sub->status === 'inactive') $status_class = 'bg-red-500/10 text-red-500 border border-red-500/20';
	                                    }
                                ?>
                                <span class="px-3 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest <?php echo $status_class; ?>">
	                                    <?php echo esc_html( str_replace('_', ' ', $display_status) ); ?>
                                </span>
                            </td>
                            <td class="px-8 py-5 text-center">
                                <div class="flex flex-col">
                                    <span class="text-[11px] text-white">
                                        <?php
	                                        if ($access_snapshot && $access_snapshot['effective_status'] === 'lifetime') echo 'NUNCA';
	                                        elseif ($access_snapshot && $access_snapshot['effective_status'] === 'blocked') echo 'IMEDIATO';
	                                        else echo ($access_snapshot && $access_snapshot['access_expires_at'] ? date('d/m/Y', strtotime($access_snapshot['access_expires_at'])) : ($sub->access_expires_at ? date('d/m/Y', strtotime($sub->access_expires_at)) : '—'));
	                                        ?>
	                                    </span>
	                                    <?php if($access_snapshot && $access_snapshot['effective_status'] === 'grace_period' && $access_snapshot['grace_ends_at'] && !$is_manual) : ?>
	                                        <span class="text-[8px] text-gold-500 font-bold uppercase">Grace até <?php echo date('d/m/Y', strtotime($access_snapshot['grace_ends_at'])); ?></span>
	                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-8 py-5 text-center">
                                <span class="text-[9px] font-mono text-zinc-600">
	                                    <?php echo $is_manual ? 'Manual' : ( $sub->last_sync_at ? date('d/m H:i', strtotime($sub->last_sync_at)) : '—' ); ?>
                                </span>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <div class="flex justify-end gap-2">
	                                    <a href="<?php echo esc_url( wp_nonce_url( admin_url('admin.php?page=elite-api&action_type=sync&email=' . rawurlencode($sub->email)), 'lms_subscription_action' ) ); ?>" class="w-9 h-9 rounded-xl bg-white/5 flex items-center justify-center text-zinc-500 hover:text-gold-500 hover:bg-gold-500/10 transition-all shadow-inner" title="Sincronizar Agora">
                                        <span class="dashicons dashicons-update" style="font-size: 15px;"></span>
                                    </a>
                                    <?php if($sub->status !== 'cancelled') : ?>
	                                    <a href="<?php echo esc_url( wp_nonce_url( admin_url('admin.php?page=elite-api&action_type=expire&email=' . rawurlencode($sub->email)), 'lms_subscription_action' ) ); ?>" class="w-9 h-9 rounded-xl bg-white/5 flex items-center justify-center text-zinc-500 hover:text-red-500 hover:bg-red-500/10 transition-all shadow-inner" title="Revogar Acesso">
                                        <span class="dashicons dashicons-no" style="font-size: 15px;"></span>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right: Global User Search & Control -->
        <div class="xl:col-span-3 space-y-6">
            <div class="glass p-8 rounded-[40px] border border-white/5 relative overflow-hidden bg-white/[0.01]">
                <h3 class="text-xl font-bold font-serif italic text-white flex items-center gap-3 mb-6">
                    <span class="w-1.5 h-6 bg-gold-500 rounded-full" style="background-color: #D4AF37;"></span>
                    Controle Total
                </h3>

                <p class="text-[10px] text-zinc-500 uppercase tracking-widest mb-4 leading-relaxed">Gerencie acesso vitalício ou bloqueios para qualquer usuário do site.</p>

                <div class="relative mb-6">
                    <input type="text" id="user-global-search" onkeyup="filterGlobalUsers()" placeholder="Buscar nome ou e-mail..." class="w-full bg-black border border-white/10 rounded-2xl px-5 py-4 text-xs focus:border-gold-500 outline-none transition-all text-white pr-12">
                    <span class="dashicons dashicons-search absolute right-4 top-4 text-zinc-600"></span>
                </div>

                <div id="global-user-list" class="space-y-3 max-h-[600px] overflow-y-auto custom-scrollbar pr-2">
                    <?php
	                    $all_users = get_users( array( 'number' => 100, 'orderby' => 'registered', 'order' => 'DESC' ) );
	                    foreach ( $all_users as $u ) :
	                        $u_access = Expressive_Access::get_user_access_snapshot( $u->ID );
	                        $m_status = $u_access['manual_status'];
	                    ?>
                    <div class="user-row p-4 bg-white/[0.02] border border-white/5 rounded-2xl group hover:bg-white/[0.05] transition-all" data-search="<?php echo esc_attr( strtolower($u->display_name . ' ' . $u->user_email) ); ?>">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg overflow-hidden border border-white/10">
                                    <?php echo get_avatar($u->ID, 32); ?>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-[10px] font-bold text-white truncate w-24"><?php echo esc_html($u->display_name); ?></span>
                                    <span class="text-[8px] text-zinc-600 truncate w-24"><?php echo esc_html($u->user_email); ?></span>
                                </div>
                            </div>
                            <div id="status-badge-<?php echo $u->ID; ?>">
	                                <?php if($m_status === 'blocked'): ?>
	                                    <span class="text-[7px] font-black uppercase bg-red-500/20 text-red-500 px-2 py-1 rounded-md border border-red-500/20">Bloqueado</span>
	                                <?php elseif($m_status === 'unblocked'): ?>
	                                    <span class="text-[7px] font-black uppercase bg-green-500/20 text-green-500 px-2 py-1 rounded-md border border-green-500/20">Vitalício</span>
	                                <?php elseif($u_access['has_access']): ?>
	                                    <span class="text-[7px] font-black uppercase bg-gold-500/20 text-gold-500 px-2 py-1 rounded-md border border-gold-500/20"><?php echo esc_html($u_access['status_label']); ?></span>
	                                <?php else: ?>
	                                    <span class="text-[7px] font-black uppercase bg-zinc-800 text-zinc-500 px-2 py-1 rounded-md">Sem Acesso</span>
	                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-1">
                            <button onclick="updateManualStatus(<?php echo $u->ID; ?>, 'none', this)" class="py-2 rounded-lg text-[7px] font-bold uppercase tracking-tighter transition-all <?php echo $m_status === 'none' ? 'bg-gold-500 text-black shadow-lg shadow-gold-500/10' : 'bg-white/5 text-zinc-500 hover:bg-white/10'; ?>">Auto</button>
                            <button onclick="updateManualStatus(<?php echo $u->ID; ?>, 'unblocked', this)" class="py-2 rounded-lg text-[7px] font-bold uppercase tracking-tighter transition-all <?php echo $m_status === 'unblocked' ? 'bg-green-600 text-white shadow-lg shadow-green-500/20' : 'bg-white/5 text-zinc-500 hover:bg-green-500/10 hover:text-green-500'; ?>">Vitalício</button>
                            <button onclick="updateManualStatus(<?php echo $u->ID; ?>, 'blocked', this)" class="py-2 rounded-lg text-[7px] font-bold uppercase tracking-tighter transition-all <?php echo $m_status === 'blocked' ? 'bg-red-600 text-white shadow-lg shadow-red-500/20' : 'bg-white/5 text-zinc-500 hover:bg-red-500/10 hover:text-red-500'; ?>">Bloquear</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Global Security Settings -->
            <div class="glass p-8 rounded-[40px] border border-white/5 relative overflow-hidden bg-white/[0.01]">
                <h3 class="text-xl font-bold font-serif italic text-white flex items-center gap-3 mb-6">
                    <span class="w-1.5 h-6 bg-gold-500 rounded-full" style="background-color: #D4AF37;"></span>
                    Segurança de Acesso
                </h3>

                <form method="post" action="" class="space-y-6">
                    <?php wp_nonce_field( 'lms_save_api_nonce' ); ?>

                    <div class="space-y-2">
                        <label for="lms_grace_period_days" class="text-[10px] font-bold uppercase tracking-widest text-gold-400">Dias de Carência (Grace Period)</label>
                        <?php $grace_days = get_option( 'lms_grace_period_days', 7 ); ?>
                        <input name="lms_grace_period_days" type="number" id="lms_grace_period_days" value="<?php echo esc_attr( $grace_days ); ?>" min="0" class="w-full bg-black border border-white/10 rounded-2xl px-5 py-4 text-xs focus:border-gold-500 outline-none transition-all text-white">
                        <p class="text-[8px] text-zinc-500 italic mt-1">Tolerância para o status <strong>grace_period</strong>.</p>
                    </div>

                    <div class="space-y-2">
                        <label for="lms_hard_fallback_days" class="text-[10px] font-bold uppercase tracking-widest text-gold-400">Limite Hard (Segurança)</label>
                        <?php $fallback_days = get_option( 'lms_hard_fallback_days', 30 ); ?>
                        <input name="lms_hard_fallback_days" type="number" id="lms_hard_fallback_days" value="<?php echo esc_attr( $fallback_days ); ?>" min="0" class="w-full bg-black border border-white/10 rounded-2xl px-5 py-4 text-xs focus:border-gold-500 outline-none transition-all text-white">
                        <p class="text-[8px] text-zinc-500 italic mt-1">Tolerância para status <strong>active</strong> expirado.</p>
                    </div>

                    <button type="submit" name="lms_save_api_settings" class="w-full py-4 bg-gold-500 hover:bg-white text-black font-black uppercase tracking-widest text-[9px] rounded-2xl transition-all shadow-xl shadow-gold-500/10">Salvar Segurança</button>
                </form>
            </div>
        </div>
    </div>

</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;700;900&family=Playfair+Display:ital,wght@1,700;1,900&display=swap');

    #wpcontent { background: #000 !important; padding-left: 0 !important; }
    #wpbody-content { padding-bottom: 0 !important; }
    .elite-admin-wrap { font-family: 'Outfit', sans-serif !important; }
    .elite-admin-wrap h1, .elite-admin-wrap h3 { font-family: 'Playfair Display', serif !important; }
    .glass { background: rgba(15, 15, 15, 0.4); backdrop-filter: blur(25px); }

    .custom-scrollbar::-webkit-scrollbar { width: 3px; height: 3px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #333; border-radius: 20px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #D4AF37; }
</style>

<script>
    const ajax_url = '<?php echo admin_url("admin-ajax.php"); ?>';

    function syncGlobalMembers(btn) {
        if (btn.disabled) return;
        btn.innerHTML = '<span class="dashicons dashicons-update animate-spin"></span> Processando...';
        btn.disabled = true;

	        const formData = new FormData();
	        formData.append('action', 'lms_sync_all_api_status');
	        formData.append('nonce', '<?php echo wp_create_nonce("lms_api_mgmt_nonce"); ?>');

        fetch(ajax_url, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.data || 'Erro fatal na comunicação.');
                btn.innerHTML = 'Tentar Novamente';
                btn.disabled = false;
            }
        });
    }

    function filterGlobalUsers() {
        const query = document.getElementById('user-global-search').value.toLowerCase();
        document.querySelectorAll('.user-row').forEach(row => {
            const text = row.getAttribute('data-search');
            row.style.display = text.includes(query) ? 'block' : 'none';
        });
    }

    function updateManualStatus(userId, status, btn) {
        const originalText = btn.innerHTML;
        btn.innerHTML = '...';
        btn.disabled = true;

        const formData = new FormData();
        formData.append('action', 'lms_update_user_manual_status');
        formData.append('nonce', '<?php echo wp_create_nonce("lms_api_mgmt_nonce"); ?>');
        formData.append('user_id', userId);
        formData.append('status', status);

        fetch(ajax_url, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Flash success state then reload or update UI
                btn.style.backgroundColor = '#D4AF37';
                btn.style.color = '#000';
                setTimeout(() => window.location.reload(), 500);
            } else {
                alert(data.data || 'Erro ao atualizar.');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });
    }
</script>
