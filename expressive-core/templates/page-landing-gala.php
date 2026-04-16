<?php
/**
 * Template Name: Elite Landing Page - Baile de Gala
 * Description: Luxury Gala/Event template for Elite LMS.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$post_id = get_the_ID();
$buttons = get_post_meta( $post_id, '_elite_lp_buttons', true ) ?: array();
$media   = get_post_meta( $post_id, '_elite_lp_media', true ) ?: array();

// Helper to render media
function render_elite_media($section_key, $media_data) {
    $config = isset($media_data[$section_key]) ? $media_data[$section_key] : array('mode' => 'single', 'ids' => array());
    $ids = $config['ids'];
    $mode = $config['mode'];

    if (empty($ids)) {
        return '<div class="w-full h-full bg-white/5 border border-white/10 rounded-[40px] flex items-center justify-center italic text-white/20 text-xs">Aguardando Mídia...</div>';
    }

    if ($mode === 'carousel' && count($ids) > 1) {
        $html = '<div class="swiper elite-swiper-' . $section_key . ' rounded-[40px] overflow-hidden border border-white/10 shadow-2xl w-full h-full">';
        $html .= '<div class="swiper-wrapper">';
        foreach ($ids as $id) {
            $url = wp_get_attachment_url($id);
            $html .= '<div class="swiper-slide"><img src="' . esc_url($url) . '" class="w-full h-full object-cover"></div>';
        }
        $html .= '</div><div class="swiper-pagination"></div><div class="swiper-button-next"></div><div class="swiper-button-prev"></div></div>';
        return $html;
    } else {
        $url = wp_get_attachment_url($ids[0]);
        return '<img src="' . esc_url($url) . '" class="w-full h-full object-cover rounded-[40px] border border-white/10 shadow-2xl">';
    }
}

// Helper to get button
function get_elite_button($section_key, $buttons, $default_label) {
    $label = isset($buttons[$section_key]['label']) && !empty($buttons[$section_key]['label']) ? $buttons[$section_key]['label'] : $default_label;
    $url = isset($buttons[$section_key]['url']) ? $buttons[$section_key]['url'] : '#';
    return array('label' => $label, 'url' => $url);
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php wp_title(); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,600;1,400&family=Inter:wght@200;400;600&family=Outfit:wght@300;600&display=swap" rel="stylesheet">
    <?php wp_head(); ?>
    <style>
        :root {
            --gold: #c5a059;
            --gold-light: #e2c275;
            --glass-bg: rgba(255, 255, 255, 0.02);
            --glass-border: rgba(255, 255, 255, 0.08);
        }
        body { background-color: #000; color: #fff; font-family: 'Inter', sans-serif; letter-spacing: -0.01em; scroll-behavior: smooth; }
        .serif { font-family: 'Cormorant Garamond', serif; }
        .outfit { font-family: 'Outfit', sans-serif; }
        .bg-wrapper { position: fixed; inset: 0; z-index: -1; background: #000; }
        .mesh { position: absolute; width: 100%; height: 100%; background: radial-gradient(circle at 10% 10%, rgba(197, 160, 89, 0.15) 0%, transparent 40%), radial-gradient(circle at 90% 80%, rgba(255, 255, 255, 0.08) 0%, transparent 40%); filter: blur(80px); }
        .noise { position: fixed; inset: 0; z-index: -1; opacity: 0.05; pointer-events: none; background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E"); }
        .glass { background: var(--glass-bg); backdrop-filter: blur(25px) saturate(180%); border: 1px solid var(--glass-border); box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.8); }
        .gold-gradient-text { background: linear-gradient(135deg, #c5a059 0%, #fff5d1 50%, #c5a059 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        
        /* Elite Mode: Hide theme interference */
        .site-header, .site-footer, #masthead, #colophon, .storefront-breadcrumb, .elementor-header, .elementor-footer {
            display: none !important;
        }
        html, body {
            overflow-x: hidden;
            width: 100%;
            margin: 0;
            padding: 0;
        }

        .swiper-button-next, .swiper-button-prev { color: var(--gold) !important; scale: 0.5; }
        .swiper-pagination-bullet-active { background: var(--gold) !important; }
        section { padding: 80px 0; position: relative; overflow: hidden; }
        .level-card { transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        .level-card:hover { transform: translateY(-10px) scale(1.02); border-color: var(--gold); background: rgba(197, 160, 89, 0.05); }
    </style>
</head>
<body>

    <div class="bg-wrapper"><div class="mesh"></div><div class="noise"></div></div>

    <!-- Navegação -->
    <nav class="fixed w-full z-50 py-3 md:py-6 px-4 md:px-24 flex justify-between items-center backdrop-blur-md bg-black/40 border-b border-white/5">
        <div class="flex items-center gap-4">
            <div class="w-8 h-8 md:w-10 md:h-10 border border-[#c5a059]/40 flex items-center justify-center rounded-sm">
                <span class="text-[8px] md:text-[10px] gold-gradient-text font-bold uppercase outfit">Gala</span>
            </div>
            <span class="text-[9px] tracking-[0.6em] font-light uppercase opacity-40 outfit hidden md:block">Gran Master Experience</span>
        </div>
        <?php $hero_btn = get_elite_button('hero', $buttons, 'Garanta sua Vaga'); ?>
        <a href="<?php echo esc_url($hero_btn['url']); ?>" class="text-[8px] md:text-[9px] uppercase tracking-[0.15em] md:tracking-[0.4em] font-bold bg-[#c5a059] text-black px-4 md:px-6 py-2 md:py-3 hover:bg-[#e2c275] transition-all rounded-sm shadow-lg">
            <?php echo esc_html($hero_btn['label']); ?>
        </a>
    </nav>

    <!-- HERO SECTION -->
    <section class="min-h-screen flex items-center pt-32">
        <div class="container mx-auto px-6 lg:px-24 text-center space-y-12">
            <div class="space-y-6">
                <div class="inline-flex items-center gap-4 px-6 py-2 glass rounded-full border-white/5 mx-auto">
                    <span class="text-[10px] uppercase tracking-[0.5em] font-semibold text-[#c5a059] outfit">Bem-vindas, Gran Masters!</span>
                </div>
                <h1 class="text-4xl sm:text-6xl md:text-8xl lg:text-[110px] serif leading-tight">
                    Jantar & Baile de Gala <br>
                    <span class="gold-gradient-text italic font-bold">"GRAN MASTER"</span>
                </h1>
                <p class="text-white/40 max-w-2xl mx-auto text-base md:text-xl font-light uppercase tracking-widest">
                    Uma experiência criada para elevar o seu brilho e celebrar suas conquistas.
                </p>
            </div>
            
            <div class="max-w-6xl mx-auto aspect-[21/9] rounded-[50px] overflow-hidden shadow-[0_0_100px_rgba(197,160,89,0.15)]">
                <?php echo render_elite_media('hero', $media); ?>
            </div>
        </div>
    </section>

    <!-- GLAMOUR & NETWORKING -->
    <section class="bg-white/[0.01]">
        <div class="container mx-auto px-6 lg:px-24 grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-24 items-center">
            <div class="order-2 lg:order-1 space-y-12 text-center lg:text-left">
                <div class="space-y-4">
                    <h2 class="text-4xl md:text-6xl serif italic gold-gradient-text">Noite de Glamour e Networking</h2>
                    <p class="text-white/60 font-light text-sm md:text-lg leading-relaxed italic">"Conecte-se com as mentes mais brilhantes e as influenciadoras de maior destaque no mercado da beleza."</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-4 border-l border-[#c5a059]/30 pl-8">
                        <h4 class="text-white font-bold uppercase tracking-widest text-xs">Reconhecimento</h4>
                        <p class="text-white/40 text-xs leading-relaxed">Seu talento e dedicação serão celebrados em uma cerimônia que promete ser inesquecível.</p>
                    </div>
                    <div class="space-y-4 border-l border-[#c5a059]/30 pl-8">
                        <h4 class="text-white font-bold uppercase tracking-widest text-xs">Luxo Único</h4>
                        <p class="text-white/40 text-xs leading-relaxed">Viva momentos de pura elegância em um ambiente exclusivo, digno de uma verdadeira Gran Master.</p>
                    </div>
                </div>
            </div>
            <div class="order-1 lg:order-2">
                <div class="aspect-square rounded-[100px] overflow-hidden shadow-2xl skew-y-3">
                    <?php echo render_elite_media('intro', $media); ?>
                </div>
            </div>
        </div>
    </section>

    <!-- OPORTUNIDADES ESTRATÉGICAS -->
    <section class="bg-black">
        <div class="container mx-auto px-6 lg:px-24 space-y-20">
            <div class="text-center space-y-4">
                <h2 class="text-4xl md:text-7xl serif italic gold-gradient-text">Oportunidades para Você</h2>
                <p class="text-white/30 max-w-2xl mx-auto font-light text-sm md:text-base">Participar do nosso Gala é um investimento estratégico na sua carreira e futuro.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php 
                $ops = [
                    ["Autoridade Premium", "Eleve sua imagem e destaque-se como referência no seu segmento."],
                    ["Networking com Líderes", "Crie conexões valiosas que podem abrir portas para novas parcerias."],
                    ["Exposição Oficial", "Ganhe visibilidade nos canais oficiais da Expressive Kollors (54k+)."],
                    ["Premiações Exclusivas", "Concorra a prêmios que reconheçam seu esforço e impulsionam seu sucesso."]
                ];
                foreach ($ops as $op) : ?>
                    <div class="glass p-10 border-white/5 space-y-6 hover:bg-[#c5a059]/5 transition-all group">
                        <div class="w-12 h-12 rounded-full border border-[#c5a059]/30 flex items-center justify-center text-[#c5a059] group-hover:bg-[#c5a059] group-hover:text-black transition-all">★</div>
                        <h4 class="text-white font-bold uppercase tracking-widest text-xs leading-tight"><?php echo $op[0]; ?></h4>
                        <p class="text-white/30 text-[11px] leading-relaxed"><?php echo $op[1]; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- DESAFIO GRAN MASTER (REGRAS) -->
    <section class="bg-white/[0.01]">
        <div class="container mx-auto px-6 lg:px-24">
            <div class="glass p-12 md:p-24 rounded-[60px] border-white/10 relative overflow-hidden bg-gradient-to-br from-white/[0.03] to-transparent">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                    <div class="space-y-10">
                        <div class="space-y-4">
                            <span class="text-[#c5a059] text-[10px] uppercase tracking-[0.5em] font-bold">O Desafio</span>
                            <h2 class="text-4xl md:text-6xl serif gold-gradient-text">A Regra de Ouro</h2>
                            <p class="text-white/60 font-light text-lg">"Quem tiver o maior número de inclusões, leva o maior prêmio!"</p>
                        </div>
                        
                        <div class="space-y-8">
                            <div class="flex gap-6 items-start">
                                <div class="w-10 h-10 rounded-xl bg-[#c5a059]/20 flex items-center justify-center shrink-0 text-[#c5a059]">01</div>
                                <div>
                                    <h4 class="text-white font-bold uppercase tracking-widest text-xs">Inclusão Mínima</h4>
                                    <p class="text-white/40 text-xs mt-1">10 inclusões de sucesso para iniciar no desafio oficial.</p>
                                </div>
                            </div>
                            <div class="flex gap-6 items-start">
                                <div class="w-10 h-10 rounded-xl bg-[#c5a059]/20 flex items-center justify-center shrink-0 text-[#c5a059]">02</div>
                                <div>
                                    <h4 class="text-white font-bold uppercase tracking-widest text-xs">Compra Obrigatória</h4>
                                    <p class="text-white/40 text-xs mt-1">Cada autoridade deverá adquirir 1 kit curinga com 3 tintas (Gold, Caramelo Dark e Coffee Dark).</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="relative">
                         <div class="aspect-video lg:aspect-square">
                            <?php echo render_elite_media('challenge', $media); ?>
                         </div>
                         <div class="absolute -bottom-8 -right-8 p-8 glass border-white/10 rounded-3xl bg-black/60 backdrop-blur-3xl max-w-xs">
                             <p class="text-[#c5a059] text-[10px] font-bold uppercase tracking-widest mb-2">Ponto de Venda</p>
                             <p class="text-white/80 text-xs font-light">As vendas e controle serão feitos via site <span class="text-white font-bold italic">CCP Academy PMU Beauty</span>.</p>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- NÍVEIS DE RECONHECIMENTO -->
    <section class="bg-black">
        <div class="container mx-auto px-6 lg:px-24 space-y-16">
            <div class="text-center space-y-4">
                <h2 class="text-4xl md:text-6xl serif italic gold-gradient-text">Seu Nível de Reconhecimento</h2>
                <p class="text-white/30 max-w-xl mx-auto font-light leading-relaxed">Cada conquista sua é um degrau a mais em direção ao topo!</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <?php 
                $levels = [
                    ["Diamante 💎", "50+ Inclusões", "O ápice da liderança e influência."],
                    ["Rubi", "40+ Inclusões", "Excelência e compromisso sólido."],
                    ["Ouro", "30+ Inclusões", "Destaque e colaboração ativa."],
                    ["Prata", "20+ Inclusões", "Destaque crescente no cenário."],
                    ["Bronze", "10+ Inclusões", "O ponto de partida da sua jornada."]
                ];
                foreach ($levels as $l) : ?>
                    <div class="glass p-8 border-white/5 space-y-4 text-center level-card">
                        <h4 class="text-[#c5a059] font-bold uppercase tracking-[0.2em] text-[10px]"><?php echo $l[0]; ?></h4>
                        <div class="py-2">
                             <span class="text-2xl md:text-3xl outfit font-medium text-white"><?php echo $l[1]; ?></span>
                        </div>
                        <p class="text-white/30 text-[9px] uppercase tracking-widest leading-relaxed"><?php echo $l[2]; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- PRÊMIOS QUE GERAM DESEJO -->
    <section class="bg-white/[0.01]">
        <div class="container mx-auto px-6 lg:px-24 space-y-20">
            <div class="text-center space-y-4">
                <span class="text-[#c5a059] text-[10px] uppercase tracking-[0.5em] font-bold">A Recompensa</span>
                <h2 class="text-4xl md:text-7xl serif gold-gradient-text">Prêmios que Geram Desejo</h2>
                <p class="text-white/30 max-w-xl mx-auto font-light italic">"Para as Gran Masters que ousam sonhar e alcançam o extraordinário!"</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="space-y-8 group">
                    <div class="aspect-[4/5] rounded-[40px] overflow-hidden grayscale group-hover:grayscale-0 transition-all duration-700 border border-white/5">
                        <?php echo render_elite_media('prize_iphone', $media); ?>
                    </div>
                    <div class="text-center space-y-2">
                        <h4 class="text-2xl md:text-3xl serif italic text-white/50 group-hover:text-[#c5a059] transition-all">🥇 iPhone 15 (128GB)</h4>
                        <p class="text-white/30 text-[10px] uppercase tracking-[0.3em]">Tecnologia de Alta Performance</p>
                    </div>
                </div>
                <div class="space-y-8 group translate-y-12">
                     <div class="aspect-[4/5] rounded-[40px] overflow-hidden grayscale group-hover:grayscale-0 transition-all duration-700 border border-white/5">
                        <?php echo render_elite_media('prize_travel', $media); ?>
                    </div>
                    <div class="text-center space-y-2">
                        <h4 class="text-2xl md:text-3xl serif italic text-white/50 group-hover:text-[#c5a059] transition-all">🥈 Viagem com Acompanhante</h4>
                        <p class="text-white/30 text-[10px] uppercase tracking-[0.3em]">Destino Paradasíaco Exclusivo</p>
                    </div>
                </div>
                <div class="space-y-8 group">
                     <div class="aspect-[4/5] rounded-[40px] overflow-hidden grayscale group-hover:grayscale-0 transition-all duration-700 border border-white/5">
                        <?php echo render_elite_media('prize_bag', $media); ?>
                    </div>
                    <div class="text-center space-y-2">
                        <h4 class="text-2xl md:text-3xl serif italic text-white/50 group-hover:text-[#c5a059] transition-all">🥉 Bolsa Expressive Premium</h4>
                        <p class="text-white/30 text-[10px] uppercase tracking-[0.3em]">Kit Profissional Completo</p>
                    </div>
                </div>
            </div>
            
            <div class="pt-20 text-center">
                 <?php $prize_btn = get_elite_button('prize_bag', $buttons, 'Eu quero esses prêmios'); ?>
                 <a href="<?php echo esc_url($prize_btn['url']); ?>" class="inline-block px-12 py-6 bg-[#c5a059] text-black font-bold uppercase tracking-[0.4em] text-[10px] rounded-sm hover:scale-105 transition-all">
                    <?php echo esc_html($prize_btn['label']); ?>
                 </a>
            </div>
        </div>
    </section>

    <!-- CATEGORIAS ARTÍSTICAS -->
    <section class="bg-black py-32">
        <div class="container mx-auto px-6 lg:px-24 grid grid-cols-1 lg:grid-cols-12 gap-16 items-start">
            <div class="lg:col-span-5 space-y-12">
                <div class="space-y-6">
                    <h2 class="text-4xl md:text-6xl serif italic gold-gradient-text leading-tight">Seu Talento <br>em Destaque</h2>
                    <p class="text-white/40 font-light leading-relaxed">Além dos prêmios, celebraremos as categorias que fazem a diferença na nossa comunidade!</p>
                </div>
                
                <div class="space-y-6">
                    <h5 class="text-[#c5a059] text-[10px] uppercase tracking-[0.5em] font-bold">Categorias Artísticas</h5>
                    <div class="grid gap-4">
                        <?php 
                        $arts = ["Melhor Artista em Lábios", "Melhor Artista em Delineado", "Melhor Artista em Sobrancelhas (Fio a Fio)", "Melhor Artista em Shadow"];
                        foreach ($arts as $a) : ?>
                            <div class="p-6 glass border-white/5 flex justify-between items-center group hover:border-[#c5a059]/40 transition-all">
                                <span class="text-white/80 uppercase text-[10px] tracking-widest font-medium"><?php echo $a; ?></span>
                                <span class="text-[#c5a059] opacity-0 group-hover:opacity-100 transition-opacity">→</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="lg:col-span-7 grid grid-cols-2 gap-4">
                 <div class="aspect-[4/6] rounded-3xl overflow-hidden glass border-white/5 relative">
                    <?php echo render_elite_media('artistic', $media); ?>
                 </div>
                 <div class="space-y-4 translate-y-16">
                     <div class="bg-white/[0.03] p-8 rounded-3xl border border-white/5 space-y-6">
                         <h5 class="text-white font-bold uppercase tracking-widest text-[9px] text-[#c5a059]">Homenagens Especiais</h5>
                         <ul class="text-white/40 text-[10px] space-y-3 uppercase tracking-widest">
                             <li>• Superação do Ano</li>
                             <li>• Maior Destaque Expressive</li>
                             <li>• Melhor Aplicação de Conteúdo</li>
                             <li>• Melhor Promoter</li>
                             <li>• Educadoras Destaque</li>
                         </ul>
                     </div>
                     <div class="aspect-square rounded-3xl overflow-hidden glass border-white/5">
                        <!-- Space for Homenagens image -->
                     </div>
                 </div>
            </div>
        </div>
    </section>

    <!-- ROSE OLIVEIRA (SPEAKER) -->
    <section class="bg-white/[0.01]">
        <div class="container mx-auto px-6 lg:px-24">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-24 items-center">
                <div class="relative group">
                    <div class="aspect-[3/4] rounded-[60px] overflow-hidden overflow-hidden shadow-2xl">
                        <?php echo render_elite_media('speaker', $media); ?>
                    </div>
                    <div class="absolute -bottom-10 -left-10 p-10 glass border-[#c5a059]/30 rounded-full w-40 h-40 flex items-center justify-center text-center backdrop-blur-3xl animate-pulse">
                        <span class="text-white font-bold text-[9px] uppercase tracking-widest">Diamante 2025</span>
                    </div>
                </div>
                <div class="space-y-10">
                    <div class="space-y-6">
                        <h2 class="text-3xl md:text-5xl serif italic text-white/30 italic">"Motivação e Superação para Inspirar Você"</h2>
                        <h3 class="text-4xl md:text-6xl serif gold-gradient-text">Rose Oliveira</h3>
                        <p class="text-white/60 font-light text-base md:text-lg leading-relaxed">Rosy Oliveira chega para compartilhar sua poderosa mensagem de motivação e superação, trazendo insights práticos e inspiração genuína para que cada uma de nós encontre a força necessária para vencer obstáculos.</p>
                    </div>
                    <div class="bg-white/[0.02] p-8 border-l-2 border-[#c5a059] italic text-white/40 text-sm font-light">
                        "Histórias como essa nos motivam a continuar construindo um futuro de sucesso juntas."
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CEO & TEAM -->
    <section class="bg-black py-32">
        <div class="container mx-auto px-6 lg:px-24 space-y-16">
            <div class="text-center space-y-6">
                <h2 class="text-4xl md:text-[80px] serif leading-tight">Time <span class="gold-gradient-text italic font-bold">Oficina Gran Master</span></h2>
                <p class="text-white/40 max-w-2xl mx-auto font-light leading-relaxed italic">"Ao lado da CEO Juliana Parra, criamos um caminho para gerar recursos financeiros e impactar positivamente milhares de pessoas."</p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-8">
                <?php 
                $team = [
                    ["Juliana Parra (CEO)", "photo_juliana"],
                    ["Cátia Araújo", "photo_catia"],
                    ["Cley Fernandes", "photo_cley"],
                    ["Paty Batista", "photo_paty"]
                ];
                foreach ($team as $member) : ?>
                    <div class="space-y-4 text-center group">
                        <div class="aspect-square rounded-full overflow-hidden border-2 border-white/5 group-hover:border-[#c5a059] transition-all duration-500 scale-95 group-hover:scale-100 shadow-[0_0_30px_rgba(197,160,89,0.1)]">
                             <?php echo render_elite_media($member[1], $media); ?>
                        </div>
                        <h5 class="text-white font-bold uppercase tracking-widest text-[10px]"><?php echo $member[0]; ?></h5>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="aspect-[21/9] rounded-[40px] overflow-hidden glass border-white/5 mt-16">
                 <?php echo render_elite_media('ceo', $media); ?>
            </div>
        </div>
    </section>

    <!-- CTA FINAL / PAGAMENTO -->
    <section id="cta" class="min-h-screen flex items-center bg-white/[0.02] border-t border-white/10 relative">
        <div class="container mx-auto px-6 lg:px-24 relative z-10">
            <div class="max-w-5xl mx-auto glass p-12 md:p-24 rounded-[60px] border-white/5 text-center space-y-16">
                <div class="space-y-6">
                    <h2 class="text-5xl md:text-8xl serif italic gold-gradient-text">Seu Passaporte para o Gala</h2>
                    <p class="text-white/40 max-w-xl mx-auto font-light uppercase tracking-[0.4em] text-[10px] md:text-xs leading-relaxed">Não perca a chance de fazer parte desta noite inesquecível!</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center text-left">
                    <div class="space-y-8">
                        <div class="space-y-4">
                            <h4 class="text-[#c5a059] font-bold uppercase tracking-widest text-sm">O Pacote Inclui:</h4>
                            <ul class="text-white/60 text-xs md:text-sm space-y-4 font-light leading-relaxed">
                                <li class="flex gap-4"><span>✨</span> Jantar & Baile de Gala Deluxe</li>
                                <li class="flex gap-4"><span>✨</span> 01 dia de Palestras Incríveis</li>
                                <li class="flex gap-4"><span>✨</span> Inscrição no Desafio Gran Master</li>
                                <li class="flex gap-4"><span>✨</span> Homenagem Oficial no Palco</li>
                            </ul>
                        </div>
                        
                        <div class="space-y-4 pt-6">
                             <p class="text-[10px] uppercase font-bold text-white/80 tracking-widest">Informações de Acesso:</p>
                             <div class="flex flex-wrap gap-4">
                                 <span class="px-4 py-2 bg-white/5 rounded-lg border border-white/10 text-[9px] uppercase tracking-widest text-[#c5a059]">09 e 10 de Novembro 2026</span>
                                 <span class="px-4 py-2 bg-white/5 rounded-lg border border-white/10 text-[9px] uppercase tracking-widest text-white/50">Local: A Definir</span>
                             </div>
                        </div>
                    </div>

                    <div class="glass p-10 rounded-[40px] border-white/10 space-y-8 text-center bg-black/40">
                        <div class="space-y-2">
                             <p class="text-[9px] uppercase tracking-[0.5em] text-[#c5a059] font-bold">Vagas Limitadas</p>
                             <p class="text-xs text-white/40">Apenas 20% das posições confirmadas</p>
                        </div>
                        
                        <!-- QR CODE Dynamic -->
                        <div class="w-40 h-40 bg-white mx-auto p-4 rounded-2xl flex items-center justify-center overflow-hidden">
                             <?php echo render_elite_media('cta_qr', $media); ?>
                        </div>
                        
                        <p class="text-[10px] text-white/40 uppercase tracking-widest leading-relaxed">Aponte a câmera para entrar na lista oficial</p>
                        
                        <div class="grid gap-2 text-center text-[10px] uppercase tracking-widest font-bold text-white/30">
                            <span>Pix • Cartão • Boleto</span>
                        </div>

                         <?php $cta_btn = get_elite_button('cta_qr', $buttons, 'Garanta Sua Vaga Agora!'); ?>
                         <a href="<?php echo esc_url($cta_btn['url']); ?>" class="block w-full py-6 bg-[#c5a059] text-black font-bold uppercase tracking-[0.15em] text-xs hover:bg-white transition-all rounded-xl shadow-2xl">
                            <?php echo esc_html($cta_btn['label']); ?>
                         </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="py-20 px-24 flex flex-col md:flex-row justify-between items-center opacity-40">
        <p class="text-[9px] uppercase tracking-[0.4em] outfit text-center md:text-left">© 2026 Elite Academy • Encontro das Grans</p>
        <div class="flex gap-8 mt-6 md:mt-0 italic font-light text-[9px]">
            <span>Luxury Events</span>
            <span>Premium Recognition</span>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const swiperContainers = document.querySelectorAll('.swiper');
            swiperContainers.forEach(container => {
                new Swiper(container, {
                    loop: true,
                    autoplay: { delay: 3000, disableOnInteraction: false },
                    speed: 800,
                    pagination: { el: '.swiper-pagination', clickable: true },
                    navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
                });
            });
        });
    </script>
    <?php wp_footer(); ?>
</body>
</html>
