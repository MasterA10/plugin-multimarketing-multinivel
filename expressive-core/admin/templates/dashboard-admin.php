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
$total_educators = count(get_users(array('meta_key' => '_lms_is_educator', 'meta_value' => '1')));
$total_courses = wp_count_posts('lms_course')->publish;
$total_lessons = wp_count_posts('lms_lesson')->publish;
$total_authorities = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}lms_referrals");
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
            <span class="text-[10px] text-gold-400/70 font-bold uppercase tracking-widest">Educadores Ativos</span>
            <div class="text-3xl font-bold mt-1 text-white"><?php echo $total_educators; ?></div>
        </div>

        <div class="bg-white/5 p-6 rounded-2xl border border-white/10 hover:border-gold-500/30 transition-all group">
            <div class="text-gold-500 mb-2 opacity-50 group-hover:opacity-100 transition-all">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <span class="text-[10px] text-gold-400/70 font-bold uppercase tracking-widest">Autoridades Registradas</span>
            <div class="text-3xl font-bold mt-1 text-white"><?php echo $total_authorities; ?></div>
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
                <button onclick="switchAdminTab('autoridades')" class="admin-tab-btn px-6 py-2.5 rounded-lg text-[10px] font-bold uppercase tracking-widest transition-all text-zinc-500">Autoridades</button>
                <button onclick="switchAdminTab('alunos')" class="admin-tab-btn px-6 py-2.5 rounded-lg text-[10px] font-bold uppercase tracking-widest transition-all text-zinc-500">Alunos</button>
            </div>
        </div>

        <!-- TAB: Educadores -->
        <div id="admin-tab-educadores" class="admin-tab-content active transition-all">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-white/10">
                            <th class="py-4 px-4 text-[9px] uppercase tracking-widest text-zinc-500">Educador</th>
                            <th class="py-4 px-4 text-[9px] uppercase tracking-widest text-zinc-500 text-center">Rank</th>
                            <th class="py-4 px-4 text-[9px] uppercase tracking-widest text-zinc-500 text-center">Autoridades</th>
                            <th class="py-4 px-4 text-[9px] uppercase tracking-widest text-zinc-500 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php 
                        $educators = get_users(array('meta_key' => '_lms_is_educator', 'meta_value' => '1'));
                        foreach ($educators as $edu): 
                            $rank = get_user_meta($edu->ID, '_lms_rank_name', true) ?: 'Bronze';
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
                            <th class="py-4 px-4 text-[9px] uppercase tracking-widest text-zinc-500 text-right">Conectado em</th>
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
                            <td class="py-4 px-4 text-right text-[10px] text-zinc-600 font-mono italic">
                                <?php echo date('d/m/Y H:i', strtotime($auth->created_at)); ?>
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
                $students = get_users(array('number' => 20, 'orderby' => 'registered', 'order' => 'DESC'));
                foreach ($students as $stu): 
                    if (get_user_meta($stu->ID, '_lms_is_educator', true)) continue;
                ?>
                <div class="bg-black/20 p-4 rounded-2xl border border-white/5 hover:border-gold-500/30 transition-all">
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
    function switchAdminTab(tab) {
        // Hide all
        document.querySelectorAll('.admin-tab-content').forEach(c => {
            c.classList.add('hidden', 'h-0', 'opacity-0');
            c.classList.remove('active', 'opacity-100');
        });
        
        // Remove active class from buttons
        document.querySelectorAll('.admin-tab-btn').forEach(b => {
            b.classList.remove('active', 'bg-gold-500/10', 'border', 'border-gold-500/50', 'text-gold-500');
            b.classList.add('text-zinc-500');
        });

        // Show target
        const target = document.getElementById('admin-tab-' + tab);
        target.classList.remove('hidden', 'h-0', 'opacity-0');
        setTimeout(() => {
            target.classList.add('active', 'opacity-100');
        }, 10);

        // Highlight button
        event.currentTarget.classList.add('active', 'bg-gold-500/10', 'border', 'border-gold-500/50', 'text-gold-500');
        event.currentTarget.classList.remove('text-zinc-500');
    }
</script>

<style>
    /* Scoped Fixes for WP Admin Interference */
    #wpcontent { background: #000 !important; }
    .elite-admin-wrap { font-family: 'Outfit', sans-serif !important; }
    .elite-admin-wrap h1, .elite-admin-wrap h2, .elite-admin-wrap h3 { font-family: 'Playfair Display', serif !important; }
    .admin-tab-btn.active { box-shadow: 0 4px 12px rgba(212, 175, 55, 0.2); }
    .admin-tab-content { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
</style>
