<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Pagination, Search and Filter
$search = isset( $_GET['s_user'] ) ? sanitize_text_field( $_GET['s_user'] ) : '';
$f_role = isset( $_GET['f_role'] ) ? sanitize_text_field( $_GET['f_role'] ) : 'all';
$paged  = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
$per_page = 20;

$args = array(
    'role__in' => array( 'educadora', 'autoridade' ),
    'number'   => $per_page,
    'offset'   => ( $paged - 1 ) * $per_page,
    'orderby'  => 'display_name',
    'order'    => 'ASC',
);

if ( $f_role !== 'all' ) {
    $args['role'] = $f_role;
}

if ( ! empty( $search ) ) {
    $args['search']         = '*' . $search . '*';
    $args['search_columns'] = array( 'user_login', 'user_email', 'display_name' );
}

$user_query = new WP_User_Query( $args );
$users = $user_query->get_results();
$total_users = $user_query->get_total();
$total_pages = ceil( $total_users / $per_page );
?>

<style>
    /* Elite Full-Screen Override */
    #wpbody-content { padding-bottom: 0 !important; float: none !important; }
    #wpcontent { padding-left: 0 !important; background: #050505 !important; }
    .wrap { margin: 0 !important; max-width: none !important; }
    #adminmenumain { border-right: 1px solid #111 !important; }
    #wpfooter { display: none !important; }
    
    /* Hide default WP H1 if it exists */
    .wrap > h1:first-child { display: none !important; }
    
    /* Scrollbar Reset */
    ::-webkit-scrollbar { width: 8px; }
    ::-webkit-scrollbar-track { background: #0a0a0a; }
    ::-webkit-scrollbar-thumb { background: #222; border-radius: 10px; }
    ::-webkit-scrollbar-thumb:hover { background: #333; }
</style>

<div class="bg-[#050505] min-h-screen p-8 text-white font-sans sm:p-16">
    
    <!-- Header Section -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-10 mb-16 pb-12 border-b border-zinc-900/80">
        <div class="relative">
            <div class="absolute -left-6 top-1 w-1.5 h-16 bg-gold-500 rounded-full blur-[2px] opacity-40"></div>
            <h1 class="text-5xl font-serif italic text-white mb-3 tracking-tighter">Gestão de Benefícios <span class="text-gold-500">Elite</span></h1>
            <p class="text-zinc-500 text-[11px] uppercase tracking-[0.4em] font-bold opacity-60">Central de Inteligência & Recompensas</p>
        </div>
        
        <!-- Bulk Actions Container -->
        <div class="flex flex-wrap items-center gap-3">
            <button onclick="triggerBulkAction('enable_all')" class="group relative overflow-hidden bg-white text-black px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all hover:scale-105 active:scale-95 shadow-xl shadow-white/5">
                <span class="relative z-10 flex items-center gap-2">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    Liberar Geral
                </span>
            </button>
            <button onclick="triggerBulkAction('enable_educators')" class="bg-gold-500/10 hover:bg-gold-500/20 text-gold-500 px-5 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all border border-gold-500/20 active:scale-95">
                + Educadoras
            </button>
            <button onclick="triggerBulkAction('enable_authorities')" class="bg-zinc-800/40 hover:bg-zinc-800/60 text-zinc-300 px-5 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all border border-zinc-700 active:scale-95">
                + Autoridades
            </button>
            <button onclick="triggerBulkAction('disable_all')" class="bg-red-500/5 hover:bg-red-500/10 text-red-500/60 hover:text-red-500 px-5 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all border border-red-500/10 active:scale-95">
                Revogar Tudo
            </button>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="mb-10 flex flex-col md:flex-row gap-4">
        <form method="get" class="flex-1 flex flex-wrap gap-3">
            <input type="hidden" name="page" value="elite-benefits">
            
            <div class="relative flex-1 group min-w-[300px]">
                <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none z-10">
                    <svg class="w-6 h-6 text-gold-500/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" name="s_user" value="<?php echo esc_attr( $search ); ?>" 
                    placeholder="Filtrar por nome, email ou ID..." 
                    style="padding-left: 70px !important; height: 70px !important;"
                    class="w-full bg-white/5 border border-zinc-800 rounded-3xl text-xl text-white placeholder-zinc-600 focus:border-gold-500/50 focus:bg-black/40 outline-none transition-all shadow-2xl">
            </div>

            <select name="f_role" onchange="this.form.submit()" class="bg-zinc-900 border border-zinc-800 text-gold-500 px-6 rounded-2xl text-[10px] font-black uppercase tracking-widest outline-none focus:border-gold-500 transition-all cursor-pointer min-w-[200px]">
                <option value="all" <?php selected($f_role, 'all'); ?>>Todas as Categorias</option>
                <option value="educadora" <?php selected($f_role, 'educadora'); ?>>Apenas Educadoras</option>
                <option value="autoridade" <?php selected($f_role, 'autoridade'); ?>>Apenas Autoridades</option>
            </select>

            <button type="submit" class="bg-zinc-800 hover:bg-zinc-700 text-gold-500 border border-gold-500/20 px-8 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all">
                Aplicar Filtro
            </button>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-zinc-900/20 rounded-[40px] border border-zinc-900 overflow-hidden shadow-2xl backdrop-blur-md">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-white/[0.02] border-b border-zinc-800/50 text-[9px] text-zinc-500 uppercase tracking-[0.2em] font-black">
                    <th class="px-10 py-6">Membro Elite</th>
                    <th class="px-10 py-6 text-center">Categoria</th>
                    <th class="px-10 py-6 text-center">Checkout Discount</th>
                    <th class="px-10 py-6 text-right">Controle</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800/30">
                <?php if ( ! empty( $users ) ) : foreach ( $users as $u ) : 
                    $role_id = in_array( 'educadora', (array) $u->roles ) ? 'educadora' : 'autoridade';
                    $role_label = ($role_id === 'educadora') ? 'Educadora (40%)' : 'Autoridade (30%)';
                    $role_class = ($role_id === 'educadora') ? 'text-gold-500 bg-gold-500/10 border-gold-500/20' : 'text-zinc-400 bg-white/5 border-white/10';
                    $is_eligible = get_user_meta( $u->ID, '_lms_discount_eligible', true ) === 'yes';
                    
                    $approval_status = get_user_meta( $u->ID, '_lms_approval_status', true );
                    $pending_role = get_user_meta( $u->ID, '_lms_pending_role', true );
                    $is_pending = ($approval_status === 'pending');
                    $is_admin = in_array( 'administrator', (array) $u->roles );
                ?>
                    <tr class="group hover:bg-gold-500/[0.02] transition-colors">
                        <td class="px-10 py-6">
                            <div class="flex items-center gap-5">
                                <div class="w-12 h-12 rounded-2xl bg-zinc-800 border border-zinc-700 p-0.5 shadow-inner flex-shrink-0">
                                    <div class="w-full h-full rounded-[14px] overflow-hidden">
                                        <?php echo get_avatar( $u->ID, 48 ); ?>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-zinc-100 group-hover:text-gold-500 transition-colors uppercase tracking-tight"><?php echo esc_html( $u->display_name ); ?></p>
                                    <p class="text-[9px] text-zinc-500 font-medium uppercase tracking-[0.1em] mt-0.5"><?php echo esc_html( $u->user_email ); ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-10 py-6 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <span class="px-4 py-1.5 rounded-xl text-[8px] font-black uppercase tracking-widest border <?php echo $role_class; ?> shadow-sm">
                                    <?php echo $role_label; ?>
                                </span>
                                <?php if ($is_admin): ?>
                                    <span class="text-[7px] text-blue-400 font-bold uppercase tracking-widest mt-2 flex items-center gap-1" title="Proteção do Sistema"><svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg> BYPASS ADMIN</span>
                                <?php elseif (!$is_pending): ?>
                                    <button onclick="changeMemberRole(<?php echo $u->ID; ?>)" title="Trocar Categoria" class="flex items-center gap-1.5 text-zinc-600 hover:text-gold-500 transition-all opacity-40 hover:opacity-100 scale-95 hover:scale-100">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                        <span class="text-[7px] font-black uppercase tracking-widest">Trocar</span>
                                    </button>
                                <?php endif; ?>
                            </div>
                            <?php if ($is_pending): ?>
                                <p class="text-[7px] text-red-400 font-bold uppercase tracking-widest mt-2 animate-pulse">Solicitado: <?php echo ucfirst($pending_role); ?></p>
                            <?php endif; ?>
                        </td>
                        <td class="px-10 py-6 text-center">
                            <?php if ($is_pending): ?>
                                <span class="text-[9px] text-zinc-600 font-bold uppercase tracking-[0.2em] italic">AGUARDANDO APROVAÇÃO</span>
                            <?php elseif ( $is_eligible ) : ?>
                                <span class="text-[9px] text-emerald-400 font-bold uppercase tracking-[0.2em] flex items-center justify-center gap-2">
                                    <span class="relative flex h-2 w-2">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                    </span>
                                    HABILITADO
                                </span>
                            <?php else: ?>
                                <span class="text-[9px] text-zinc-500 font-bold uppercase tracking-[0.2em] opacity-40">BLOQUEADO</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-10 py-6 text-right">
                             <?php if ($is_admin): ?>
                                <span class="text-[8px] text-zinc-500 font-bold uppercase tracking-widest opacity-50 px-4">Nível Fixo</span>
                             <?php elseif ($is_pending): ?>
                                <button 
                                    onclick="approveRoleUpgrade(<?php echo $u->ID; ?>, this)" 
                                    class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl text-[9px] bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500 hover:text-black font-black uppercase tracking-widest transition-all shadow-xl shadow-emerald-500/10 transform hover:scale-105"
                                >
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    Aprovar Nível
                                </button>
                             <?php else: ?>
                                <button 
                                    onclick="toggleEligibility(<?php echo $u->ID; ?>, this)" 
                                    data-status="<?php echo $is_eligible ? 'yes' : 'no'; ?>"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-[8px] font-black uppercase tracking-widest transition-all border <?php echo $is_eligible ? 'bg-red-500/10 text-red-500/80 border-red-500/20 hover:bg-red-500 hover:text-white' : 'bg-gold-500/10 text-gold-500 border-gold-500/20 hover:bg-gold-500 hover:text-black'; ?>"
                                >
                                    <?php echo $is_eligible ? 'Revogar' : 'Conceder'; ?>
                                </button>
                             <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; else : ?>
                    <tr>
                        <td colspan="4" class="px-8 py-20 text-center">
                            <p class="text-zinc-600 uppercase tracking-widest text-xs">Nenhum potencial beneficiário encontrado.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ( $total_pages > 1 ) : ?>
        <div class="mt-12 flex items-center justify-center gap-3">
            <?php for ( $i = 1; $i <= $total_pages; $i++ ) : 
                $active_class = ( $i === $paged ) ? 'bg-gold-500 text-black shadow-2xl shadow-gold-500/30 scale-110' : 'bg-zinc-900/50 text-zinc-500 hover:text-white border border-zinc-800';
                $url = add_query_arg( 'paged', $i );
            ?>
                <a href="<?php echo esc_url( $url ); ?>" class="w-12 h-12 flex items-center justify-center rounded-2xl text-[10px] font-black transition-all <?php echo $active_class; ?>">
                    <?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>

</div>

<script>
async function toggleEligibility(userId, btn) {
    const currentStatus = btn.getAttribute('data-status');
    const newStatus = currentStatus === 'yes' ? 'no' : 'yes';
    
    btn.classList.add('opacity-40', 'pointer-events-none');
    
    const formData = new FormData();
    formData.append('action', 'lms_toggle_discount_eligibility');
    formData.append('user_id', userId);
    formData.append('status', newStatus);
    formData.append('nonce', '<?php echo wp_create_nonce("benefits_mgmt_nonce"); ?>');

    try {
        const response = await fetch(ajaxurl, { method: 'POST', body: formData });
        const data = await response.json();
        
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.data || 'Erro ao processar');
            btn.classList.remove('opacity-40', 'pointer-events-none');
        }
    } catch (e) {
        alert('Erro de conexão');
        btn.classList.remove('opacity-40', 'pointer-events-none');
    }
}

async function triggerBulkAction(actionType) {
    if(!confirm('Tem certeza que deseja processar esta ação em massa? Isso afetará muitos usuários simultaneamente.')) return;
    
    const formData = new FormData();
    formData.append('action', 'lms_bulk_discount_control');
    formData.append('bulk_type', actionType);
    formData.append('nonce', '<?php echo wp_create_nonce("benefits_mgmt_nonce"); ?>');

    try {
        const response = await fetch(ajaxurl, { method: 'POST', body: formData });
        const data = await response.json();
        
        if (data.success) {
            alert(data.data);
            window.location.reload();
        } else {
            alert(data.data || 'Erro ao processar ação em massa');
        }
    } catch (e) {
        alert('Erro de conexão ao processar comando em larga escala');
    }
}

async function approveRoleUpgrade(userId, btn) {
    if(!confirm('Deseja aprovar o upgrade de nível para este membro?')) return;
    
    btn.classList.add('opacity-40', 'pointer-events-none');
    
    const formData = new FormData();
    formData.append('action', 'lms_approve_role_upgrade');
    formData.append('user_id', userId);
    formData.append('nonce', '<?php echo wp_create_nonce("benefits_mgmt_nonce"); ?>');

    try {
        const response = await fetch(ajaxurl, { method: 'POST', body: formData });
        const data = await response.json();
        
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.data || 'Erro ao aprovar upgrade');
            btn.classList.remove('opacity-40', 'pointer-events-none');
        }
    } catch (e) {
        alert('Erro de conexão');
        btn.classList.remove('opacity-40', 'pointer-events-none');
    }
}

async function changeMemberRole(userId) {
    if(!confirm('Deseja realmente trocar a categoria deste membro (Educadora <-> Autoridade)?')) return;
    
    const formData = new FormData();
    formData.append('action', 'lms_change_member_role');
    formData.append('user_id', userId);
    formData.append('nonce', '<?php echo wp_create_nonce("benefits_mgmt_nonce"); ?>');

    try {
        const response = await fetch(ajaxurl, { method: 'POST', body: formData });
        const data = await response.json();
        
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.data || 'Erro ao trocar categoria');
        }
    } catch (e) {
        alert('Erro de conexão');
    }
}
</script>
