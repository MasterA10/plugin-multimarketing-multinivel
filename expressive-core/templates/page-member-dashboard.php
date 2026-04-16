<?php
/**
 * Template Name: Member Area Dashboard
 * 
 * Standalone template for a luxury student dashboard.
 */
if ( ! is_user_logged_in() ) {
    // Guest Access Allowed for Dashboard (Elite Curiosity Model)
}
show_admin_bar(false);

$user_id = get_current_user_id();
$user_data = $user_id ? get_userdata( $user_id ) : (object) array(
    'display_name' => 'Visitante de Elite',
    'user_login'    => 'guest'
);

// Check if Guest
$is_guest = ! $user_id;

// Dashboard Educator Logic
global $wpdb;
$table_refs = $wpdb->prefix . 'lms_referrals';
$ref_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_refs WHERE educator_id = %d", $user_id));

// Admin Overdrive
$is_admin = $user_id ? current_user_can('manage_options') : false;
if ($is_admin) {
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

$next_lv = ($current_level < 6) ? $current_level + 1 : 6;
$threshold_low = $ranks[$current_level]['min'];
$threshold_high = $ranks[$current_level]['max'];
$progress_towards_next = ($current_level == 6) ? 100 : min( 100, (($ref_count - $threshold_low) / ($threshold_high - $threshold_low)) * 100 );
$needed_for_next = ($current_level == 6) ? 0 : $threshold_high - $ref_count;

// Fetch ALL Authorities for Detailed Directory
$authorities_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_refs WHERE educator_id = %d ORDER BY created_at DESC", $user_id));

// Calculate Total Revenue from Referrals
$total_revenue = 0;
foreach($authorities_data as $ref) { $total_revenue += floatval($ref->order_total); }

$total_commissions = 0;
foreach($authorities_data as $ref) { $total_commissions += floatval($ref->commission_amount); }

// Fetch personal referrer
$my_referrer_id = $wpdb->get_var($wpdb->prepare("SELECT educator_id FROM $table_refs WHERE authority_id = %d", $user_id));
$my_referrer = $my_referrer_id ? get_userdata($my_referrer_id) : null;

// Calculate GLOBAL Presence System
$completed_lesson_ids = get_user_meta( $user_id, '_lms_completed_lessons', true ) ?: [];
$total_lessons_watched = count($completed_lesson_ids);

$all_lessons_query = get_posts( array( 
    'post_type' => 'lms_lesson', 
    'posts_per_page' => -1,
    'post_status' => 'publish'
) );
$total_lessons_platform = count($all_lessons_query);

// Global Presence Percentage
$global_presence_pct = $total_lessons_platform > 0 ? round( ($total_lessons_watched / $total_lessons_platform) * 100 ) : 0;
$can_generate_certificate = ($global_presence_pct >= 75);

// Calculate Real Training Hours based on _lms_duration meta
$total_training_minutes = 0;
foreach ( $completed_lesson_ids as $watched_id ) {
    $duration = get_post_meta( $watched_id, '_lms_duration', true ) ?: 15;
    $total_training_minutes += intval($duration);
}
$total_training_hours = round( $total_training_minutes / 60, 1 );

// Fetch Global Annual Ranking
$ranking_data = Expressive_Referral::get_annual_ranking(50);
$my_global_rank = $user_id ? Expressive_Referral::get_user_rank_position($user_id) : 0;
$current_date = date_i18n('d \d\e F, Y');
$show_commissions = get_option('lms_show_commissions', 'yes');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área de Membros Elite - Expressive Core</title>
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
        .gold-glow { box-shadow: 0 0 20px rgba(212, 175, 55, 0.1); }
        .sidebar-active { background: linear-gradient(90deg, rgba(212, 175, 55, 0.2) 0%, transparent 100%); border-left: 3px solid #D4AF37; }
        .course-card:hover { transform: translateY(-5px); border-color: #D4AF37; }
        .aspect-4-5 { aspect-ratio: 4/5; }
    </style>
</head>
<?php 
$is_educator = $user_id ? Expressive_Referral::is_educator( $user_id ) : false;
$access_checker = new Expressive_Access();
$has_access = $user_id ? $access_checker->has_active_subscription( $user_id ) : false;

// RBAC Visibility Logic
$visibility_meta_query = array(
    'relation' => 'OR',
    array(
        'key' => '_lms_visibility_role',
        'compare' => 'NOT EXISTS'
    ),
    array(
        'key' => '_lms_visibility_role',
        'value' => 'all',
        'compare' => '='
    ),
    array(
        'key' => '_lms_visibility_role',
        'value' => $is_educator ? 'educadora' : 'autoridade',
        'compare' => '='
    )
);
?>
<body class="bg-black text-white font-sans min-h-screen flex">

    <!-- OVERLAY DE BLOQUEIO / MENSALIDADE (DEPRECATED FOR CURIOSITY ACCESS) -->
    <?php /* if ( ! $has_access ) : ?>
    <div class="fixed inset-0 z-50 bg-black/95 backdrop-blur-md flex items-center justify-center">
        ...
    </div>
    <style>body { height: 100vh; overflow: hidden; }</style>
    <?php wp_head(); ?>
    <?php endif; */ ?>

    <!-- Sidebar shadow for mobile -->
    <div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 hidden lg:hidden transition-opacity duration-300 opacity-0"></div>

    <!-- Sidebar -->
    <aside id="elite-sidebar" class="fixed lg:static inset-y-0 left-0 w-72 bg-onyx border-r border-white/5 flex-col z-50 transform -translate-x-full lg:translate-x-0 transition-all duration-500 flex lg:flex">
        <div class="p-8 text-center border-b border-white/5 flex flex-col items-center">
            <h1 class="font-serif text-2xl text-gold-500 italic mb-1">Elite Members</h1>
            <p class="text-xs text-gray-500 uppercase tracking-tighter mb-4">Área de Exclusividade</p>
            <span class="px-3 py-1 rounded-lg text-[10px] font-bold uppercase tracking-widest bg-white/5 text-gold-400 border border-gold-500/10">
                LMS: <?php echo $is_educator ? 'Educadora' : 'Autoridade'; ?>
            </span>
            <?php if ($my_referrer): ?>
                <div class="mt-4 flex flex-col items-center">
                    <span class="text-[8px] text-zinc-600 uppercase tracking-widest mb-1 italic">Conectada por:</span>
                    <div class="flex items-center gap-2 px-3 py-1.5 bg-gold-500/5 border border-gold-500/10 rounded-full">
                        <div class="w-4 h-4 rounded-full border border-gold-500/30 overflow-hidden">
                            <?php echo get_avatar($my_referrer_id, 16); ?>
                        </div>
                        <span class="text-[10px] text-gold-500 font-medium"><?php echo esc_html($my_referrer->display_name); ?></span>
                    </div>
                </div>
            <?php else: ?>
                <div class="mt-4 flex flex-col items-center opacity-30">
                    <span class="text-[8px] text-zinc-600 uppercase tracking-widest italic">Membro Direto / Orgânico</span>
                </div>
            <?php endif; ?>
        </div>

        <nav class="p-4 space-y-2 mt-6">
            <a href="javascript:void(0)" onclick="switchTab('home', this)" class="tab-link sidebar-active flex items-center gap-3 px-6 py-4 text-gold-400 group">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span class="text-sm font-medium">Início / Dash</span>
            </a>
            <a href="javascript:void(0)" onclick="switchTab('courses', this)" class="tab-link flex items-center gap-3 px-6 py-4 text-gray-400 hover:text-white transition-all group">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.168 0.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332 0.477 4.5 1.253v13C19.832 18 18.246 17.5 16.5 17.5c-1.747 0-3.168 0.477-4.5 1.253"></path></svg>
                <span class="text-sm font-medium">Meus Treinamentos</span>
            </a>
            <a href="javascript:void(0)" onclick="switchTab('lives', this)" class="tab-link flex items-center gap-3 px-6 py-4 text-gray-400 hover:text-white transition-all group">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span class="text-sm font-medium">Calendário de Lives</span>
            </a>
            
            <a href="<?php echo home_url('/equipe-academia'); ?>" target="_blank" class="flex items-center gap-3 px-6 py-4 text-gray-400 hover:text-gold-500 transition-all group">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <span class="text-sm font-medium">Equipe Academia</span>
            </a>

            <?php if ($is_educator): ?>
            <a href="javascript:void(0)" onclick="switchTab('ranking', this)" class="tab-link flex items-center gap-3 px-6 py-4 text-gray-400 hover:text-gold-500 transition-all group border-t border-white/5 mt-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span class="text-sm font-medium">Minha Rede</span>
            </a>
            <?php endif; ?>
            <a href="javascript:void(0)" onclick="switchTab('global-ranking', this)" class="tab-link <?php echo $is_guest ? 'hidden' : 'flex'; ?> items-center gap-3 px-6 py-4 text-gray-400 hover:text-gold-500 transition-all group <?php echo !$is_educator ? 'border-t border-white/5 mt-2' : ''; ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                <span class="text-sm font-medium">Ranking Anual</span>
            </a>
            
            <!-- Contextual Login/Logout -->
            <?php if ( $is_guest ) : ?>
                <a href="<?php echo site_url( '/login/' ); ?>" class="flex items-center gap-3 px-6 py-4 text-gold-500 hover:text-gold-400 transition-all group border-t border-white/5 mt-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    <span class="text-sm font-medium italic">Fazer Login de Elite</span>
                </a>
            <?php else : ?>
                <a href="<?php echo wp_logout_url(); ?>" class="flex items-center gap-3 px-6 py-4 text-red-500 hover:text-red-400 transition-all group border-t border-white/5 mt-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    <span class="text-sm font-medium">Sair com Segurança</span>
                </a>
            <?php endif; ?>

            <?php if ($is_educator): ?>
            <div class="px-6 py-6 border-t border-white/5 space-y-3 mt-4">
                <h5 class="text-[9px] text-zinc-500 uppercase tracking-widest font-bold">Link de Indicação</h5>
                <?php if ($is_educator && $has_access): ?>
                    <div id="referral-link-container" class="bg-black/50 p-3 rounded-lg border border-white/10 text-[9px] text-gold-400 font-mono break-all line-clamp-1 border-dashed">
                        <?php echo site_url('/?ref=' . $user_data->user_login); ?>
                    </div>
                    <button onclick="copyReferralLink(this)" class="w-full py-2 bg-white/5 hover:bg-white/10 border border-white/10 rounded-lg text-[9px] uppercase font-bold tracking-widest transition-all">Copiar Link</button>
                <?php else: ?>
                    <div class="bg-black/20 p-4 rounded-xl border border-white/5 text-center">
                        <span class="dashicons dashicons-lock text-zinc-600 text-sm mb-2"></span>
                        <p class="text-[8px] text-zinc-500 uppercase tracking-widest leading-none">Desbloqueie para Indicar</p>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </nav>


    </aside>

    <!-- Main Content -->
    <main class="flex-1 bg-black overflow-y-auto w-full">
        <header class="h-20 md:h-24 px-4 md:px-10 flex items-center justify-between border-b border-white/5 sticky top-0 bg-black/80 backdrop-blur-md z-40">
            <div class="flex items-center gap-4">
                <!-- Hamburger Button (Mobile) -->
                <button onclick="toggleSidebar()" class="lg:hidden w-10 h-10 flex items-center justify-center text-zinc-400 hover:text-gold-500 focus:outline-none transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                </button>
                <div class="flex flex-col">
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-semibold">Olá, <?php echo esc_html($user_data->display_name); ?>.</h2>
                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-widest bg-gold-500/10 text-gold-500 border border-gold-500/20">
                        <?php echo $is_educator ? 'Educadora' : 'Autoridade'; ?>
                    </span>
                </div>
                <!-- Benefit & Subscription Notification -->
                <div class="flex flex-col gap-1 mt-2">
                    <?php if ( ! $is_guest ) : ?>
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-gold-500 animate-pulse"></div>
                            <span class="text-[9px] text-zinc-500 uppercase tracking-widest">Benefício Elite Ativo: <span class="text-gold-500 font-bold"><?php echo Expressive_Referral::is_educator($user_id) ? '40%' : '30%'; ?> DESCONTO</span></span>
                        </div>
                        <?php 
                            $expiry_date = get_user_meta( $user_id, '_lms_elite_api_expiry', true );
                            if ( $expiry_date ) :
                                $expiry_formatted = date_i18n( 'd/m/Y', strtotime( $expiry_date ) );
                        ?>
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-blue-500 shadow-[0_0_8px_rgba(59,130,246,0.5)]"></div>
                            <span class="text-[9px] text-zinc-500 uppercase tracking-widest">Próxima Renovação: <span class="text-blue-400 font-bold"><?php echo $expiry_formatted; ?></span></span>
                        </div>
                        <?php endif; ?>
                    <?php else : ?>
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-zinc-700"></div>
                            <span class="text-[9px] text-zinc-500 uppercase tracking-widest italic">Acesso Restrito: Modo Visitante</span>
                        </div>
                        <a href="<?php echo site_url( '/login/' ); ?>" class="text-[8px] text-gold-500 font-bold uppercase tracking-widest hover:underline mt-1">Fazer Login para Desbloquear Benefícios</a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Profile and Info -->
            <div class="flex items-center gap-6">
                <div class="hidden md:flex flex-col items-end">
                    <span class="text-[8px] text-zinc-600 uppercase tracking-widest italic"><?php echo $current_date; ?></span>
                    <span class="text-[10px] text-white font-bold tracking-tighter"><?php echo $is_guest ? 'Convidado' : $rank_name; ?></span>
                </div>
                <div class="w-12 h-12 rounded-full border border-gold-500/30 p-1 group cursor-pointer" <?php echo $is_guest ? '' : 'onclick="toggleProfile()"'; ?>>
                    <div class="w-full h-full rounded-full overflow-hidden transition-all group-hover:opacity-70 flex items-center justify-center bg-zinc-900">
                        <?php if ( ! $is_guest ) : ?>
                            <?php echo Expressive_Core::get_elite_avatar($user_id, 48, 'w-full h-full object-cover current-user-avatar'); ?>
                        <?php else : ?>
                            <svg class="w-6 h-6 text-zinc-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </header>

        <div class="p-4 md:p-10 max-w-7xl mx-auto w-full">
            
            <!-- TAB: HOME -->
            <div id="tab-home" class="tab-content space-y-12">
                <!-- Hero Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="glass p-8 rounded-2xl border border-white/10 relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-gold-500/5 rounded-full blur-2xl group-hover:bg-gold-500/10 transition-all"></div>
                        <span class="text-xs text-gold-400 uppercase tracking-widest font-medium">Presença Pro</span>
                        <div class="text-3xl font-bold mt-2"><?php echo str_pad($total_lessons_watched, 2, '0', STR_PAD_LEFT); ?> <span class="text-xs font-light text-gray-500">/ <?php echo $total_lessons_platform; ?></span> <span class="text-[10px] text-zinc-600">AULAS</span></div>
                    </div>
                    <div class="glass p-8 rounded-2xl border border-white/10 relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-gold-500/5 rounded-full blur-2xl group-hover:bg-gold-500/10 transition-all"></div>
                        <span class="text-xs text-gold-400 uppercase tracking-widest font-medium">Horas de Treino</span>
                        <div class="text-3xl font-bold mt-2"><?php echo $total_training_hours; ?>h <span class="text-xs font-light text-gray-500 italic font-serif">concluídas</span></div>
                    </div>
                    <div class="glass p-8 rounded-2xl border border-white/10 relative overflow-hidden group flex flex-col justify-between">
                        <div>
                            <span class="text-xs text-gold-400 uppercase tracking-widest font-medium">Certificado de Elite</span>
                            <div class="mt-2">
                                <?php if($can_generate_certificate): ?>
                                    <div class="flex flex-col gap-3">
                                        <div class="flex items-center gap-2 text-gold-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            <span class="text-[9px] font-bold uppercase tracking-widest">Jornada 75%+ Concluída</span>
                                        </div>
                                        <a href="<?php echo home_url('/certificado-elite'); ?>" target="_blank" class="inline-block bg-gradient-to-r from-gold-600 to-gold-400 text-black px-6 py-3 rounded-xl text-[10px] font-bold uppercase tracking-widest shadow-lg shadow-gold-500/20 hover:scale-105 transition-all text-center">
                                            Gerar Certificado
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="flex items-center gap-3 text-zinc-600 grayscale">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                        <span class="text-[9px] font-bold uppercase tracking-widest text-zinc-500">Liberado aos 75%</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-white/5">
                            <span class="text-[10px] text-zinc-500 font-serif italic"><?php echo esc_html(get_user_meta($user_id, '_lms_rank_name', true) ?: 'Elite Bronze'); ?></span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-3 gap-10">
                    <div class="xl:col-span-3 space-y-6">
                        <div class="flex justify-between items-center">
                            <h3 class="text-xl font-bold font-serif italic text-gold-500">Destaques da Temporada</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php $courses = get_posts(array(
                                'post_type' => 'lms_course', 
                                'posts_per_page' => 3,
                                'meta_query' => $visibility_meta_query
                            )); 
                            foreach ($courses as $c): $thumb = get_the_post_thumbnail_url($c->ID, 'large'); ?>
                                <div onclick="switchTab('courses')" class="glass p-4 rounded-3xl border border-white/5 course-card transition-all cursor-pointer group">
                                    <div class="w-full aspect-4-5 bg-onyx rounded-2xl mb-5 overflow-hidden relative">
                                        <?php if ($thumb): ?><img src="<?php echo $thumb; ?>" class="w-full h-full object-cover"><?php endif; ?>
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
                                    </div>
                                    <h4 class="font-semibold"><?php echo esc_html($c->post_title); ?></h4>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB: TREINAMENTOS -->
            <div id="tab-courses" class="tab-content hidden space-y-8 animate-fade-in">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <h3 class="text-3xl font-bold font-serif italic text-gold-500">Meus Treinamentos</h3>
                        <p class="text-xs text-gray-500 mt-1">Acesse todo o conhecimento da nossa rede exclusiva.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php 
                    $all_courses = get_posts(array(
                        'post_type' => 'lms_course', 
                        'posts_per_page' => -1,
                        'meta_query' => $visibility_meta_query
                    ));
                    $cert_engine = new Expressive_Certificate();
                    foreach ($all_courses as $course): 
                        $thumb = get_the_post_thumbnail_url($course->ID, 'large');
                        
                        // Calculate specific course progress
                        $course_lessons = get_posts(array(
                            'post_type' => 'lms_lesson',
                            'meta_query' => array(array('key' => '_lms_course_id', 'value' => $course->ID)),
                            'posts_per_page' => -1,
                            'fields' => 'ids'
                        ));
                        $c_total = count($course_lessons);
                        $c_completed = 0;
                        if($c_total > 0) {
                            foreach($course_lessons as $cl_id) { if(in_array($cl_id, $completed_lesson_ids)) $c_completed++; }
                        }
                        $c_pct = $c_total > 0 ? round(($c_completed / $c_total) * 100) : 0;
                    ?>
                        <div onclick="window.location.href='<?php echo get_permalink($course->ID); ?>'" class="glass p-5 rounded-[32px] border border-white/5 course-card transition-all cursor-pointer group hover:bg-white/5 flex flex-col justify-between">
                            <div>
                                <div class="w-full aspect-4-5 bg-onyx rounded-2xl mb-6 overflow-hidden relative">
                                    <?php if ($thumb): ?>
                                        <img src="<?php echo esc_url($thumb); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-all duration-700">
                                    <?php endif; ?>
                                    <div class="absolute top-4 right-4 px-3 py-1 bg-black/60 backdrop-blur-md rounded-full text-[9px] font-bold uppercase tracking-widest text-gold-400 border border-white/10">Premium</div>
                                    
                                    <!-- Progress Overlay -->
                                    <div class="absolute bottom-4 left-4 right-4 h-1.5 bg-black/40 rounded-full overflow-hidden border border-white/5">
                                        <div class="h-full bg-gold-500" style="width: <?php echo $c_pct; ?>%"></div>
                                    </div>
                                </div>
                                <h4 class="font-serif italic text-xl text-white group-hover:text-gold-500 transition-colors px-2"><?php echo esc_html($course->post_title); ?></h4>
                                <p class="text-[9px] text-zinc-600 uppercase tracking-widest mt-2 px-2"><?php echo $c_pct; ?>% Concluído</p>
                            </div>

                            <div class="mt-8 flex flex-col gap-3 border-t border-white/5 pt-6">
                                <?php if($c_pct >= 75): ?>
                                    <div onclick="event.stopPropagation();">
                                        <?php echo $cert_engine->render_certificate_button( array( 'course_id' => $course->ID ) ); ?>
                                    </div>
                                <?php endif; ?>
                                <a href="<?php echo get_permalink($course->ID); ?>" class="w-full block text-center py-3 border border-white/10 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-white/5 transition-all">Continuar Treino</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- TAB: LIVES -->
            <div id="tab-lives" class="tab-content hidden space-y-8 animate-fade-in">
                <div>
                    <h3 class="text-3xl font-bold font-serif italic text-gold-500">Calendário de Lives</h3>
                    <p class="text-xs text-gray-500 mt-1">Mentorias e encontros ao vivo com foco em crescimento.</p>
                </div>
                <div class="grid grid-cols-1 gap-6 max-w-4xl">
                    <?php 
                    $lives = get_posts(array(
                        'post_type' => 'lms_live', 
                        'posts_per_page' => -1,
                        'meta_query' => $visibility_meta_query
                    ));
                    foreach ($lives as $live): 
                    ?>
                        <div class="glass p-8 rounded-3xl border border-white/5 flex flex-col md:flex-row items-center gap-8 group hover:border-gold-500/30 transition-all">
                            <div class="w-20 h-20 bg-onyx rounded-2xl flex flex-col items-center justify-center text-gold-400 border border-gold-500/20 group-hover:bg-gold-500 group-hover:text-black transition-all">
                                <span class="text-[10px] font-bold uppercase leading-none opacity-60">LIVE</span>
                                <span class="text-2xl font-bold">#</span>
                            </div>
                            <div class="flex-1 text-center md:text-left">
                                <h4 class="text-xl font-bold mb-1"><?php echo esc_html($live->post_title); ?></h4>
                                <?php $prof = get_post_meta($live->ID, '_lms_professor_name', true); ?>
                                <?php if($prof): ?>
                                    <div class="text-gold-500/80 text-[10px] font-bold uppercase tracking-widest mb-3 italic">Prof: <?php echo esc_html($prof); ?></div>
                                <?php endif; ?>
                                <div class="flex flex-wrap justify-center md:justify-start gap-4">
                                    <span class="text-[10px] text-gray-400 uppercase tracking-widest flex items-center gap-2">
                                        <svg class="w-3 h-3 text-gold-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        20:00 (Brasília)
                                    </span>
                                    <span class="text-[10px] text-gray-400 uppercase tracking-widest flex items-center gap-2">
                                        <svg class="w-3 h-3 text-gold-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                        Acesso Exclusivo
                                    </span>
                                </div>
                            </div>
                            <div class="w-full md:w-auto">
                                <a href="<?php echo get_permalink($live->ID); ?>" class="w-full md:w-auto block px-10 py-4 bg-white/5 border border-white/10 rounded-2xl text-[10px] font-bold uppercase tracking-widest text-gold-400 hover:bg-gold-500 hover:text-black hover:border-gold-500 transition-all">Entrar na Sala</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- TAB: RANKING (UNIFIED) -->
            <?php if ($is_educator): ?>
            <div id="tab-ranking" class="tab-content hidden space-y-10 animate-fade-in pb-20">
                <!-- Ranking Evolution Card -->
                <div class="glass p-10 rounded-[40px] border border-white/5 gold-glow overflow-hidden relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-gold-500/5 to-transparent pointer-events-none"></div>
                    
                    <div class="relative z-10">
                        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-end mb-12 gap-6">
                            <div>
                                <h3 class="text-4xl font-serif italic text-gold-500 mb-2">Roadmap to Elite</h3>
                                <p class="text-gray-400 text-sm leading-relaxed max-w-xl">Sua trajetória no Marco Zero é definida pelo impacto que você gera. Cada nova indicação soma 10% de comissão vitalícia para você e acelera sua ascensão ao topo do ranking.</p>
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
                        <div class="relative py-12 px-2 mb-8">
                            <!-- Horizontal Track (Only Desktop/Large Tablet) -->
                            <div class="hidden lg:block absolute top-1/2 left-0 right-0 h-1 bg-white/5 -translate-y-1/2 rounded-full">
                                <div class="h-full shadow-[0_0_15px_currentColor] transition-all duration-1000" style="width: <?php echo (($current_level - 1) / 5) * 100 + ($progress_towards_next / 5); ?>%; background-color: <?php echo $ranks[$current_level]['color']; ?>; color: <?php echo $ranks[$current_level]['color']; ?>;"></div>
                            </div>

                            <!-- Nodes -->
                            <div class="relative grid grid-cols-3 place-items-center lg:flex lg:justify-between w-full gap-x-2 gap-y-12 px-2">
                                <?php foreach($ranks as $lv => $r): 
                                    $is_past = $lv < $current_level;
                                    $is_current = $lv == $current_level;
                                ?>
                                <div class="flex flex-col items-center group relative">
                                    <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-full border-2 transition-all duration-500 flex items-center justify-center z-10 bg-black
                                        <?php echo $is_past ? 'text-black shadow-lg' : ($is_current ? 'scale-125 gold-glow shadow-[0_0_20px_rgba(212,175,55,0.4)]' : 'border-white/10 text-white/20'); ?>"
                                        style="<?php echo $is_past ? 'background-color: '.$r['color'].'; border-color: '.$r['color'] : ($is_current ? 'border-color: '.$r['color'].'; color: '.$r['color'] : ''); ?>">
                                        <?php if($is_past): ?><span class="dashicons dashicons-yes text-sm"></span><?php else: echo $r['icon']; endif; ?>
                                    </div>
                                    <div class="absolute mt-14 lg:mt-16 flex flex-col items-center whitespace-nowrap opacity-40 group-hover:opacity-100 <?php echo $is_current ? 'opacity-100' : ''; ?> transition-opacity pointer-events-none">
                                        <span class="text-[8px] lg:text-[9px] font-bold uppercase tracking-[0.1em]" style="color: <?php echo $is_current ? $r['color'] : '#fff'; ?>"><?php echo $r['name']; ?></span>
                                        <span class="text-[7px] text-zinc-600 mt-0.5 tracking-tighter"><?php echo $r['min']; ?>+ Indicações</span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="flex flex-col gap-10 mt-12 bg-white/[0.02] p-8 rounded-2xl border border-white/5">
                            <!-- Top: Progress Bar -->
                            <div class="w-full">
                                 <div class="flex justify-between text-xs font-bold uppercase tracking-widest mb-2">
                                    <span class="text-white">Progresso para <?php echo $ranks[$next_lv]['name']; ?></span>
                                    <span style="color: <?php echo $ranks[$next_lv]['color']; ?>"><?php echo round($progress_towards_next); ?>%</span>
                                </div>
                                <div class="w-full h-2 bg-white/5 rounded-full overflow-hidden">
                                    <div class="h-full transition-all duration-1000" style="width: <?php echo $progress_towards_next; ?>%; background-color: <?php echo $ranks[$next_lv]['color']; ?>;"></div>
                                </div>
                                <p class="text-[10px] text-zinc-500 mt-3 uppercase tracking-widest leading-relaxed">
                                    <?php if($current_level < 6): ?>
                                        Faltam <strong><?php echo $needed_for_next; ?> indicações</strong> para o próximo nível de bonificação.
                                    <?php else: ?>
                                        Você atingiu o status máximo de Elite. Parabéns pela liderança.
                                    <?php endif; ?>
                                </p>
                            </div>

                            <!-- Bottom: Stats Cards (2x2 Grid) -->
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-black/40 px-5 py-6 rounded-xl border border-white/5 flex flex-row items-center justify-between">
                                    <span class="text-[9px] lg:text-[10px] text-zinc-500 uppercase tracking-widest text-left">Indicações</span>
                                    <div class="text-xl lg:text-2xl font-bold text-white"><?php echo $ref_count; ?></div>
                                </div>
                                <div class="bg-black/40 px-5 py-6 rounded-xl border border-gold-500/10 relative overflow-hidden group flex flex-row items-center justify-between">
                                    <div class="absolute inset-0 bg-gold-500/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                    <span class="text-[9px] lg:text-[10px] text-zinc-500 uppercase tracking-widest text-left relative z-10">Lugar Global</span>
                                    <div class="text-xl lg:text-2xl font-black text-gold-500 relative z-10">
                                        <?php echo $my_global_rank > 0 ? '#' . $my_global_rank : '--'; ?>
                                    </div>
                                </div>
                                <?php if ( $show_commissions === 'yes' ) : ?>
                                 <div class="bg-black/40 px-5 py-6 rounded-xl border border-white/5 flex flex-row items-center justify-between">
                                    <span class="text-[9px] lg:text-[10px] text-zinc-500 uppercase tracking-widest text-left">Total em Vendas</span>
                                    <div class="text-lg lg:text-xl font-bold text-white flex items-center gap-1">
                                        <span class="opacity-40 text-[10px]">R$</span>
                                        <span><?php echo number_format($total_revenue, 2, ',', '.'); ?></span>
                                    </div>
                                </div>
                                <div class="bg-gold-500/10 px-5 py-6 rounded-xl border border-gold-500/30 shadow-lg shadow-gold-500/10 flex flex-row items-center justify-between">
                                    <span class="text-[9px] lg:text-[10px] text-gold-500 uppercase tracking-widest font-bold text-left">Comissões Totais</span>
                                    <div class="text-lg lg:text-xl font-black text-gold-500 flex items-center gap-1">
                                        <span class="opacity-40 text-[10px]">R$</span>
                                        <span><?php echo number_format($total_commissions, 2, ',', '.'); ?></span>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Leadership Directory -->
                <div class="glass p-10 rounded-[40px] border border-white/5 gold-glow">

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
                                    <th class="py-4 px-2">Membro Conectado</th>
                                    <th class="py-4 px-2 text-center">Papel</th>
                                    <th class="py-4 px-2 text-center">Progresso (Insights)</th>
                                    <?php if ( $show_commissions === 'yes' ) : ?>
                                        <th class="py-4 px-2 text-center">Venda (R$)</th>
                                        <th class="py-4 px-2 text-center">Comissão (R$)</th>
                                    <?php endif; ?>
                                    <th class="py-4 px-2 text-center">Data</th>
                                    <th class="py-4 px-2 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <?php if($authorities_data): foreach($authorities_data as $ref): 
                                    $auth_user = get_userdata($ref->authority_id);
                                    if(!$auth_user) continue;
                                    $completed_auth = get_user_meta($ref->authority_id, '_lms_completed_lessons', true) ?: [];
                                    $progress_auth = $total_lessons_platform > 0 ? round((count($completed_auth) / $total_lessons_platform) * 100) : 0;
                                ?>
                                <tr class="group hover:bg-white/[0.02] transition-all">
                                    <td class="py-5 px-2 flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-full border border-gold-500/20 p-0.5 overflow-hidden">
                                            <?php echo Expressive_Core::get_elite_avatar($ref->authority_id, 40, 'rounded-full'); ?>
                                        </div>
                                        <div>
                                            <div class="text-sm font-semibold text-white"><?php echo esc_html($auth_user->display_name); ?></div>
                                            <div class="text-[9px] text-zinc-600 uppercase tracking-wider"><?php echo esc_html($auth_user->user_email); ?></div>
                                        </div>
                                    </td>
                                    <td class="py-5 px-2 text-center">
                                        <?php 
                                        $role = $ref->referred_role ?: 'autoridade'; 
                                        $role_color = ($role === 'educadora') ? 'bg-gold-500/10 text-gold-500 border-gold-500/20' : 'bg-purple-500/10 text-purple-500 border-purple-500/20';
                                        ?>
                                        <span class="px-2 py-0.5 border <?php echo $role_color; ?> text-[7px] font-bold uppercase rounded-full tracking-widest"><?php echo $role; ?></span>
                                    </td>
                                    <td class="py-5 px-2">
                                        <div class="w-48 mx-auto">
                                            <div class="flex justify-between text-[8px] uppercase tracking-widest mb-1 text-zinc-500">
                                                <span>Domínio do Curso</span>
                                                <span><?php echo $progress_auth; ?>%</span>
                                            </div>
                                            <div class="w-full h-1 bg-white/5 rounded-full overflow-hidden">
                                                <div class="h-full bg-gold-600 transition-all duration-1000" style="width: <?php echo $progress_auth; ?>%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <?php if ( $show_commissions === 'yes' ) : ?>
                                        <td class="py-5 px-2 text-center text-xs font-bold text-white">
                                            <?php echo $ref->order_total > 0 ? 'R$ ' . number_format($ref->order_total, 2, ',', '.') : '--'; ?>
                                        </td>
                                        <td class="py-5 px-2 text-center text-xs font-black text-gold-500">
                                            <?php echo $ref->commission_amount > 0 ? 'R$ ' . number_format($ref->commission_amount, 2, ',', '.') : '--'; ?>
                                        </td>
                                    <?php endif; ?>
                                    <td class="py-5 px-2 text-center text-[10px] text-zinc-500">
                                        <?php echo date('d/m/y', strtotime($ref->created_at)); ?>
                                    </td>
                                    <td class="py-5 px-2 text-right">
                                        <span class="px-3 py-1 bg-green-500/10 text-green-500 border border-green-500/20 text-[8px] font-bold uppercase rounded-full">Ativo</span>
                                    </td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr><td colspan="<?php echo $show_commissions === 'yes' ? 7 : 5; ?>" class="py-10 text-center text-xs text-zinc-600 italic">Nenhuma autoridade conectada à sua rede ainda.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- TAB: RANKING GLOBAL -->
            <div id="tab-global-ranking" class="tab-content hidden space-y-10 animate-fade-in pb-20">
                <?php if ( ! $is_educator ) : 
                    $educator_upgrade_url = get_option( 'lms_educator_upgrade_link', '#' );
                ?>
                <div class="bg-gold-500/5 hover:bg-gold-500/10 border border-gold-500/20 p-8 rounded-[40px] flex flex-col md:flex-row items-center justify-between gap-6 relative overflow-hidden transition-all shadow-2xl">
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-gold-500/10 rounded-full blur-3xl"></div>
                    <div class="relative z-10 w-full md:w-2/3">
                        <h4 class="text-2xl font-bold text-gold-400 font-serif italic mb-2">Quer fazer parte da Elite?</h4>
                        <p class="text-sm text-gray-300">A aba "Minha Rede", os links de indicações comissionados e as disputas no Pódio são recursos exclusivos das <strong>Educadoras</strong>. Construa hoje o seu exército de autoridades!</p>
                    </div>
                    <a href="<?php echo esc_url( $educator_upgrade_url ); ?>" class="relative z-10 px-8 py-4 bg-gold-500 text-black rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-white transition-all text-center shrink-0 w-full md:w-auto">Quero Ser Educadora</a>
                </div>
                <?php endif; ?>
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <h3 class="text-3xl font-bold font-serif italic text-gold-500">🏆 Ranking Anual Elite</h3>
                        <p class="text-xs text-gray-500 mt-1">Os maiores impulsionadores da rede no ano de <?php echo date('Y'); ?>.</p>
                    </div>
                </div>

                <!-- Podium (Top 3) -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-end max-w-5xl mx-auto py-10">
                    <?php 
                    $podium_count = 0;
                    foreach(array_slice($ranking_data, 0, 3) as $index => $row): 
                        $podium_count++;
                        $user_info = get_userdata($row->educator_id);
                        if(!$user_info) continue;
                        $rank_level = get_user_meta($row->educator_id, '_lms_rank_level', true) ?: 1;
                        
                        // Podium UI variations
                        $order_class = ($index == 0) ? 'order-2 scale-110 z-10' : (($index == 1) ? 'order-1' : 'order-3');
                        $medal_color = ($index == 0) ? 'text-gold-500' : (($index == 1) ? 'text-zinc-400' : 'text-orange-600');
                        $card_border = ($index == 0) ? 'border-gold-500/40 shadow-gold-500/20' : 'border-white/10';
                    ?>
                    <div class="glass p-8 rounded-[40px] border flex flex-col items-center text-center transition-all hover:scale-[1.02] <?php echo $order_class; ?> <?php echo $card_border; ?> shadow-2xl">
                        <div class="relative mb-6">
                            <div class="w-24 h-24 rounded-full border-2 p-1 <?php echo ($index == 0) ? 'border-gold-500' : 'border-white/20'; ?>">
                                <div class="w-full h-full rounded-full overflow-hidden">
                                    <?php echo Expressive_Core::get_elite_avatar($row->educator_id, 96, 'w-full h-full object-cover'); ?>
                                </div>
                            </div>
                            <div class="absolute -bottom-2 -right-2 w-10 h-10 bg-black border border-white/10 rounded-full flex items-center justify-center text-xl <?php echo $medal_color; ?> font-bold shadow-lg">
                                <?php echo ($index == 0) ? '🥇' : (($index == 1) ? '🥈' : '🥉'); ?>
                            </div>
                        </div>
                        <h4 class="text-lg font-serif italic font-bold text-white line-clamp-1"><?php echo esc_html($user_info->display_name); ?></h4>
                        <div class="text-[9px] text-zinc-500 uppercase tracking-[0.2em] mb-4">Nível <?php echo $rank_level; ?></div>
                        
                        <div class="bg-white/5 px-6 py-2 rounded-full border border-white/5">
                            <span class="text-2xl font-black text-gold-500"><?php echo $row->ref_count; ?></span>
                            <span class="text-[9px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Indicações</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Global Table (The rest) -->
                <div class="glass p-10 rounded-[40px] border border-white/5 gold-glow max-w-5xl mx-auto">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-separate border-spacing-y-4">
                            <thead>
                                <tr class="text-[9px] uppercase tracking-[0.2em] text-zinc-500">
                                    <th class="px-6 pb-2">Pos.</th>
                                    <th class="px-6 pb-2">Educador</th>
                                    <th class="px-6 pb-2 text-center">Nível</th>
                                    <th class="px-6 pb-2 text-right">Indicações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                foreach(array_slice($ranking_data, 0, 5) as $index => $row): 
                                    $position = $index + 1;
                                    $user_info = get_userdata($row->educator_id);
                                    if(!$user_info) continue;
                                    $is_me = ($user_info->ID == get_current_user_id());
                                    $rank_name = get_user_meta($row->educator_id, '_lms_rank_name', true) ?: 'Bronze';
                                ?>
                                <tr class="group transition-all <?php echo $is_me ? 'bg-gold-500/10 border-2 border-gold-500/50' : 'hover:bg-white/5'; ?> rounded-2xl">
                                    <td class="px-6 py-4 first:rounded-l-2xl">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-bold <?php echo $is_me ? 'text-gold-500' : 'text-zinc-600'; ?>">#<?php echo $position; ?></span>
                                            <?php if($is_me): ?>
                                                <span class="text-[7px] bg-gold-500 text-black px-1.5 py-0.5 rounded font-black tracking-tighter">VOCÊ</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-full overflow-hidden border <?php echo $is_me ? 'border-gold-500' : 'border-white/10'; ?> group-hover:border-gold-500/30 transition-all">
                                            <?php echo Expressive_Core::get_elite_avatar($row->educator_id, 40, 'w-full h-full object-cover'); ?>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-semibold <?php echo $is_me ? 'text-gold-500' : 'text-white'; ?>"><?php echo esc_html($user_info->display_name); ?></span>
                                            <span class="text-[9px] text-zinc-600 uppercase tracking-widest"><?php echo $is_me ? 'Sua Posição' : 'Top Educador'; ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-[10px] font-bold uppercase tracking-widest text-zinc-400"><?php echo esc_html($rank_name); ?></span>
                                    </td>
                                    <td class="px-6 py-4 text-right last:rounded-r-2xl">
                                        <span class="text-lg font-black text-white group-hover:text-gold-500 transition-all"><?php echo $row->ref_count; ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <div id="ranking-expand-btn" class="mt-8 text-center">
                            <button onclick="toggleRankingModal()" class="px-8 py-3 bg-white/5 border border-white/10 rounded-xl text-[10px] font-bold uppercase tracking-widest text-zinc-400 hover:bg-gold-500 hover:text-black hover:border-gold-500 transition-all shadow-lg hover:shadow-gold-500/10">
                                Ver Ranking Completo
                            </button>
                        </div>
                        <?php if (empty($ranking_data)): ?>
                            <div class="text-center py-20 bg-white/[0.02] rounded-3xl border border-dashed border-white/10">
                                <div class="w-20 h-20 bg-gold-500/10 rounded-full flex items-center justify-center mx-auto mb-6">
                                    <svg class="w-10 h-10 text-gold-500/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                </div>
                                <h4 class="text-xl font-serif italic text-gold-500 mb-2">O Pódio Espera por Você!</h4>
                                <p class="text-gray-500 text-sm max-w-xs mx-auto mb-8">Ninguém inaugurou o pódio de <?php echo date('Y'); ?> ainda. Seja o primeiro a conectar autoridades e domine o topo!</p>
                                <a href="javascript:void(0)" onclick="switchTab('referral', this)" class="inline-block px-8 py-3 bg-gold-500 text-black text-[10px] font-bold uppercase tracking-widest rounded-xl hover:scale-105 transition-all">Começar Agora</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- Ranking Full Modal Overlay -->
    <div id="ranking-modal" class="fixed inset-0 bg-black/80 backdrop-blur-xl z-50 hidden flex items-center justify-center p-4 md:p-10 transition-all duration-500 opacity-0 overlay-hidden">
        <div class="glass w-full max-w-4xl bg-onyx rounded-[40px] overflow-hidden border border-gold-500/20 shadow-2xl relative animate-modal-in flex flex-col max-h-[90vh]">
            <!-- Header Section -->
            <div class="p-8 md:p-12 border-b border-white/5 bg-gradient-to-br from-gold-600/10 to-transparent relative shrink-0">
                <button onclick="toggleRankingModal()" class="absolute top-8 right-8 w-10 h-10 bg-white/5 rounded-full flex items-center justify-center text-white hover:bg-white/10 transition-all z-30">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
                
                <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 relative z-10">
                    <div>
                        <h3 class="text-3xl md:text-4xl font-serif italic text-gold-500 mb-2">Elite Global Leaderboard</h3>
                        <p class="text-xs text-gray-400 uppercase tracking-[0.3em]">Temporada <?php echo date('Y'); ?></p>
                    </div>
                    
                    <div class="flex gap-4 w-full md:w-auto">
                        <div class="flex-1 md:flex-none px-6 py-4 bg-black/40 rounded-2xl border border-white/5 flex flex-col items-center">
                            <span class="text-[8px] text-zinc-500 uppercase tracking-widest mb-1">Membros Ativos</span>
                            <span class="text-xl font-bold text-white"><?php echo str_pad(count($ranking_data), 2, '0', STR_PAD_LEFT); ?></span>
                        </div>
                        <div class="flex-1 md:flex-none px-6 py-4 bg-gold-500/10 rounded-2xl border border-gold-500/30 flex flex-col items-center">
                            <span class="text-[8px] text-gold-500 uppercase tracking-widest mb-1">Seu Lugar</span>
                            <span class="text-xl font-black text-gold-500"><?php echo $my_global_rank > 0 ? '#' . $my_global_rank : '--'; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- List Body (Scrollable) -->
            <div class="flex-1 overflow-y-auto p-6 md:p-12 scroll-custom">
                <table class="w-full text-left border-separate border-spacing-y-3">
                    <thead>
                        <tr class="text-[8px] uppercase tracking-[0.2em] text-zinc-500">
                            <th class="px-6 pb-2">Posição</th>
                            <th class="px-6 pb-2">Educador</th>
                            <th class="px-6 pb-2 text-center">Renda/Meta</th>
                            <th class="px-6 pb-2 text-right">Indicações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        foreach($ranking_data as $index => $row): 
                            $position = $index + 1;
                            $user_info = get_userdata($row->educator_id);
                            if(!$user_info) continue;
                            $is_me = ($user_info->ID == get_current_user_id());
                            $r_name = get_user_meta($row->educator_id, '_lms_rank_name', true) ?: 'Bronze';
                        ?>
                        <tr class="group transition-all <?php echo $is_me ? 'bg-gold-500/10 border border-gold-500/50' : 'hover:bg-white/5'; ?> rounded-2xl">
                            <td class="px-6 py-4 first:rounded-l-2xl">
                                <span class="text-sm font-bold <?php echo $is_me ? 'text-gold-500' : 'text-zinc-500'; ?>">#<?php echo $position; ?></span>
                            </td>
                            <td class="px-6 py-4 flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full overflow-hidden border <?php echo $is_me ? 'border-gold-500' : 'border-white/10'; ?>">
                                    <?php echo Expressive_Core::get_elite_avatar($row->educator_id, 40, 'w-full h-full object-cover'); ?>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-semibold <?php echo $is_me ? 'text-gold-500' : 'text-white'; ?>"><?php echo esc_html($user_info->display_name); ?></span>
                                    <span class="text-[9px] text-zinc-600 uppercase tracking-widest italic"><?php echo $is_me ? 'Elite / Orgânica' : 'Membro Elite'; ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 bg-white/5 rounded-full text-[9px] font-bold text-zinc-400 uppercase tracking-widest border border-white/5"><?php echo esc_html($r_name); ?></span>
                            </td>
                            <td class="px-6 py-4 text-right last:rounded-r-2xl">
                                <span class="text-lg font-black text-white"><?php echo $row->ref_count; ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Footer Section -->
            <div class="px-12 py-6 border-t border-white/5 bg-black/40 text-center shrink-0">
                <p class="text-[10px] text-zinc-600 uppercase tracking-widest italic font-serif">Marco Zero - Sistema de Gratificações Vitalícias</p>
            </div>
        </div>
    </div>

    <!-- Profile Modal Overlay -->
    <div id="profile-modal" class="fixed inset-0 bg-black/80 backdrop-blur-xl z-50 hidden flex items-center justify-center p-6 transition-all duration-500 opacity-0 overlay-hidden">
        <div class="glass w-full max-w-md bg-onyx rounded-[40px] overflow-hidden border border-gold-500/20 shadow-2xl relative animate-modal-in">
            <!-- Top Decoration -->
            <div class="h-32 bg-gradient-to-br from-gold-600 to-gold-400 relative">
                 <button onclick="toggleProfile()" class="absolute top-6 right-6 w-10 h-10 bg-black/20 rounded-full flex items-center justify-center text-white hover:bg-black/40 transition-all">
                    <svg class="w-6 h-6 font-bold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                 </button>
            </div>
            
            <div class="px-10 pb-12 text-center -mt-16 relative z-20">
                <!-- Avatar Large with Upload Overlay -->
                <div class="relative w-32 h-32 mx-auto mb-2 group cursor-pointer" onclick="document.getElementById('elite-avatar-input').click()">
                    <div id="profile-avatar-display" class="w-32 h-32 rounded-full border-4 border-onyx bg-onyx overflow-hidden shadow-xl transition-all duration-500 group-hover:opacity-50">
                        <?php echo Expressive_Core::get_elite_avatar($user_id, 128, 'w-full h-full object-cover current-user-avatar'); ?>
                    </div>
                    <!-- Camera Overlay -->
                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all">
                        <svg class="w-8 h-8 text-gold-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </div>
                    <!-- Hidden Input -->
                    <input type="file" id="elite-avatar-input" class="hidden" accept="image/*" onchange="handleAvatarUpload(this)">
                </div>

                <!-- Remove Avatar Button -->
                <button onclick="handleRemoveAvatar()" class="mb-6 text-[10px] text-gray-500 hover:text-red-400 uppercase tracking-widest transition-colors">Remover Foto</button>
                
                <h3 class="text-2xl font-serif italic text-gold-500 font-bold mb-1"><?php echo esc_html($user_data->display_name); ?></h3>
                <p class="text-[10px] text-gray-500 uppercase tracking-widest mb-6"><?php echo esc_html($user_data->user_email); ?></p>
                
                <div class="mb-12">
                    <span class="inline-block px-10 py-4 bg-gold-400/10 border border-gold-500/40 rounded-3xl text-sm font-black uppercase tracking-[0.2em] text-gold-500 shadow-xl shadow-gold-500/10 scale-110">
                        <?php echo esc_html(get_user_meta($user_id, '_lms_rank_name', true) ?: 'Elite Bronze'); ?>
                    </span>
                </div>

                <div class="space-y-3">
                    <a href="<?php echo admin_url('profile.php'); ?>" class="w-full block py-4 bg-white/10 rounded-2xl text-[10px] font-bold uppercase tracking-widest hover:bg-white/15 transition-all">Editar Perfil</a>
                    <a href="<?php echo wp_logout_url(); ?>" class="w-full block py-4 bg-red-500/10 rounded-2xl text-[10px] font-bold uppercase tracking-widest text-red-500 hover:bg-red-500/20 transition-all">Desconectar</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Ensure lms_vars is available for the upload system
        window.lms_vars = window.lms_vars || {
            ajax_url: '<?php echo admin_url("admin-ajax.php"); ?>',
            nonce: '<?php echo wp_create_nonce("lms_engine_nonce"); ?>',
            user_id: '<?php echo $user_id; ?>'
        };

        function toggleProfile() {
            const modal = document.getElementById('profile-modal');
            if (modal.classList.contains('hidden')) {
                modal.classList.remove('hidden', 'opacity-0');
                modal.classList.add('opacity-100');
                document.body.style.overflow = 'hidden';
            } else {
                modal.classList.remove('opacity-100');
                modal.classList.add('opacity-0');
                setTimeout(() => {
                    modal.classList.add('hidden');
                    document.body.style.overflow = '';
                }, 300);
            }
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('elite-sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const isHidden = !sidebar.classList.contains('translate-x-0');

            if (isHidden) {
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                overlay.classList.remove('hidden');
                setTimeout(() => overlay.classList.add('opacity-100'), 10);
                document.body.style.overflow = 'hidden';
            } else {
                sidebar.classList.remove('translate-x-0');
                sidebar.classList.add('-translate-x-full');
                overlay.classList.remove('opacity-100');
                setTimeout(() => overlay.classList.add('hidden'), 300);
                document.body.style.overflow = '';
            }
        }

        // Close on escape
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const modal = document.getElementById('profile-modal');
                if (!modal.classList.contains('hidden')) toggleProfile();
                const sidebar = document.getElementById('elite-sidebar');
                if (sidebar.classList.contains('translate-x-0')) toggleSidebar();
            }
        });

        function switchTab(tabId, el) {
            // Close sidebar on mobile if open
            if (window.innerWidth < 1024) {
                const sidebar = document.getElementById('elite-sidebar');
                if (sidebar.classList.contains('translate-x-0')) {
                    toggleSidebar();
                }
            }

            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.add('hidden');
                tab.style.setProperty('display', 'none', 'important');
                tab.style.opacity = '0';
            });

            // Show target tab
            const target = document.getElementById('tab-' + tabId);
            if (target) {
                target.classList.remove('hidden');
                target.style.setProperty('display', 'block', 'important');
                target.style.opacity = '1';
                // Trigger animation reset
                target.style.animation = 'none';
                target.offsetHeight; // trigger reflow
                target.style.animation = null;
            }
            
            // Update sidebar links
            document.querySelectorAll('.tab-link').forEach(link => {
                link.classList.remove('sidebar-active', 'text-gold-400');
                link.classList.add('text-gray-400');
            });

            if (el) {
                el.classList.add('sidebar-active', 'text-gold-400');
                el.classList.remove('text-gray-400');
            } else {
                // Find link by tabId if no element provided (for URL based switching)
                const link = document.querySelector(`[onclick*="switchTab('${tabId}')"]`);
                if (link) {
                    link.classList.add('sidebar-active', 'text-gold-400');
                    link.classList.remove('text-gray-400');
                }
            }
        }

        // Handle initial tab from URL
        window.addEventListener('DOMContentLoaded', () => {
            const params = new URLSearchParams(window.location.search);
            const tab = params.get('tab');
            if (tab && document.getElementById('tab-' + tab)) {
                switchTab(tab);
            }
        });

        function copyReferralLink(btn) {
            const link = "<?php echo site_url('/?ref=' . $user_data->user_login); ?>";
            const originalText = btn.innerText;
            
            const handleSuccess = () => {
                btn.innerText = "Copiado!";
                btn.classList.add('bg-gold-500', 'text-black');
                setTimeout(() => {
                    btn.innerText = originalText;
                    btn.classList.remove('bg-gold-500', 'text-black');
                }, 2000);
            };

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(link).then(handleSuccess).catch(err => console.error('Erro ao copiar: ', err));
            } else {
                // Fallback for HTTP environments (like .local testing)
                const textArea = document.createElement("textarea");
                textArea.value = link;
                textArea.style.position = "absolute";
                textArea.style.opacity = "0";
                document.body.appendChild(textArea);
                textArea.select();
                try {
                    document.execCommand('copy');
                    handleSuccess();
                } catch (err) {
                    console.error('Fallback erro ao copiar: ', err);
                }
                document.body.removeChild(textArea);
            }
        }

        function handleAvatarUpload(input) {
            if (!input.files || !input.files[0]) return;

            const file = input.files[0];
            
            // Safety check for lms_vars (WP Script Localization)
            if (typeof lms_vars === 'undefined') {
                console.error('LMS Error: lms_vars is not defined. Ensure wp_head/wp_footer are loading.');
                alert('Erro interno do sistema (Nonce ausente). Recarregue a página.');
                return;
            }

            // Validate file type
            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                alert('Formato não suportado. Use JPG, PNG ou WebP.');
                input.value = '';
                return;
            }

            // Show loading state
            const avatarDisplay = document.getElementById('profile-avatar-display');
            avatarDisplay.style.opacity = '0.3';
            avatarDisplay.innerHTML = '<div class="w-full h-full flex items-center justify-center text-gold-500"><svg class="w-8 h-8 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg></div>';

            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const img = new Image();

            img.onload = function() {
                // Calculate Center Crop 1:1
                const size = Math.min(img.width, img.height);
                const sourceX = (img.width - size) / 2;
                const sourceY = (img.height - size) / 2;

                // Max Output size (512x512) for profile quality/performance balance
                const outSize = Math.min(size, 512);

                canvas.width = outSize;
                canvas.height = outSize;
                
                // Draw square crop
                ctx.drawImage(img, sourceX, sourceY, size, size, 0, 0, outSize, outSize);

                canvas.toBlob(function(blob) {
                    const formData = new FormData();
                    formData.append('action', 'lms_upload_avatar');
                    formData.append('nonce', lms_vars.nonce);
                    formData.append('avatar', blob, 'elite-profile-' + lms_vars.user_id + '.jpg');

                    fetch(lms_vars.ajax_url || '<?php echo admin_url("admin-ajax.php"); ?>', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.text().then(text => { // Catch plaintext errors like -1
                        try { return JSON.parse(text); } 
                        catch(e) { return { success: false, data: "Erro de servidor (Autenticação falhou)." }; }
                    }))
                    .then(data => {
                        if (data.success) {
                            const newUrl = data.data.url + '?t=' + new Date().getTime();
                            avatarDisplay.style.opacity = '1';
                            avatarDisplay.innerHTML = `<img src="${newUrl}" class="w-full h-full object-cover" alt="Avatar">`;

                            // Update all UI avatars belonging to this user
                            document.querySelectorAll('.user-' + lms_vars.user_id + '-avatar, .current-user-avatar').forEach(img_el => {
                                if (img_el.closest('#profile-avatar-display')) return;
                                img_el.src = newUrl;
                            });

                            avatarDisplay.style.boxShadow = '0 0 30px rgba(212,175,55,0.6)';
                            setTimeout(() => { avatarDisplay.style.boxShadow = ''; }, 2000);
                        } else {
                            alert(data.data || 'Erro ao processar imagem.');
                            avatarDisplay.style.opacity = '1';
                        }
                    })
                    .catch(err => {
                        console.error('Elite Upload Error:', err);
                        alert('Erro de conexão. Verifique sua internet.');
                        avatarDisplay.style.opacity = '1';
                    });
                }, 'image/jpeg', 0.82);
            };

            img.onerror = () => { alert('Erro ao carregar imagem.'); avatarDisplay.style.opacity = '1'; };
            img.src = URL.createObjectURL(file);
            input.value = '';
        }

        function handleRemoveAvatar() {
            if (!confirm('Deseja realmente remover sua foto de perfil?')) return;

            const avatarDisplay = document.getElementById('profile-avatar-display');
            avatarDisplay.style.opacity = '0.3';

            const formData = new FormData();
            formData.append('action', 'lms_remove_avatar');
            formData.append('nonce', lms_vars.nonce);

            fetch(lms_vars.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    avatarDisplay.style.opacity = '1';
                    avatarDisplay.innerHTML = data.data.html;

                    // Extract new SRC from returned HTML to update other avatars
                    const temp = document.createElement('div');
                    temp.innerHTML = data.data.html;
                    const newSrc = temp.querySelector('img').src;

                    // Update all UI avatars belonging to this user
                    document.querySelectorAll('.user-' + lms_vars.user_id + '-avatar, .current-user-avatar').forEach(img_el => {
                        if (img_el.closest('#profile-avatar-display')) return;
                        img_el.src = newSrc;
                    });
                } else {
                    alert(data.data || 'Erro ao remover foto.');
                    avatarDisplay.style.opacity = '1';
                }
            })
            .catch(err => {
                console.error('Elite Remove Error:', err);
                alert('Erro de conexão.');
                avatarDisplay.style.opacity = '1';
            });
        }

        function toggleGlobalRanking() {
            document.querySelectorAll('.ranking-extra').forEach(row => {
                row.classList.remove('hidden');
                row.style.animation = 'fadeIn 0.5s ease-out forwards';
            });
            document.getElementById('ranking-expand-btn').classList.add('hidden');
        }

        function toggleRankingModal() {
            const modal = document.getElementById('ranking-modal');
            if (modal.classList.contains('hidden')) {
                modal.classList.remove('hidden', 'opacity-0');
                modal.classList.add('opacity-100');
                document.body.style.overflow = 'hidden';
            } else {
                modal.classList.remove('opacity-100');
                modal.classList.add('opacity-0');
                setTimeout(() => {
                    modal.classList.add('hidden');
                    document.body.style.overflow = '';
                }, 300);
            }
        }
    </script>

    <style>
        .scroll-custom::-webkit-scrollbar { width: 6px; }
        .scroll-custom::-webkit-scrollbar-track { background: transparent; }
        .scroll-custom::-webkit-scrollbar-thumb { background: rgba(212, 175, 55, 0.2); border-radius: 10px; }
        .scroll-custom::-webkit-scrollbar-thumb:hover { background: rgba(212, 175, 55, 0.4); }
    </style>

    <style>
        .animate-fade-in { animation: fadeIn 0.4s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        
        .animate-modal-in { animation: modalIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
        @keyframes modalIn { from { opacity: 0; transform: scale(0.9) translateY(20px); } to { opacity: 1; transform: scale(1) translateY(0); } }

        /* Global scrollbar removal for dashboard content */
        * { scrollbar-width: none !important; -ms-overflow-style: none !important; }
        *::-webkit-scrollbar { display: none !important; width: 0 !important; height: 0 !important; background: transparent !important; }
        
        .custom-scrollbar::-webkit-scrollbar { display: none !important; }
    </style>
    <?php wp_footer(); ?>
    <?php include EXPRESSIVE_CORE_PATH . 'templates/parts/visitor-indicator.php'; ?>
</body>
</html>
