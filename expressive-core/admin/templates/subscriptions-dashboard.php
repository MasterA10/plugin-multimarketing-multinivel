<?php
/**
 * Template Name: Subscriptions Dashboard
 * 
 * Management of local member access states and API sync audit.
 */
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Acesso negado.' );
}

global $wpdb;
$table_access = $wpdb->prefix . 'elite_subscription_access';
$table_events = $wpdb->prefix . 'elite_subscription_events';

// Logic for manual actions
if ( isset( $_GET['action_type'] ) && check_admin_referer( 'lms_subscription_action' ) ) {
    $email = sanitize_email( $_GET['email'] );
    
    if ( $_GET['action_type'] === 'sync' ) {
        $user = get_user_by( 'email', $email );
        if ( $user ) {
            Expressive_External_API::check_user_status( $user->ID );
            echo '<div class="updated"><p>Sincronização manual concluída para ' . esc_html($email) . '</p></div>';
        }
    } elseif ( $_GET['action_type'] === 'expire' ) {
        $wpdb->update( $table_access, array( 'status' => 'cancelled', 'last_sync_at' => current_time('mysql') ), array( 'email' => $email ) );
        echo '<div class="updated"><p>Acesso encerrado manualmente para ' . esc_html($email) . '</p></div>';
    }
}

// Fetch Stats
$total_active = $wpdb->get_var( "SELECT COUNT(*) FROM $table_access WHERE status = 'active'" );
$total_grace  = $wpdb->get_var( "SELECT COUNT(*) FROM $table_access WHERE status = 'grace_period'" );

// Fetch Subscriptions
$subscriptions = $wpdb->get_results( "SELECT * FROM $table_access ORDER BY last_sync_at DESC LIMIT 100" );

// Fetch Recent Events
$events = $wpdb->get_results( "SELECT * FROM $table_events ORDER BY created_at DESC LIMIT 20" );
?>

<div class="elite-admin-wrap bg-[#111] text-white min-h-screen p-8 rounded-xl shadow-2xl mr-4 mt-4 font-sans">
    
    <!-- Header -->
    <header class="flex justify-between items-center mb-10 border-b border-white/5 pb-8">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-gold-500/10 rounded-xl flex items-center justify-center text-gold-500 border border-gold-500/20">
                <span class="dashicons dashicons-groups" style="font-size: 24px; width: 24px; height: 24px;"></span>
            </div>
            <div>
                <h1 class="font-serif italic text-3xl mb-1 leading-tight" style="color: #D4AF37 !important;">Gestão de Assinaturas</h1>
                <p class="text-[10px] uppercase tracking-[0.25em] font-medium" style="color: rgba(242, 212, 128, 0.6) !important;">Monitoramento de Acesso e Período de Graça</p>
            </div>
        </div>

        <div class="flex gap-4">
            <div class="bg-white/5 border border-white/10 px-6 py-3 rounded-2xl">
                <span class="text-[8px] uppercase font-black tracking-widest text-zinc-500 block">Ativos Agora</span>
                <span class="text-xl font-bold text-green-500"><?php echo $total_active; ?></span>
            </div>
            <div class="bg-white/5 border border-white/10 px-6 py-3 rounded-2xl">
                <span class="text-[8px] uppercase font-black tracking-widest text-zinc-500 block">Em Grace Period</span>
                <span class="text-xl font-bold text-gold-500"><?php echo $total_grace; ?></span>
            </div>
        </div>
    </header>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        <!-- Subscriptions Table -->
        <div class="xl:col-span-2 space-y-6">
            <div class="glass p-8 rounded-3xl border border-white/5 relative overflow-hidden group">
                <h3 class="text-xl font-bold font-serif italic mb-6 flex items-center gap-3" style="color: #D4AF37 !important;">
                    <span class="w-2 h-6 bg-gold-500 rounded-full"></span>
                    Membros Sincronizados
                </h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="text-[10px] uppercase tracking-widest text-zinc-500 border-b border-white/5">
                                <th class="pb-4">Membro</th>
                                <th class="pb-4">Status Local</th>
                                <th class="pb-4">Gateway</th>
                                <th class="pb-4">Expira em</th>
                                <th class="pb-4 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <?php foreach ( $subscriptions as $sub ) : ?>
                            <tr class="group hover:bg-white/[0.02] transition-all">
                                <td class="py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-white"><?php echo esc_html($sub->email); ?></span>
                                        <span class="text-[10px] text-zinc-600 font-mono"><?php echo esc_html($sub->plan_name ?: 'Plano não identificado'); ?></span>
                                    </div>
                                </td>
                                <td class="py-4">
                                    <?php 
                                        $status_class = 'bg-zinc-800 text-zinc-400';
                                        if($sub->status === 'active') $status_class = 'bg-green-500/10 text-green-500';
                                        if($sub->status === 'grace_period') $status_class = 'bg-gold-500/10 text-gold-500';
                                        if($sub->status === 'cancelled') $status_class = 'bg-red-500/10 text-red-500';
                                    ?>
                                    <span class="px-2 py-1 rounded-full text-[9px] font-black uppercase tracking-widest <?php echo $status_class; ?>">
                                        <?php echo str_replace('_', ' ', $sub->status); ?>
                                    </span>
                                </td>
                                <td class="py-4">
                                    <span class="text-[10px] font-mono text-zinc-500"><?php echo esc_html($sub->gateway_status ?: '—'); ?></span>
                                </td>
                                <td class="py-4">
                                    <div class="flex flex-col">
                                        <span class="text-[11px] text-white"><?php echo $sub->access_expires_at ? date('d/m/Y', strtotime($sub->access_expires_at)) : '—'; ?></span>
                                        <?php if($sub->status === 'grace_period') : ?>
                                            <span class="text-[8px] text-gold-500 font-bold uppercase">Grace até <?php echo date('d/m/Y', strtotime($sub->grace_ends_at)); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="<?php echo wp_nonce_url( admin_url('admin.php?page=lms-subscriptions&action_type=sync&email=' . $sub->email), 'lms_subscription_action' ); ?>" class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-zinc-500 hover:text-gold-500 hover:bg-gold-500/10 transition-all" title="Sincronizar">
                                            <span class="dashicons dashicons-update" style="font-size: 14px;"></span>
                                        </a>
                                        <?php if($sub->status !== 'cancelled') : ?>
                                        <a href="<?php echo wp_nonce_url( admin_url('admin.php?page=lms-subscriptions&action_type=expire&email=' . $sub->email), 'lms_subscription_action' ); ?>" class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-zinc-500 hover:text-red-500 hover:bg-red-500/10 transition-all" title="Encerrar Acesso">
                                            <span class="dashicons dashicons-no" style="font-size: 14px;"></span>
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
        </div>

        <!-- Audit Log -->
        <div class="space-y-6">
            <div class="glass p-8 rounded-3xl border border-white/5 h-full">
                <h3 class="text-xl font-bold font-serif italic mb-6 flex items-center gap-3" style="color: #D4AF37 !important;">
                    <span class="w-2 h-6 bg-zinc-800 rounded-full"></span>
                    Audit Log Recente
                </h3>

                <div class="space-y-4">
                    <?php foreach ( $events as $event ) : ?>
                    <div class="p-4 bg-white/[0.02] border border-white/5 rounded-2xl">
                        <div class="flex justify-between items-start mb-2">
                            <span class="text-[9px] font-black uppercase tracking-widest text-gold-500"><?php echo str_replace('_', ' ', $event->action); ?></span>
                            <span class="text-[8px] text-zinc-700 font-mono"><?php echo date('H:i', strtotime($event->created_at)); ?></span>
                        </div>
                        <p class="text-xs text-white font-bold truncate mb-1"><?php echo esc_html($event->email); ?></p>
                        <div class="flex items-center gap-2 text-[9px] font-mono">
                            <span class="text-zinc-600"><?php echo $event->status_before ?: '?'; ?></span>
                            <span class="text-zinc-800">→</span>
                            <span class="text-zinc-300"><?php echo $event->status_after ?: '?'; ?></span>
                        </div>
                        <?php if($event->reason) : ?>
                            <p class="mt-2 text-[10px] text-zinc-500 italic leading-relaxed border-t border-white/5 pt-2">
                                "<?php echo esc_html($event->reason); ?>"
                            </p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
    #wpcontent { background: #000 !important; }
    .glass { background: rgba(255, 255, 255, 0.02); backdrop-filter: blur(10px); }
    .elite-admin-wrap table { border-collapse: separate; border-spacing: 0; }
</style>
