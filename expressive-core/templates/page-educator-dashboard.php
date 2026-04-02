<?php
/**
 * Template Name: Educator Dashboard
 * 
 * Standalone template for a luxury educator dashboard.
 */
if ( ! is_user_logged_in() ) {
    wp_redirect( site_url( '/login/' ) );
    exit;
}

// Redirect to unified dashboard
wp_redirect( home_url( '/area-de-membros?tab=ranking' ) );
exit;

if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'edit_posts' ) && ! get_user_meta( get_current_user_id(), '_lms_is_educator', true ) ) {

$user_id = get_current_user_id();
$user_data = get_userdata( $user_id );

// Dashboard Logic
global $wpdb;
$table_refs = $wpdb->prefix . 'lms_referrals';
$ref_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_refs WHERE educator_id = %d", $user_id));

// Admin Overdrive (Treat admin as educator and authority)
$is_admin = current_user_can('manage_options');
if ($is_admin) {
    // If admin has no refs, use simulated logic for preview
    if ($ref_count == 0) $ref_count = 12; // Example: Silver rank
}

// Rank Progress Math (Regra dos 10)
$ranks = array(
    1 => array(
        'name'  => 'Bronze',   
        'min'   => 0,   
        'max'   => 10, 
        'color' => '#CD7F32',
        'icon'  => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15l-2 5l2 2l2-2l-2-5zM12 15l2 5l-2 2l-2-2l2-5zM12 3a6 6 0 1 0 0 12a6 6 0 1 0 0 -12z"></path></svg>'
    ),
    2 => array(
        'name'  => 'Prata',    
        'min'   => 10,  
        'max'   => 20, 
        'color' => '#C0C0C0',
        'icon'  => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8l-2 4h4l-2 -4zM8 21l4-9l4 9zM4 14h16"></path></svg>'
    ),
    3 => array(
        'name'  => 'Ouro',     
        'min'   => 20,  
        'max'   => 30, 
        'color' => '#D4AF37',
        'icon'  => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.54 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.784.57-1.838-.196-1.539-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>'
    ),
    4 => array(
        'name'  => 'Platina',  
        'min'   => 30,  
        'max'   => 40, 
        'color' => '#E5E4E2',
        'icon'  => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>'
    ),
    5 => array(
        'name'  => 'Diamante', 
        'min'   => 40,  
        'max'   => 50, 
        'color' => '#B9F2FF',
        'icon'  => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 5L3 9l9 10L21 9l-3-4H6zM3 9h18M9 5l3 4l3-4"></path></svg>'
    ),
    6 => array(
        'name'  => 'Elite',    
        'min'   => 50,  
        'max'   => 1000, 
        'color' => '#A855F7',
        'icon'  => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>'
    )
);

// Determine current level based on refs
$current_level = 1;
foreach($ranks as $lv => $r) {
    if($ref_count >= $r['min']) $current_level = $lv;
}
$rank_name = $ranks[$current_level]['name'];

// Update user meta if increased (skip for admin simulation)
if (!$is_admin || ($is_admin && $ref_count > 0)) {
    update_user_meta($user_id, '_lms_rank_level', $current_level);
    update_user_meta($user_id, '_lms_rank_name', $rank_name);
}

$next_lv = ($current_level < 6) ? $current_level + 1 : 6;
$threshold_low = $ranks[$current_level]['min'];
$threshold_high = $ranks[$current_level]['max'];
$progress_towards_next = ($current_level == 6) ? 100 : min( 100, (($ref_count - $threshold_low) / ($threshold_high - $threshold_low)) * 100 );
$needed_for_next = ($current_level == 6) ? 0 : $threshold_high - $ref_count;

// Fetch ALL Authorities for Detailed Directory
$authorities_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_refs WHERE educator_id = %d ORDER BY created_at DESC", $user_id));
$total_lessons_platform = wp_count_posts('lms_lesson')->publish;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard do Educador - Expressive Core</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&family=Playfair+Display:ital,wght@0,700;1,700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        gold: {
                            400: '#F2D480',
                            500: '#D4AF37',
                            600: '#B8962E',
                        },
                        onyx: '#121212',
                    },
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    }
                }
            }
        }
    </script>
    <style>
        .glass { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(12px); border: 1px solid rgba(212, 175, 55, 0.1); }
        .gold-glow { box-shadow: 0 0 30px rgba(212, 175, 55, 0.1); }
        .sidebar-active { background: linear-gradient(90deg, rgba(212, 175, 55, 0.2) 0%, transparent 100%); border-left: 3px solid #D4AF37; }
    </style>
</head>
<body class="bg-black text-white font-sans min-h-screen flex">

    <!-- Sidebar (Shared Style) -->
    <aside class="w-72 bg-onyx border-r border-white/5 flex-col hidden lg:flex">
        <div class="p-8 text-center border-b border-white/5">
            <h1 class="font-serif text-2xl text-gold-500 italic mb-1">Elite Educator</h1>
            <p class="text-xs text-gray-500 uppercase tracking-tighter">Gestão de Autoridades</p>
        </div>

        <?php if (current_user_can('manage_options')): ?>
            <div class="px-6 py-6 border-b border-white/5 bg-gold-500/5">
                <p class="text-[8px] text-zinc-600 uppercase tracking-widest mb-4">Admin Persona Controller</p>
                <a href="<?php echo home_url('/area-de-membros'); ?>" class="w-full py-3 bg-gold-500 text-black rounded-xl text-[9px] font-bold uppercase tracking-widest flex items-center justify-center gap-2 hover:bg-gold-400 transition-all shadow-lg shadow-gold-500/20">
                    <span class="dashicons dashicons-visibility"></span> Ver Dashboard Aluno
                </a>
            </div>
        <?php endif; ?>

        <nav class="flex-1 p-4 space-y-2 mt-6">
            <a href="<?php echo site_url('/area-de-membros/'); ?>" class="flex items-center gap-3 px-6 py-4 text-gray-400 hover:text-white transition-all group">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.168 0.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332 0.477 4.5 1.253v13C19.832 18 18.246 17.5 16.5 17.5c-1.747 0-3.168 0.477-4.5 1.253"></path></svg>
                <span class="text-sm font-medium">Voltar aos Cursos</span>
            </a>
            <a href="#" class="sidebar-active flex items-center gap-3 px-6 py-4 text-gold-400 group">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                <span class="text-sm font-medium">Minha Rede / Ranking</span>
            </a>
        </nav>

        <div class="p-8 border-t border-white/5 space-y-4">
            <h5 class="text-[10px] text-gray-500 uppercase tracking-widest mb-4">Seu Link de Indicação</h5>
            <div class="bg-black/50 p-3 rounded-lg border border-white/10 text-[10px] text-gold-400 font-mono break-all line-clamp-1 border-dashed">
                <?php echo site_url('/?ref=' . $user_data->user_login); ?>
            </div>
            <button onclick="copyReferralLink(this)" class="w-full py-2 bg-white/5 hover:bg-white/10 border border-white/10 rounded-lg text-[10px] uppercase font-bold tracking-widest transition-all">Copiar Link</button>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 bg-black overflow-y-auto">
        <!-- Header -->
        <header class="h-24 px-10 flex items-center justify-between border-b border-white/5 sticky top-0 bg-black/80 backdrop-blur-md z-10">
            <div class="flex flex-col">
                <h2 class="text-xl font-semibold">Painel de Expansão</h2>
                <p class="text-xs text-gray-500">Gestão de Autoridades e Resultados Educacionais.</p>
            </div>

            <div class="flex items-center gap-6">
                <!-- Current Rank Identity -->
                <div class="flex items-center gap-3 bg-white/5 px-4 py-2 rounded-full border border-gold-500/20">
                    <div class="w-2 h-2 bg-gold-500 rounded-full animate-pulse shadow-[0_0_8px_#D4AF37]"></div>
                    <span class="text-xs font-bold text-gold-400 uppercase tracking-widest">Educador: <?php echo $rank_name; ?></span>
                </div>
            </div>
        </header>

        <!-- Body -->
        <div class="p-10 max-w-7xl mx-auto space-y-10">

            <!-- Ranking Evolution Card -->
            <div class="glass p-10 rounded-3xl border border-white/5 gold-glow overflow-hidden relative">
                <div class="absolute inset-0 bg-gradient-to-r from-gold-500/5 to-transparent pointer-events-none"></div>
                
                <div class="relative z-10">
                    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-end mb-12 gap-6">
                        <div>
                            <h3 class="text-4xl font-serif italic text-gold-500 mb-2">Roadmap to Elite</h3>
                            <p class="text-gray-400 text-sm leading-relaxed max-w-xl">Sua trajetória no Marco Zero é definida pelo impacto que você gera. Cada autoridade conectada acelera sua ascensão ao topo da pirâmide educacional.</p>
                        </div>
                        <div class="px-6 py-3 rounded-2xl flex items-center gap-4 shadow-xl transition-all duration-500" style="background-color: <?php echo $ranks[$current_level]['color']; ?>; color: <?php echo in_array($current_level, [4,5]) ? '#121212' : '#000'; ?>; box-shadow: 0 10px 25px <?php echo $ranks[$current_level]['color']; ?>40;">
                            <div class="p-2 bg-black/10 rounded-lg">
                                <?php echo $ranks[$current_level]['icon']; ?>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-[9px] font-bold uppercase tracking-widest opacity-60">Estágio Atual:</span>
                                <span class="text-xl font-black uppercase tracking-tight"><?php echo $rank_name; ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Visual Roadmap -->
                    <div class="relative py-12 px-4 mb-8">
                        <!-- Horizontal Track -->
                        <div class="absolute top-1/2 left-0 right-0 h-1 bg-white/5 -translate-y-1/2 rounded-full">
                            <div class="h-full shadow-[0_0_15px_currentColor] transition-all duration-1000" style="width: <?php echo (($current_level - 1) / 5) * 100 + ($progress_towards_next / 5); ?>%; background-color: <?php echo $ranks[$current_level]['color']; ?>; color: <?php echo $ranks[$current_level]['color']; ?>;"></div>
                        </div>

                        <!-- Nodes -->
                        <div class="relative flex justify-between">
                            <?php foreach($ranks as $lv => $r): 
                                $is_past = $lv < $current_level;
                                $is_current = $lv == $current_level;
                            ?>
                            <div class="flex flex-col items-center group">
                                <div class="w-12 h-12 rounded-full border-2 transition-all duration-500 flex items-center justify-center z-10 
                                    <?php echo $is_past ? 'text-black' : ($is_current ? 'scale-125 gold-glow' : 'bg-black border-white/10 text-white/20'); ?>"
                                    style="<?php echo $is_past ? 'background-color: '.$r['color'].'; border-color: '.$r['color'] : ($is_current ? 'background-color: #000; border-color: '.$r['color'].'; color: '.$r['color'] : ''); ?>">
                                    <?php if($is_past): ?><span class="dashicons dashicons-yes"></span><?php else: echo $r['icon']; endif; ?>
                                </div>
                                <div class="absolute mt-16 flex flex-col items-center opacity-40 group-hover:opacity-100 <?php echo $is_current ? 'opacity-100' : ''; ?> transition-opacity">
                                    <span class="text-[9px] font-bold uppercase tracking-[0.2em]" style="color: <?php echo $is_current ? $r['color'] : '#fff'; ?>"><?php echo $r['name']; ?></span>
                                    <span class="text-[8px] text-zinc-600 mt-1"><?php echo $r['min']; ?>+ Refs</span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center mt-12 bg-white/[0.02] p-8 rounded-2xl border border-white/5">
                        <div>
                             <div class="flex justify-between text-xs font-bold uppercase tracking-widest mb-2">
                                <span class="text-white">Progresso para <?php echo $ranks[$next_lv]['name']; ?></span>
                                <span style="color: <?php echo $ranks[$next_lv]['color']; ?>"><?php echo round($progress_towards_next); ?>%</span>
                            </div>
                            <div class="w-full h-2 bg-white/5 rounded-full overflow-hidden">
                                <div class="h-full transition-all duration-1000" style="width: <?php echo $progress_towards_next; ?>%; background-color: <?php echo $ranks[$next_lv]['color']; ?>;"></div>
                            </div>
                            <p class="text-[10px] text-zinc-500 mt-3 uppercase tracking-widest leading-relaxed">
                                <?php if($current_level < 6): ?>
                                    Faltam <strong><?php echo $needed_for_next; ?> authorities</strong> para o próximo nível de bonificação.
                                <?php else: ?>
                                    Você atingiu o status máximo de **Elite**. Parabéns pela liderança.
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-black/40 p-4 rounded-xl border border-white/5 text-center">
                                <span class="text-[9px] text-zinc-500 uppercase tracking-widest">Autoridades</span>
                                <div class="text-2xl font-bold mt-1"><?php echo $ref_count; ?></div>
                            </div>
                             <div class="bg-black/40 p-4 rounded-xl border border-white/5 text-center">
                                <span class="text-[9px] text-zinc-500 uppercase tracking-widest">Bonus/Ref</span>
                                <div class="text-2xl font-bold mt-1" style="color: <?php echo $ranks[$current_level]['color']; ?>">R$ <?php echo 100 + ($current_level * 50); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Phase 32: Detailed Authority Leadership Directory -->
            <div class="glass p-10 rounded-3xl border border-white/5 gold-glow">
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h3 class="text-2xl font-serif italic text-gold-500">Diretório de Liderança Elite</h3>
                        <p class="text-xs text-gray-500 uppercase tracking-widest mt-1">Acompanhamento de Performance da Rede</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-white/5 text-[9px] uppercase tracking-widest text-zinc-500">
                                <th class="py-4 px-2">Autoridade Conectada</th>
                                <th class="py-4 px-2 text-center">Progresso (Insights)</th>
                                <th class="py-4 px-2 text-center">Data de Ingresso</th>
                                <th class="py-4 px-2 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <?php if($authorities_data): foreach($authorities_data as $ref): 
                                $auth = get_userdata($ref->authority_id);
                                if(!$auth) continue;
                                $completed = get_user_meta($ref->authority_id, '_lms_completed_lessons', true) ?: [];
                                $progress_pct = $total_lessons_platform > 0 ? round((count($completed) / $total_lessons_platform) * 100) : 0;
                            ?>
                            <tr class="group hover:bg-white/[0.02] transition-all">
                                <td class="py-5 px-2 flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full border border-gold-500/20 p-0.5 overflow-hidden">
                                        <?php echo Expressive_Core::get_elite_avatar($ref->authority_id, 40, 'rounded-full'); ?>
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-white"><?php echo esc_html($auth->display_name); ?></div>
                                        <div class="text-[9px] text-zinc-600 uppercase tracking-wider"><?php echo esc_html($auth->user_email); ?></div>
                                    </div>
                                </td>
                                <td class="py-5 px-2">
                                    <div class="w-48 mx-auto">
                                        <div class="flex justify-between text-[8px] uppercase tracking-widest mb-1 text-zinc-500">
                                            <span>Domínio</span>
                                            <span><?php echo $progress_pct; ?>%</span>
                                        </div>
                                        <div class="w-full h-1 bg-white/5 rounded-full overflow-hidden">
                                            <div class="h-full bg-gold-600 transition-all duration-1000" style="width: <?php echo $progress_pct; ?>%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-5 px-2 text-center text-[10px] text-zinc-500">
                                    <?php echo date('d M Y', strtotime($ref->created_at)); ?>
                                </td>
                                <td class="py-5 px-2 text-right">
                                    <span class="px-3 py-1 bg-green-500/10 text-green-500 border border-green-500/20 text-[8px] font-bold uppercase rounded-full">Ativo</span>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="4" class="py-10 text-center text-xs text-zinc-600 italic">Nenhuma autoridade conectada à sua rede ainda.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <script>
        function copyReferralLink(btn) {
            const link = "<?php echo site_url('/?ref=' . $user_data->user_login); ?>";
            const originalText = btn.innerText;
            
            navigator.clipboard.writeText(link).then(() => {
                btn.innerText = "Copiado!";
                btn.classList.add('bg-gold-500', 'text-black');
                setTimeout(() => {
                    btn.innerText = originalText;
                    btn.classList.remove('bg-gold-500', 'text-black');
                }, 2000);
            }).catch(err => {
                console.error('Erro ao copiar: ', err);
            });
        }
    </script>
</body>
</html>
