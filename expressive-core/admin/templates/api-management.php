<?php 
$api_url = get_option( 'lms_external_api_url', '' );
$api_token = get_option( 'lms_external_api_token', '' );
$last_api_log = get_option( 'lms_api_last_log', array() );

// Fetch Users for Audit (Same filter as Elite Members -> Assinantes)
$users = get_users( array( 
    'orderby' => 'registered', 
    'order'   => 'DESC',
    'role__in' => array('administrator', 'educadora', 'autoridade')
) );
$total_users = count( $users );
?>

<div class="elite-admin-wrap bg-[#000] text-white min-h-screen p-4 sm:p-8 rounded-xl shadow-2xl mr-4 mt-4 font-sans max-w-full overflow-x-hidden">
    
    <!-- HEADER & CONFIG TOP SECTION -->
    <header class="mb-10 text-center md:text-left">
        <div class="flex flex-col md:flex-row justify-between items-center gap-6 border-b border-white/5 pb-8">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gold-500/10 rounded-2xl flex items-center justify-center text-gold-500 border border-gold-500/20 shadow-lg shadow-gold-500/5">
                    <span class="dashicons dashicons-rest-api" style="font-size: 30px; width: 30px; height: 30px; color: #D4AF37;"></span>
                </div>
                <div>
                    <h1 class="font-serif italic text-4xl mb-1 leading-tight text-white tracking-tight">Elite <span style="color: #D4AF37;">API</span> Manager</h1>
                    <p class="text-[10px] uppercase tracking-[0.3em] text-zinc-500 font-bold">Arquitetura de Conectividade de Alto Padrão</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-4">
                <div class="px-6 py-3 bg-white/5 rounded-2xl border border-white/10 flex items-center gap-4 backdrop-blur-md">
                    <div class="flex flex-col">
                        <span class="text-[8px] uppercase font-black tracking-widest text-zinc-500 mb-0.5">Status do Servidor</span>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full <?php echo $api_url ? 'bg-green-500 animate-pulse shadow-[0_0_10px_#22c55e]' : 'bg-red-500'; ?>"></span>
                            <span class="text-[10px] uppercase font-bold tracking-widest <?php echo $api_url ? 'text-green-500' : 'text-red-500'; ?>"><?php echo $api_url ? 'Conectado' : 'Offline'; ?></span>
                        </div>
                    </div>
                </div>
                <button onclick="syncGlobalMembers(this)" class="px-8 py-4 bg-gold-500 hover:bg-white text-black rounded-2xl text-[11px] font-black uppercase tracking-[0.1em] transition-all shadow-xl shadow-gold-600/20 flex items-center gap-3 active:scale-95" style="background-color: #D4AF37 !important; color: #000 !important;">
                    <span class="dashicons dashicons-update" style="font-size: 16px; width: 16px; height: 16px;"></span>
                    Sincronização Global
                </button>
            </div>
        </div>
    </header>

    <!-- CONFIG & LOGS (FULL WIDTH) -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-8 mb-12">
        <!-- Configuration -->
        <div class="xl:col-span-7 glass p-10 rounded-[40px] border border-white/10 relative overflow-hidden group shadow-2xl">
            <div class="absolute top-0 left-0 w-1/2 h-1 bg-gradient-to-r from-transparent to-gold-500 opacity-30"></div>
            <h3 class="text-xl font-bold font-serif italic mb-10 text-white flex items-center gap-3">
                <span class="w-1.5 h-6 bg-gold-500 rounded-full shadow-[0_0_15px_#D4AF37]" style="background-color: #D4AF37;"></span>
                Configuração Estrutural
            </h3>
            
            <form method="post" action="" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-end">
                <?php wp_nonce_field( 'lms_save_api_nonce' ); ?>
                <div class="md:col-span-1 lg:col-span-2 space-y-3">
                    <label class="text-[9px] font-black uppercase tracking-[0.3em] text-zinc-600 ml-1">Endpoint de Sincronização</label>
                    <input name="api_url" type="url" value="<?php echo esc_attr( $api_url ); ?>" placeholder="https://api.exemplo.com/v1" class="w-full bg-black/80 border border-white/5 rounded-2xl px-6 py-5 text-sm focus:border-gold-500/50 outline-none transition-all text-white placeholder:text-zinc-900 border-l-2 border-l-gold-500/20">
                </div>
                <div class="md:col-span-1 lg:col-span-1 space-y-3">
                    <label class="text-[9px] font-black uppercase tracking-[0.3em] text-zinc-600 ml-1">Chave de Acesso</label>
                    <input name="api_token" type="password" value="<?php echo esc_attr( $api_token ); ?>" placeholder="••••••••••••" class="w-full bg-black/80 border border-white/5 rounded-2xl px-6 py-5 text-sm focus:border-gold-500/50 outline-none transition-all text-white placeholder:text-zinc-900">
                </div>
                <div class="md:col-span-1 lg:col-span-1">
                    <button type="submit" name="lms_save_api_settings" class="w-full py-5 bg-gold-500 hover:bg-white text-black rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] transition-all active:scale-95 shadow-lg shadow-gold-500/20" style="background-color: #D4AF37 !important; color: #000 !important;">
                        Salvar
                    </button>
                </div>
            </form>
        </div>

        <!-- Latest Log Info -->
        <div class="xl:col-span-5 glass p-10 rounded-[40px] border border-white/10 relative overflow-hidden bg-white/[0.01]">
            <div class="flex justify-between items-center mb-10">
                <h3 class="text-xl font-bold font-serif italic text-white flex items-center gap-3">
                    <span class="w-1.5 h-6 bg-zinc-800 rounded-full"></span>
                    Monitor de Tráfego
                </h3>
                <span class="text-[8px] bg-white/5 px-3 py-1 rounded-full text-zinc-500 font-black uppercase tracking-widest border border-white/5">Última Resposta</span>
            </div>
            
            <?php if ( ! empty( $last_api_log ) ) : ?>
                <div class="flex gap-4 mb-6">
                    <div class="flex-1 bg-black/60 p-4 rounded-2xl border border-white/5">
                        <span class="text-[7px] text-zinc-700 uppercase font-black block mb-1 tracking-widest">HTTP Status</span>
                        <span class="text-sm font-mono <?php echo $last_api_log['code'] < 400 ? 'text-green-500' : 'text-red-500'; ?> font-bold"><?php echo $last_api_log['code']; ?> <?php echo $last_api_log['code'] == 200 ? 'OK' : 'ERR'; ?></span>
                    </div>
                    <div class="flex-1 bg-black/60 p-4 rounded-2xl border border-white/5">
                        <span class="text-[7px] text-zinc-700 uppercase font-black block mb-1 tracking-widest">Timestamp</span>
                        <span class="text-xs font-mono text-zinc-500"><?php echo date('H:i:s', strtotime($last_api_log['timestamp'])); ?></span>
                    </div>
                </div>
                <div class="bg-black/80 p-5 rounded-3xl border border-white/5 group relative">
                    <div class="max-h-20 overflow-y-auto custom-scrollbar">
                        <pre class="text-[10px] font-mono text-gold-500/50 leading-tight"><?php echo esc_html( $last_api_log['response'] ); ?></pre>
                    </div>
                    <div class="absolute bottom-2 right-4 text-[7px] text-zinc-800 font-black uppercase tracking-tighter">JSON Stream</div>
                </div>
            <?php else : ?>
                <div class="flex flex-col items-center justify-center h-32 bg-black/20 rounded-[30px] border border-dashed border-white/10">
                    <span class="dashicons dashicons-cloud-upload text-zinc-800 mb-2" style="font-size: 30px; width: 30px; height: 30px;"></span>
                    <p class="text-[9px] font-black uppercase tracking-widest text-zinc-700">Aguardando Primeira Conexão</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- MAIN AUDIT TABLE -->
    <div class="glass rounded-[48px] border border-white/5 overflow-hidden shadow-2xl relative">
        <div class="absolute top-0 right-0 w-64 h-64 bg-gold-500/5 rounded-full -mr-32 -mt-32 blur-3xl"></div>
        
        <div class="p-10 border-b border-white/5 flex flex-col md:flex-row justify-between items-center bg-white/[0.01] gap-4">
            <div>
                <h3 class="text-2xl font-bold font-serif italic text-white flex items-center gap-3">Painel de Auditoria e Auditoria</h3>
                <p class="text-[10px] text-zinc-600 mt-1 uppercase tracking-[0.2em] font-black">Intervenção Manual vs Inteligência da API</p>
            </div>
            <div class="flex items-center gap-2">
                 <span class="px-4 py-2 bg-white/5 rounded-xl text-[10px] font-black text-white border border-white/10 uppercase tracking-widest">
                    <?php echo $total_users; ?> Membros Carregados
                </span>
            </div>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[10px] uppercase tracking-[0.25em] text-zinc-700 bg-white/[0.01]">
                        <th class="px-10 py-6">Perfil do Membro</th>
                        <th class="px-10 py-6 text-center">Status de Acesso</th>
                        <th class="px-10 py-6 text-center">Próxima Cobrança</th>
                        <th class="px-10 py-6 text-center">Diretriz de Controle</th>
                        <th class="px-10 py-6 text-right">Ficha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php 
                        $access_checker = new Expressive_Access();
                        foreach ( $users as $user ) : 
                        $is_active = $access_checker->has_active_subscription( $user->ID );
                        $is_admin = user_can( $user->ID, 'manage_options' );
                        $manual_status = get_user_meta( $user->ID, '_lms_elite_manual_status', true ) ?: 'none';
                    ?>
                    <tr id="user-row-<?php echo $user->ID; ?>" class="hover:bg-white/[0.03] transition-all group">
                        <td class="px-10 py-6">
                            <div class="flex items-center gap-5">
                                <div class="w-14 h-14 rounded-full overflow-hidden border-2 border-white/5 group-hover:border-gold-500/50 transition-all shadow-xl relative">
                                    <?php echo get_avatar( $user->ID, 56, '', '', array('class' => 'w-full h-full object-cover') ); ?>
                                    <?php if($is_admin): ?>
                                        <div class="absolute inset-0 bg-gold-500/10 ring-1 ring-gold-500/50 rounded-full"></div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-base font-bold <?php echo $is_admin ? 'text-gold-500' : 'text-white'; ?> tracking-tight"><?php echo esc_html( $user->display_name ); ?></span>
                                    <span class="text-[11px] text-zinc-600 font-medium"><?php echo esc_html( $user->user_email ); ?></span>
                                    <?php if($is_admin): ?>
                                        <div class="mt-1.5 flex items-center gap-1.5">
                                            <span class="text-[8px] bg-gold-500 text-black px-2 py-0.5 rounded-full font-black uppercase tracking-[0.1em]">VIP Admin</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="px-10 py-6 text-center">
                            <?php if ( $is_admin || $is_active ) : ?>
                                <div class="inline-flex items-center gap-2.5 px-5 py-2 bg-green-500/20 rounded-2xl border border-green-500/30 shadow-[0_0_40px_rgba(34,197,94,0.1)]">
                                    <span class="w-2.5 h-2.5 rounded-full bg-green-500 shadow-[0_0_12px_#22c55e]"></span>
                                    <span class="text-[10px] font-black text-green-500 uppercase tracking-widest">Liberado</span>
                                </div>
                            <?php else : ?>
                                <div class="inline-flex items-center gap-2.5 px-5 py-2 bg-red-500/20 rounded-2xl border border-red-500/30 shadow-[0_0_40px_rgba(239,68,68,0.1)]">
                                    <span class="w-2.5 h-2.5 rounded-full bg-red-500 shadow-[0_0_12px_#ef4444]"></span>
                                    <span class="text-[10px] font-black text-red-500 uppercase tracking-widest">Suspenso</span>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-10 py-6 text-center">
                            <?php 
                                $expiry = get_user_meta( $user->ID, '_lms_elite_api_expiry', true );
                                if ( $expiry ) :
                                    $date_formatted = date_i18n( 'd/m/Y', strtotime( $expiry ) );
                            ?>
                                <div class="flex flex-col items-center">
                                    <span class="text-[11px] font-mono text-gold-500/80"><?php echo $date_formatted; ?></span>
                                    <span class="text-[7px] uppercase text-zinc-700 font-black mt-1">Sincronizado</span>
                                </div>
                            <?php else : ?>
                                <span class="text-[9px] text-zinc-800 uppercase font-black tracking-widest">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-10 py-6 text-center">
                            <?php if ( ! $is_admin ) : ?>
                                <div class="flex items-center justify-center gap-1 p-1 bg-black/50 rounded-2xl border border-white/5 inline-flex">
                                    <button onclick="changeAccessState(<?php echo $user->ID; ?>, 'none', this)" class="px-4 py-2 rounded-xl text-[8px] font-black uppercase tracking-widest transition-all <?php echo $manual_status === 'none' ? 'bg-zinc-800 text-white border border-white/10 ring-1 ring-white/20' : 'text-zinc-600 hover:text-zinc-300'; ?>">Automático</button>
                                    <button onclick="changeAccessState(<?php echo $user->ID; ?>, 'blocked', this)" class="px-4 py-2 rounded-xl text-[8px] font-black uppercase tracking-widest transition-all <?php echo $manual_status === 'blocked' ? 'bg-red-600 text-white shadow-lg shadow-red-600/30' : 'text-zinc-600 hover:text-red-500'; ?>"><span class="dashicons dashicons-lock mr-1" style="font-size: 10px; width: 10px; height: 10px;"></span>Bloquear</button>
                                    <button onclick="changeAccessState(<?php echo $user->ID; ?>, 'unblocked', this)" class="px-4 py-2 rounded-xl text-[8px] font-black uppercase tracking-widest transition-all <?php echo $manual_status === 'unblocked' ? 'bg-green-600 text-white shadow-lg shadow-green-600/30' : 'text-zinc-600 hover:text-green-400'; ?>"><span class="dashicons dashicons-unlock mr-1" style="font-size: 10px; width: 10px; height: 10px;"></span>Liberar</button>
                                </div>
                            <?php else: ?>
                                <div class="text-[9px] text-zinc-800 font-black uppercase tracking-[0.3em]">Imunidade Ativa</div>
                            <?php endif; ?>
                        </td>
                        <td class="px-10 py-6 text-right">
                            <a href="<?php echo get_edit_user_link( $user->ID ); ?>" class="w-10 h-10 bg-white/5 rounded-xl border border-white/10 flex items-center justify-center text-zinc-600 hover:text-gold-500 hover:border-gold-500/30 transition-all inline-flex" title="Ver Ficha Técnica">
                                <span class="dashicons dashicons-id-alt"></span>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="p-8 bg-white/[0.01] text-center border-t border-white/5">
            <p class="text-[10px] italic text-zinc-700 font-serif tracking-widest uppercase">Engine V3.0 • Criptografia de Ponta a Ponta • Elite LMS System</p>
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;700;900&family=Playfair+Display:ital,wght@1,700;1,900&display=swap');
    
    #wpcontent { background: #000 !important; padding-left: 0 !important; }
    #wpbody-content { padding-bottom: 0 !important; }
    .elite-admin-wrap { font-family: 'Outfit', sans-serif !important; }
    .elite-admin-wrap h1, .elite-admin-wrap h3 { font-family: 'Playfair Display', serif !important; }
    .glass { background: rgba(25, 25, 25, 0.4); backdrop-filter: blur(25px); }
    
    /* Custom Scrollbar */
    .custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #333; border-radius: 20px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #D4AF37; }
    
    .elite-admin-wrap table { min-width: 900px; }
    
    input::placeholder { color: #222 !important; }
    
    /* Buttons Hover Effects */
    .elite-admin-wrap button { cursor: pointer; }
</style>

<script>
    const ajax_url = '<?php echo admin_url("admin-ajax.php"); ?>';

    function syncGlobalMembers(btn) {
        if (btn.disabled) return;
        btn.innerHTML = '<span class="dashicons dashicons-update animate-spin"></span> Processando...';
        btn.disabled = true;

        const formData = new FormData();
        formData.append('action', 'lms_sync_all_api_status');

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

    function changeAccessState(userId, newState, btn) {
        if (btn.classList.contains('active')) return;
        
        // Show immediate loading on the row
        const row = document.getElementById('user-row-' + userId);
        row.style.opacity = '0.5';
        row.style.pointerEvents = 'none';

        const formData = new FormData();
        formData.append('action', 'lms_set_manual_status');
        formData.append('user_id', userId);
        formData.append('new_status', newState);

        fetch(ajax_url, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.data || 'Erro de permissão.');
                row.style.opacity = '1';
                row.style.pointerEvents = 'auto';
            }
        });
    }
</script>
