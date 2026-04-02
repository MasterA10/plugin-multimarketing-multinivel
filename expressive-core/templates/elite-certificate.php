<?php
/**
 * Template Name: Elite Certificate
 * 
 * High-end digital certificate for Elite LMS Specialists.
 */

if ( ! is_user_logged_in() ) {
    wp_redirect( site_url( '/login/' ) );
    exit;
}

$user_id = get_current_user_id();
$user_data = get_userdata( $user_id );

// Security Check: Progress must be >= 75%
$completed_lesson_ids = get_user_meta( $user_id, '_lms_completed_lessons', true ) ?: [];
$total_lessons_watched = count($completed_lesson_ids);

$all_lessons_query = get_posts( array( 
    'post_type' => 'lms_lesson', 
    'posts_per_page' => -1,
    'post_status' => 'publish'
) );
$total_lessons_platform = count($all_lessons_query);
$global_presence_pct = $total_lessons_platform > 0 ? ($total_lessons_watched / $total_lessons_platform) * 100 : 0;

if ($global_presence_pct < 75) {
    wp_die('Você ainda não atingiu o nível de maestria (75%) necessário para gerar este certificado de elite.');
}

$date_issued = date('d/m/Y');
$cert_id = 'ELITE-' . $user_id . '-' . date('Ymd');

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificado de Especialista Elite - <?php echo esc_html($user_data->display_name); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;700&family=Playfair+Display:ital,wght@0,700;1,700&display=swap" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none; }
            body { background: white !important; }
            .cert-container { border: none !important; box-shadow: none !important; }
        }
        body { background-color: #050505; color: #fff; font-family: 'Outfit', sans-serif; }
        .cert-border {
            border: 20px solid transparent;
            border-image: linear-gradient(to bottom right, #D4AF37, #F2D480, #AA8C2C) 1;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 15rem;
            opacity: 0.03;
            pointer-events: none;
            white-space: nowrap;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="no-print fixed top-6 left-6 z-50">
        <button onclick="window.print()" class="bg-gold-500 hover:bg-gold-400 text-black px-8 py-3 rounded-xl text-xs font-bold uppercase tracking-widest transition-all shadow-xl shadow-gold-500/20">
            Imprimir / Salvar PDF
        </button>
    </div>

    <!-- Certificate Container -->
    <div class="cert-container relative w-full max-w-5xl aspect-video bg-[#0a0a0a] cert-border p-20 flex flex-col items-center justify-between shadow-2xl relative overflow-hidden">
        
        <div class="watermark font-serif italic uppercase">Elite Specialist</div>

        <!-- Header -->
        <div class="text-center">
            <h2 class="text-gold-500 text-xs font-bold uppercase tracking-[0.5em] mb-4">Certificado de Especialização</h2>
            <div class="w-16 h-px bg-gold-500/30 mx-auto mb-8"></div>
            <h1 class="font-serif italic text-6xl text-white mb-2">Membro de Elite</h1>
            <p class="text-zinc-500 text-sm uppercase tracking-widest">Expressive Learning Platform</p>
        </div>

        <!-- Body -->
        <div class="text-center max-w-2xl">
            <p class="text-zinc-400 text-sm italic mb-6">Certificamos solenemente para os devidos fins que</p>
            <h3 class="text-4xl font-bold text-gold-400 mb-8 tracking-tight"><?php echo esc_html($user_data->display_name); ?></h3>
            <p class="text-zinc-300 text-sm leading-relaxed">
                Concluiu com excelência técnica o programa de treinamento estratégico de alto nível, 
                atingindo o patamar de <strong class="text-white">Especialista de Elite</strong> com presença global superior a 75% 
                em todos os módulos de domínios práticos e teóricos.
            </p>
        </div>

        <!-- Footer -->
        <div class="w-full flex justify-between items-end">
            <div class="text-left">
                <p class="text-[10px] text-zinc-600 uppercase tracking-widest mb-1">ID Autenticidade</p>
                <p class="text-xs font-mono text-gold-500/50"><?php echo $cert_id; ?></p>
            </div>
            
            <div class="text-center px-10">
                <div class="w-48 h-px bg-zinc-800 mb-4 mx-auto"></div>
                <p class="text-[10px] text-zinc-500 uppercase tracking-widest mb-1">Diretoria Acadêmica</p>
                <p class="font-serif italic text-gold-500">Elite Members Group</p>
            </div>

            <div class="text-right">
                <p class="text-[10px] text-zinc-600 uppercase tracking-widest mb-1">Data de Emissão</p>
                <p class="text-xs text-zinc-400 font-bold"><?php echo $date_issued; ?></p>
            </div>
        </div>

        <!-- Corner Decorations -->
        <div class="absolute top-10 left-10 w-10 h-10 border-t-2 border-l-2 border-gold-500/20"></div>
        <div class="absolute top-10 right-10 w-10 h-10 border-t-2 border-r-2 border-gold-500/20"></div>
        <div class="absolute bottom-10 left-10 w-10 h-10 border-b-2 border-l-2 border-gold-500/20"></div>
        <div class="absolute bottom-10 right-10 w-10 h-10 border-b-2 border-r-2 border-gold-500/20"></div>
    </div>

</body>
</html>
