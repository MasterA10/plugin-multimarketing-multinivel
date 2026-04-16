<?php
/**
 * Template Name: Elite Landing Page - Gran Master
 * Description: Luxury Glassmorphism template for Elite LMS Landing Pages.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$post_id = get_the_ID();
$buttons = get_post_meta( $post_id, '_elite_lp_buttons', true ) ?: array();
$media   = get_post_meta( $post_id, '_elite_lp_media', true ) ?: array();

// Helper to render media (Single or Carousel)
function render_elite_media($section_key, $media_data) {
    $config = isset($media_data[$section_key]) ? $media_data[$section_key] : array('mode' => 'single', 'ids' => array());
    $ids = $config['ids'];
    $mode = $config['mode'];

    if (empty($ids)) return '';

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
        .mesh { position: absolute; width: 100%; height: 100%; background: radial-gradient(circle at 10% 10%, rgba(197, 160, 89, 0.12) 0%, transparent 40%), radial-gradient(circle at 90% 80%, rgba(255, 255, 255, 0.05) 0%, transparent 40%); filter: blur(80px); }
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

        .btn-elite { position: relative; overflow: hidden; transition: all 0.5s ease; border-radius: 4px; }
        .btn-elite span { position: relative; z-index: 10; }
        .btn-elite::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: var(--gold); transform: translateY(100%); transition: transform 0.5s cubic-bezier(0.19, 1, 0.22, 1); }
        .btn-elite:hover::before { transform: translateY(0); }
        .btn-elite:hover { color: #000; border-color: var(--gold); }
        @keyframes rotate-glow { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        .glow-orbit { position: absolute; width: 100%; height: 100%; max-width: 100vw; background: conic-gradient(from 0deg, transparent, rgba(197, 160, 89, 0.1), transparent); animation: rotate-glow 10s linear infinite; }
        .swiper-button-next, .swiper-button-prev { color: var(--gold) !important; scale: 0.7; }
        .swiper-pagination-bullet-active { background: var(--gold) !important; }
        section { padding: 80px 0; position: relative; overflow: hidden; }
        @media (min-width: 768px) {
            section { padding: 120px 0; }
        }
    </style>
</head>
<body>

    <div class="bg-wrapper">
        <div class="mesh"></div>
        <div class="noise"></div>
    </div>

    <!-- Navegação -->
    <nav class="fixed w-full z-50 py-3 md:py-8 px-4 md:px-24 flex justify-between items-center backdrop-blur-md bg-black/20 border-b border-white/5">
        <div class="flex items-center gap-4">
             <div class="w-8 h-8 md:w-10 md:h-10 border border-[#c5a059]/40 flex items-center justify-center rounded-sm">
                <span class="text-[8px] md:text-[10px] gold-gradient-text font-bold uppercase outfit">Elite</span>
            </div>
            <span class="text-[8px] tracking-[0.6em] font-light uppercase opacity-40 outfit hidden md:block">Gran Master Program</span>
        </div>
        <?php $hero_btn = get_elite_button('hero', $buttons, 'Solicitar Acesso'); ?>
        <a href="<?php echo esc_url($hero_btn['url']); ?>" class="text-[8px] md:text-[9px] uppercase tracking-[0.1em] md:tracking-[0.4em] font-bold bg-[#c5a059] text-black px-4 md:px-6 py-2 md:py-3 hover:bg-[#e2c275] transition-all rounded-sm">
            Candidatar-se
        </a>
    </nav>

    <!-- HERO SECTION -->
    <section class="min-h-screen flex items-center pt-24 md:pt-32">
        <div class="container mx-auto px-6 lg:px-24 grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
            <div class="lg:col-span-12 lg:hidden mb-8"> <!-- Hero Mobile Image Header -->
                 <div class="aspect-[4/5] relative z-20 w-full animate-float max-w-[400px] mx-auto">
                    <?php echo render_elite_media('hero', $media); ?>
                </div>
            </div>
            <div class="lg:col-span-7 space-y-8 md:space-y-12 text-center lg:text-left order-2 lg:order-1">
                <div class="inline-flex items-center gap-4 px-5 py-2 glass rounded-full border-white/5 mx-auto lg:mx-0">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#c5a059] opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-[#c5a059]"></span>
                    </span>
                    <span class="text-[8px] md:text-[9px] uppercase tracking-[0.4em] font-semibold text-white/60 outfit">Seleção Restrita 2026</span>
                </div>
                <h1 class="text-4xl sm:text-5xl md:text-[90px] serif leading-[1.1] md:leading-[1] font-light">
                    Ser <span class="gold-gradient-text font-bold italic">Gran Master</span><br class="hidden md:block">
                    <span class="text-white not-italic opacity-90">significa assumir uma identidade de autoridade.</span>
                </h1>
                <p class="text-base md:text-xl text-white/40 font-light max-w-xl mx-auto lg:mx-0 leading-relaxed">
                    Este não é um convite aberto. É uma seleção para quem já domina a técnica e busca o <span class="text-white italic">reconhecimento institucional</span> que o seu nível merece.
                </p>
                <div class="pt-4 md:pt-6">
                    <a href="<?php echo esc_url($hero_btn['url']); ?>" class="inline-block px-10 md:px-16 py-5 md:py-6 bg-[#c5a059] text-black uppercase tracking-[0.3em] md:tracking-[0.5em] text-[9px] md:text-[10px] font-bold hover:bg-[#e2c275] transition-all rounded-sm shadow-lg shadow-gold-500/20">
                        <span><?php echo esc_html($hero_btn['label']); ?></span>
                    </a>
                </div>
            </div>
            <div class="lg:col-span-5 relative hidden lg:block lg:order-2">
                <div class="aspect-[4/5] relative z-20 w-full animate-float max-w-[400px] mx-auto lg:max-w-none">
                    <?php echo render_elite_media('hero', $media); ?>
                </div>
                <div class="absolute -top-10 -right-10 md:-top-20 md:-right-20 w-40 h-40 md:w-80 md:h-80 bg-[#c5a059]/10 rounded-full blur-[60px] md:blur-[100px] z-10 pointer-events-none"></div>
            </div>
        </div>
    </section>

    <!-- INTRODUÇÃO / VALIDAÇÃO -->
    <section class="bg-white/[0.01]">
        <div class="container mx-auto px-6 lg:px-24 grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-24 items-center">
            <div class="order-1 lg:order-1 relative"> <!-- IMAGE FIRST ON MOBILE -->
                <div class="aspect-video md:aspect-square">
                    <?php echo render_elite_media('validation', $media); ?>
                </div>
            </div>
            <div class="order-2 lg:order-2 space-y-6 md:space-y-8 text-center lg:text-left"> <!-- TEXT SECOND ON MOBILE -->
                <h2 class="text-3xl md:text-5xl serif leading-tight">
                    Você já domina a técnica. <br class="hidden md:block">
                    <span class="text-white/40 italic">Mas ainda não tem o reconhecimento que merece.</span>
                </h2>
                <div class="space-y-4 md:space-y-6 text-white/60 leading-relaxed font-light text-sm md:text-base">
                    <p>O mercado respeita quem tem autoridade. E autoridade não se improvisa — constrói-se com credenciamento oficial.</p>
                    <p>Há profissionais que executam procedimentos com excelência impecável. Dominam cada traço, cada nuance. No entanto, falta o posicionamento que distingue uma profissional comum de uma referência no mercado.</p>
                    <p class="text-white italic">Talento isolado não basta. Técnica sem validação institucional não cria diferenciação percebida.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- RECONHECIMENTO INSTITUCIONAL -->
    <section class="bg-black">
        <div class="container mx-auto px-6 lg:px-24 space-y-16">
            <div class="text-center space-y-4">
                <h2 class="text-4xl md:text-6xl serif italic gold-gradient-text leading-tight">Reconhecimento Institucional</h2>
                <p class="text-white/40 max-w-2xl mx-auto font-light">Você não pede respeito. Você impõe posicionamento através de credenciais que o mercado reconhece como intransferíveis.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 md:gap-8">
                <div class="glass p-8 border-white/5 space-y-4 hover:border-[#c5a059]/30 transition-all group">
                    <h3 class="text-white font-bold uppercase tracking-widest text-xs md:text-sm">Respeito Imediato</h3>
                    <p class="text-white/40 text-[10px] md:text-xs leading-relaxed">Você não precisa mais provar sua competência. A patente Gran Master comunica instantaneamente seu patamar de excelência.</p>
                </div>
                <div class="glass p-8 border-white/5 space-y-4 hover:border-[#c5a059]/30 transition-all group">
                    <h3 class="text-white font-bold uppercase tracking-widest text-xs md:text-sm">Referência Nacional</h3>
                    <p class="text-white/40 text-[10px] md:text-xs leading-relaxed">Representação oficial da marca mais respeitada, transformando você em autoridade consultada e admirada.</p>
                </div>
                <div class="glass p-8 border-white/5 space-y-4 hover:border-[#c5a059]/30 transition-all group">
                    <h3 class="text-white font-bold uppercase tracking-widest text-xs md:text-sm">Autoridade Validada</h3>
                    <p class="text-white/40 text-[10px] md:text-xs leading-relaxed">Posicionamento que não se conquista sozinha, mas através de um processo seletivo que poucos ultrapassam.</p>
                </div>
                <div class="glass p-8 border-white/5 space-y-4 hover:border-[#c5a059]/30 transition-all group">
                    <h3 class="text-white font-bold uppercase tracking-widest text-xs md:text-sm">Credenciamento Oficial</h3>
                    <p class="text-white/40 text-[10px] md:text-xs leading-relaxed">Credenciamento oficial que valida publicamente seu nível técnico e posicionamento profissional perante todo o mercado.</p>
                </div>
            </div>

            <div class="aspect-video md:aspect-[21/9] rounded-3xl overflow-hidden glass border-white/5">
                <?php echo render_elite_media('recognition', $media); ?>
            </div>
        </div>
    </section>

    <!-- CHECKLIST / ELIGIBILIDADE -->
    <section>
        <div class="container mx-auto px-4 lg:px-24">
            <div class="max-w-4xl mx-auto glass p-8 md:p-16 rounded-[40px] md:rounded-[60px] border-white/5 relative overflow-hidden text-center space-y-8 md:space-y-12">
                <div class="glow-orbit opacity-20 pointer-events-none"></div>
                <div class="relative z-10 space-y-4 px-2">
                    <h2 class="text-3xl md:text-6xl serif italic gold-gradient-text">Este programa é para você?</h2>
                    <p class="text-white/40 uppercase tracking-[0.2em] md:tracking-[0.3em] text-[9px] md:text-[10px]">O credenciamento é para profissionais que:</p>
                </div>
                <div class="relative z-10 grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 text-left">
                    <?php 
                    $checks = ["Atuam há pelo menos dois anos", "Dominam fundamentos e visagismo", "Possuem carteira ativa de clientes", "Entregam excelência consistente", "Buscam reconhecimento nacional", "Ambicionam ser educadoras"];
                    foreach ($checks as $c) : ?>
                        <div class="flex items-center gap-4 bg-white/5 p-4 md:p-5 rounded-2xl border border-white/5 hover:border-[#c5a059]/30 transition-all">
                            <span class="text-[#c5a059] font-bold shrink-0">✓</span>
                            <span class="text-[10px] md:text-xs text-white/70 uppercase tracking-widest leading-tight"><?php echo $c; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="relative z-10 pt-4 md:pt-8">
                    <?php $check_btn = get_elite_button('checklist', $buttons, 'Quero me candidatar'); ?>
                    <a href="<?php echo esc_url($check_btn['url']); ?>" class="inline-block w-full md:w-auto px-12 py-5 bg-[#c5a059] text-black font-bold uppercase tracking-[0.3em] md:tracking-[0.4em] text-[11px] rounded-sm hover:bg-[#e2c275] transition-all">
                        <?php echo esc_html($check_btn['label']); ?>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- LIDERANÇA E ESTRUTURA -->
    <section class="bg-white/[0.01]">
        <div class="container mx-auto px-6 lg:px-24 grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-24 items-center">
            <div class="order-1 lg:order-1 space-y-8 text-center lg:text-left">
                <div class="space-y-4">
                    <h2 class="text-3xl md:text-5xl serif leading-tight">Liderança e Estrutura <br><span class="text-white/30 italic">do Projeto</span></h2>
                    <p class="text-white/60 font-light text-sm md:text-base leading-relaxed">Conduzido por especialistas com ampla experiência em formação profissional, desenvolvimento de carreira e gestão estratégica dentro do setor da beleza.</p>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <?php 
                    $mentorships = ["Análise SWOT/FOFA", "Encontro Presencial", "Mentoria Financeira", "Mentoria de Técnicas", "Marketing e Vendas", "Mentoria de Palestras", "Apresentação de Técnicas", "Apresentação de Palestras"];
                    foreach ($mentorships as $m) : ?>
                        <div class="p-4 border border-white/5 rounded-xl bg-white/[0.02]">
                            <span class="text-[8px] md:text-[9px] uppercase tracking-widest text-[#c5a059] block mb-1">Especialidade</span>
                            <span class="text-[10px] md:text-xs text-white font-medium"><?php echo $m; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="pt-4">
                    <?php $lead_btn = get_elite_button('leadership', $buttons, 'Conhecer Mentores'); ?>
                    <a href="<?php echo esc_url($lead_btn['url']); ?>" class="inline-block w-full md:w-auto px-10 py-4 border border-[#c5a059]/40 text-[#c5a059] uppercase tracking-widest text-[9px] font-bold hover:bg-[#c5a059] hover:text-black transition-all">
                        <?php echo esc_html($lead_btn['label']); ?>
                    </a>
                </div>
            </div>
            <div class="order-2 lg:order-2 relative">
                <div class="aspect-[4/5] rounded-3xl overflow-hidden shadow-2xl">
                    <?php echo render_elite_media('leadership', $media); ?>
                </div>
            </div>
        </div>
    </section>

    <!-- JORNADA DE TRANSFORMAÇÃO (FASES) -->
    <section class="bg-black">
        <div class="container mx-auto px-6 lg:px-24 space-y-16">
            <div class="text-center space-y-4">
                <h2 class="text-4xl md:text-6xl serif italic gold-gradient-text leading-tight">A Jornada de Transformação</h2>
                <p class="text-white/40 max-w-2xl mx-auto font-light">O Projeto Gran Master não é apenas um credenciamento burocrático. É uma experiência completa de elevação profissional.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-0 lg:divide-x divide-white/10">
                <div class="p-8 space-y-6 group hover:bg-white/[0.02] transition-all">
                    <span class="text-4xl md:text-5xl font-light text-white/5 group-hover:text-[#c5a059]/20 transition-all outfit">01</span>
                    <h3 class="text-[#c5a059] font-bold uppercase tracking-widest text-xs">Fase 1: Online</h3>
                    <p class="text-white/40 text-[10px] md:text-xs leading-relaxed">Formação Estratégica Online focada em posicionamento de autoridade e precificação premium.</p>
                </div>
                <div class="p-8 space-y-6 group hover:bg-white/[0.02] transition-all">
                    <span class="text-4xl md:text-5xl font-light text-white/5 group-hover:text-[#c5a059]/20 transition-all outfit">02</span>
                    <h3 class="text-[#c5a059] font-bold uppercase tracking-widest text-xs">Fase 2: Mentorias</h3>
                    <h3 class="text-[10px] md:text-xs text-white/80">Mentorias de Posicionamento e diferenciação competitiva individualizada.</h3>
                </div>
                <div class="p-8 space-y-6 group hover:bg-white/[0.02] transition-all">
                    <span class="text-4xl md:text-5xl font-light text-white/5 group-hover:text-[#c5a059]/20 transition-all outfit">03</span>
                    <h3 class="text-[#c5a059] font-bold uppercase tracking-widest text-xs">Fase 3: Presencial</h3>
                    <p class="text-white/40 text-[10px] md:text-xs leading-relaxed">Oficina Presencial de Alinhamento Técnico com refinamento de técnicas e protocolos.</p>
                </div>
                <div class="p-8 space-y-6 group hover:bg-white/[0.02] transition-all">
                    <span class="text-4xl md:text-5xl font-light text-white/5 group-hover:text-[#c5a059]/20 transition-all outfit">04</span>
                    <h3 class="text-[#c5a059] font-bold uppercase tracking-widest text-xs">Fase 4: Gala</h3>
                    <p class="text-white/40 text-[10px] md:text-xs leading-relaxed">Baile de Gala e Entrega Oficial do Título em evento cinematográfico de consagração.</p>
                </div>
            </div>

            <div class="aspect-video md:aspect-[21/9] rounded-3xl overflow-hidden glass border-white/5">
                <?php echo render_elite_media('phases', $media); ?>
            </div>
        </div>
    </section>

    <!-- JORNADA GRAN MASTER -->
    <section class="bg-black">
        <div class="container mx-auto px-6 lg:px-24 grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">
            <div class="order-1 lg:order-2 relative px-4 lg:px-0"> <!-- IMAGE FIRST ON MOBILE -->
                <div class="aspect-video lg:aspect-[3/4]">
                    <?php echo render_elite_media('authority', $media); ?>
                </div>
            </div>
            <div class="order-2 lg:order-1 space-y-6 md:space-y-8 text-center lg:text-left"> <!-- TEXT SECOND ON MOBILE -->
                <h2 class="text-4xl md:text-6xl serif italic gold-gradient-text">A Jornada Gran Master</h2>
                <p class="text-base md:text-xl text-white/60 font-light leading-relaxed">
                    O Projeto Oficina Gran Master é um programa seletivo criado para reconhecer profissionais de alto nível técnico e transformá-las em autoridades reconhecidas.
                </p>
                <div class="grid gap-4 md:gap-6 text-left max-w-lg mx-auto lg:mx-0">
                    <div class="flex gap-4 md:gap-6 border-l border-[#c5a059]/30 pl-6 md:pl-8 py-2">
                        <span class="text-2xl md:text-3xl font-light text-[#c5a059] outfit shrink-0">01</span>
                        <div>
                            <h4 class="text-white font-bold uppercase tracking-widest text-[10px] md:text-xs">Credenciamento Oficial</h4>
                            <p class="text-white/40 text-[10px] md:text-xs mt-1">Validação pública do seu nível técnico perante o mercado.</p>
                        </div>
                    </div>
                    <div class="flex gap-4 md:gap-6 border-l border-[#c5a059]/30 pl-6 md:pl-8 py-2">
                        <span class="text-2xl md:text-3xl font-light text-[#c5a059] outfit shrink-0">02</span>
                        <div>
                            <h4 class="text-white font-bold uppercase tracking-widest text-[10px] md:text-xs">Padronização Técnica</h4>
                            <p class="text-white/40 text-[10px] md:text-xs mt-1">Refinamento de protocolos sob o padrão Academy.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- POR QUE ENTRAR NO PROGRAMA? -->
    <section class="bg-white/[0.01]">
        <div class="container mx-auto px-6 lg:px-24 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="order-1 lg:order-1 relative">
                 <div class="aspect-square rounded-[40px] overflow-hidden">
                    <?php echo render_elite_media('reasons', $media); ?>
                 </div>
            </div>
            <div class="order-2 lg:order-2 space-y-8">
                <h2 class="text-3xl md:text-5xl serif leading-tight">Por que entrar <br><span class="text-[#c5a059]">no programa agora?</span></h2>
                <div class="space-y-4">
                    <?php 
                    $reasons = [
                        "Ser vista e reconhecida como você merece.",
                        "Recuperar o investimento em cursos e equipamentos rapidamente.",
                        "Ganhar mais trabalhando menos horas por dia.",
                        "Ter mais tempo de qualidade com seus filhos e família.",
                        "Conquistar bens como trocar seu carro ou viajar mais.",
                        "Assumir sua posição como herdeira da prosperidade."
                    ];
                    foreach ($reasons as $r) : ?>
                        <div class="flex gap-4 items-start">
                            <span class="w-1.5 h-1.5 bg-[#c5a059] rounded-full mt-2 shrink-0"></span>
                            <p class="text-white/60 text-sm md:text-base font-light"><?php echo $r; ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="pt-6">
                    <?php $reason_btn = get_elite_button('reasons', $buttons, 'Essa é a minha oportunidade'); ?>
                    <a href="<?php echo esc_url($reason_btn['url']); ?>" class="inline-block px-10 py-5 bg-[#c5a059] text-black font-bold uppercase tracking-widest text-[10px] rounded-sm hover:scale-105 transition-all">
                        <?php echo esc_html($reason_btn['label']); ?>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- BENEFÍCIOS GRAN MASTER -->
    <section class="bg-black">
        <div class="container mx-auto px-6 lg:px-24 space-y-16">
            <div class="text-center space-y-4">
                <span class="text-[#c5a059] text-[10px] uppercase tracking-[0.5em] font-bold">Vantagens Exclusivas</span>
                <h2 class="text-4xl md:text-6xl serif gold-gradient-text">Benefícios de ser Gran Master</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php 
                $benefits_items = [
                    "Mentorias semanais ao vivo",
                    "Descontos exclusivos em pigmentos",
                    "Lives e Collabs no perfil da Marca",
                    "Acesso VIP a eventos e feiras",
                    "Palestrante autorizada da Marca",
                    "Uso do Banco de Leads (54k contatos)",
                    "Desconto em locação de espaço físico",
                    "Estúdio de gravação profissional",
                    "Visibilidade Nacional Validada"
                ];
                foreach ($benefits_items as $b) : ?>
                    <div class="p-6 glass border-white/5 flex items-center gap-4 hover:bg-white/[0.03] transition-all">
                        <div class="w-8 h-8 rounded-full bg-[#c5a059]/20 flex items-center justify-center shrink-0">
                            <span class="text-[#c5a059] text-xs">★</span>
                        </div>
                        <span class="text-white/80 text-xs md:text-sm uppercase tracking-wider font-medium"><?php echo $b; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="aspect-video md:aspect-[21/9] rounded-3xl overflow-hidden glass border-white/5">
                <?php echo render_elite_media('benefits', $media); ?>
            </div>
        </div>
    </section>

    <!-- CRONOGRAMA -->
    <section>
        <div class="container mx-auto px-6 lg:px-24">
             <h2 class="text-4xl md:text-[80px] serif text-center mb-12 md:mb-24 gold-gradient-text uppercase py-2 leading-tight">Cronograma do Projeto</h2>
             <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3 md:gap-4">
                <?php 
                $steps = ["Avaliação de Perfil", "Grupo WhatsApp", "Apresentação", "Avaliação Presencial", "Encontros Oficiais", "Ciclo Concluído", "Finalização"];
                foreach ($steps as $i => $s) : ?>
                    <div class="glass p-4 md:p-8 rounded-2xl md:rounded-3xl border-white/5 text-center group hover:bg-[#c5a059]/[0.05] transition-all">
                        <span class="text-2xl md:text-4xl font-light text-white/10 group-hover:text-[#c5a059]/40 outfit block mb-2 md:mb-4"><?php echo str_pad($i+1, 2, '0', STR_PAD_LEFT); ?></span>
                        <h5 class="text-[8px] md:text-[10px] uppercase font-bold tracking-[0.1em] md:tracking-[0.2em] text-white/80"><?php echo $s; ?></h5>
                    </div>
                <?php endforeach; ?>
             </div>
             <div class="mt-12 md:mt-20 aspect-video max-w-5xl mx-auto px-2">
                 <?php echo render_elite_media('schedule', $media); ?>
             </div>
        </div>
    </section>

    <!-- MAPA DE GANHOS -->
    <section class="bg-black py-24 md:py-32">
        <div class="container mx-auto px-6 lg:px-24 space-y-16">
            <div class="text-center space-y-4">
                <h2 class="text-4xl md:text-6xl serif italic gold-gradient-text">O Mapa para Ganhar Mais</h2>
                <p class="text-white/40 max-w-2xl mx-auto font-light leading-relaxed">Pague o investimento já no segundo mês seguindo o planejamento validado por quem já é Gran Master.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
                <div class="glass p-8 border-white/5 space-y-6 text-center hover:bg-[#c5a059]/[0.02] transition-all">
                    <span class="text-[#c5a059] text-xs uppercase tracking-widest font-bold">Mês 01</span>
                    <div class="space-y-2">
                        <p class="text-white/60 text-xs uppercase">10 Design + 2 Micro</p>
                        <h4 class="text-3xl md:text-4xl outfit font-medium">R$ 1.250,00</h4>
                    </div>
                </div>
                <div class="glass p-10 border-[#c5a059]/30 space-y-6 text-center bg-[#c5a059]/[0.05] relative -translate-y-2">
                    <span class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-[#c5a059] text-black text-[8px] font-bold px-4 py-1 rounded-full uppercase tracking-widest">Ponto de Equilíbrio</span>
                    <span class="text-[#c5a059] text-xs uppercase tracking-widest font-bold">Mês 02</span>
                    <div class="space-y-2">
                        <p class="text-white/60 text-xs uppercase">20 Design + 5 Micro + 2 Labial</p>
                        <h4 class="text-3xl md:text-4xl outfit font-medium">R$ 3.850,00</h4>
                    </div>
                </div>
                <div class="glass p-8 border-white/5 space-y-6 text-center hover:bg-[#c5a059]/[0.02] transition-all">
                    <span class="text-[#c5a059] text-xs uppercase tracking-widest font-bold">Mês 03</span>
                    <div class="space-y-2">
                        <p class="text-white/60 text-xs uppercase">25 Design + 8 Micro + 4 Labial</p>
                        <h4 class="text-3xl md:text-4xl outfit font-medium text-[#c5a059]">R$ 6.275,00</h4>
                    </div>
                </div>
            </div>

            <p class="text-[10px] md:text-xs text-center text-white/20 italic max-w-xl mx-auto">Este planejamento foi validado por todos que já chegaram ao nível Gran Master, mas o resultado depende exclusivamente da sua execução.</p>
            
            <div class="aspect-video md:aspect-[21/9] rounded-3xl overflow-hidden glass border-white/5">
                <?php echo render_elite_media('map', $media); ?>
            </div>
        </div>
    </section>

    <!-- PRESTÍGIO / AVALIAÇÃO TÉCNICA -->
    <section class="bg-white/[0.01]">
        <div class="container mx-auto px-6 lg:px-24 space-y-16">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-24 items-center">
                <div class="space-y-8 order-2 lg:order-1">
                    <div class="space-y-4 text-center lg:text-left">
                        <h2 class="text-3xl md:text-5xl serif leading-tight">Avaliação Técnica <br><span class="text-[#c5a059]">de Excelência</span></h2>
                        <p class="text-white/60 font-light text-sm md:text-base leading-relaxed">O coração do Projeto Oficina Gran Master acontece em um encontro presencial exclusivo no Centro Técnico em Guarulhos-SP. Durante dois dias intensivos, cada participante passa por uma avaliação criteriosa.</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php 
                        $criteria = ["Domínio Técnico", "Precisão de Execução", "Padrão de Resultados", "Segurança de Procedimento", "Postura Profissional", "Refinamento de Protocolos"];
                        foreach ($criteria as $c) : ?>
                            <div class="flex items-center gap-3 p-4 bg-white/5 border border-white/5 rounded-xl">
                                <span class="w-1 h-1 bg-[#c5a059] rounded-full"></span>
                                <span class="text-[10px] md:text-xs text-white/80 uppercase tracking-widest"><?php echo $c; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="bg-[#c5a059]/10 p-6 rounded-2xl border border-[#c5a059]/20">
                        <p class="text-white/80 text-xs md:text-sm font-light italic text-center lg:text-left">"A credencial Gran Master não é concedida automaticamente. Ela é conquistada por profissionais que demonstram excelência real."</p>
                    </div>
                </div>
                <div class="order-1 lg:order-2 relative">
                    <div class="aspect-square rounded-[40px] overflow-hidden shadow-2xl">
                        <?php echo render_elite_media('prestige', $media); ?>
                    </div>
                </div>
            </div>

            <!-- RECONHECIMENTO OFICIAL -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-12">
                <div class="text-center p-6 glass border-white/5 space-y-3">
                    <span class="text-[#c5a059] text-2xl">🏆</span>
                    <h5 class="text-white text-[10px] uppercase font-bold tracking-widest">Troféu Oficial</h5>
                </div>
                <div class="text-center p-6 glass border-white/5 space-y-3">
                    <span class="text-[#c5a059] text-2xl">📜</span>
                    <h5 class="text-white text-[10px] uppercase font-bold tracking-widest">Titularidade</h5>
                </div>
                <div class="text-center p-6 glass border-white/5 space-y-3">
                    <span class="text-[#c5a059] text-2xl">🛡️</span>
                    <h5 class="text-white text-[10px] uppercase font-bold tracking-widest">Selo Gran Master</h5>
                </div>
                <div class="text-center p-6 glass border-white/5 space-y-3">
                    <span class="text-[#c5a059] text-2xl">📸</span>
                    <h5 class="text-white text-[10px] uppercase font-bold tracking-widest">Foto de Autoridade</h5>
                </div>
            </div>
        </div>
    </section>

    <!-- INVESTIMENTO -->
    <section id="pricing" class="bg-white/[0.02] border-y border-white/5">
        <div class="container mx-auto px-6 lg:px-24 text-center">
            <div class="max-w-4xl mx-auto space-y-8 md:space-y-12 py-16 md:py-24">
                <div class="space-y-4">
                    <p class="text-[#c5a059] text-[10px] md:text-xs uppercase tracking-[0.5em] font-bold">Valor Real do Programa</p>
                    <p class="text-white/40 text-sm md:text-base max-w-2xl mx-auto font-light leading-relaxed">Se você pagasse separadamente por cada consultoria, mentoria ou curso dentro deste programa, o investimento seria de aproximadamente <span class="text-white font-bold">R$ 30.000,00</span>.</p>
                </div>
                <h2 class="text-5xl md:text-7xl serif italic gold-gradient-text">Investimento</h2>
                <div class="space-y-4">
                    <p class="text-white/30 uppercase tracking-[0.2em] md:tracking-[0.4em] line-through text-xs md:text-base">De R$ 5.500,00</p>
                    <p class="text-4xl md:text-7xl font-bold outfit">R$ 1.197,00 <br class="md:hidden"><span class="text-[10px] md:text-xs uppercase text-[#c5a059] tracking-widest">no pix</span></p>
                    <p class="text-white/40 text-xs md:text-sm">Ou em até 12x R$ 131,92</p>
                </div>
                <div class="pt-4 md:pt-8">
                    <?php $price_btn = get_elite_button('pricing', $buttons, 'Garantir minha vaga'); ?>
                    <a href="<?php echo esc_url($price_btn['url']); ?>" class="block w-full py-6 md:py-8 text-center bg-[#c5a059] text-black font-bold uppercase tracking-[0.3em] md:tracking-[0.6em] text-xs md:text-sm px-4 hover:bg-[#e2c275] transition-all rounded-sm shadow-xl shadow-gold-500/10">
                        <span><?php echo esc_html($price_btn['label']); ?></span>
                    </a>
                    <p class="mt-4 md:mt-6 text-[8px] md:text-[10px] uppercase tracking-widest text-white/20 italic">Única oportunidade de credenciamento regional exclusivo</p>
                </div>
            </div>
        </div>
    </section>

    <!-- DUAS ESCOLHAS -->
    <section class="bg-black py-24 md:py-32">
        <div class="container mx-auto px-6 lg:px-24 space-y-16">
            <div class="text-center space-y-4">
                <h2 class="text-4xl md:text-6xl serif italic gold-gradient-text">Duas Escolhas. Dois Destinos.</h2>
                <p class="text-white/40 max-w-xl mx-auto font-light">A escolha não é entre trabalhar ou não trabalhar. É entre permanecer comum ou tornar-se inesquecível.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-16">
                <!-- OPÇÃO 1: COMUM -->
                <div class="glass p-10 border-white/5 space-y-8 relative overflow-hidden group">
                    <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/5 rounded-full blur-3xl"></div>
                    <h3 class="text-white/40 text-xl md:text-2xl serif">Continuar como está</h3>
                    <ul class="space-y-4">
                        <?php 
                        $common = ["Sem diferenciação percebida", "Disputando preço por região", "Construindo autoridade sozinha", "Tecnicamente excelente, mas invisível", "Trabalhando muito sem reconhecimento"];
                        foreach ($common as $c) : ?>
                            <li class="flex gap-4 items-center text-white/30 text-xs md:text-sm italic">
                                <span>—</span> <?php echo $c; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- OPÇÃO 2: GRAN MASTER -->
                <div class="glass p-10 border-[#c5a059]/30 space-y-8 relative overflow-hidden bg-[#c5a059]/[0.05] group">
                    <div class="absolute -top-10 -right-10 w-40 h-40 bg-[#c5a059]/10 rounded-full blur-3xl"></div>
                    <h3 class="text-[#c5a059] text-xl md:text-2xl serif italic">Tornar-se Gran Master</h3>
                    <ul class="space-y-4">
                        <?php 
                        $elite = ["Autoridade validada institucionalmente", "Reconhecimento instantâneo", "Diferenciação impossível de replicar", "Justificativa para precificação premium", "Magnetismo para clientes exigentes"];
                        foreach ($elite as $e) : ?>
                            <li class="flex gap-4 items-center text-white/80 text-xs md:text-sm font-medium uppercase tracking-wider">
                                <span class="text-[#c5a059]">✓</span> <?php echo $e; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <div class="aspect-[21/9] rounded-[40px] overflow-hidden shadow-2xl">
                 <?php echo render_elite_media('choices', $media); ?>
            </div>
        </div>
    </section>

    <!-- REGRAS DO PROCESSO SELETIVO -->
    <section class="bg-white/[0.01] border-t border-white/5">
        <div class="container mx-auto px-6 lg:px-24 py-24">
            <div class="max-w-3xl mx-auto glass p-8 md:p-12 border-white/10 rounded-3xl space-y-10">
                <div class="text-center space-y-2">
                    <h2 class="text-2xl md:text-4xl serif gold-gradient-text uppercase">Regras do Processo Seletivo</h2>
                    <p class="text-white/40 text-[10px] md:text-xs uppercase tracking-widest">Leitura obrigatória antes da candidatura</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-[10px] md:text-xs text-white/60 leading-relaxed font-light">
                    <div class="space-y-6">
                        <div class="space-y-2">
                            <h4 class="text-[#c5a059] font-bold uppercase tracking-widest">01. Candidatura</h4>
                            <p>O candidato deverá fazer o pré-cadastro completo e aguardar a avaliação técnica inicial.</p>
                        </div>
                        <div class="space-y-2">
                            <h4 class="text-[#c5a059] font-bold uppercase tracking-widest">02. Aprovação</h4>
                            <p>Somente após a aprovação do perfil, o acesso ao pagamento e à plataforma oficial será liberado.</p>
                        </div>
                    </div>
                    <div class="space-y-6">
                        <div class="space-y-2">
                            <h4 class="text-[#c5a059] font-bold uppercase tracking-widest">03. Comunidade Oficial</h4>
                            <p>O candidato deverá participar da nossa comunidade por no mínimo 6 meses sob taxa de R$ 59,97 mensais.</p>
                        </div>
                        <div class="space-y-2">
                            <h4 class="text-[#c5a059] font-bold uppercase tracking-widest">04. Titularidade</h4>
                            <p>A titularidade Gran Master é intransferível e vinculada ao cumprimento dos ciclos de avaliação.</p>
                        </div>
                    </div>
                </div>

                <div class="pt-6 text-center">
                    <?php $rules_btn = get_elite_button('rules', $buttons, 'Fazer Pré-Cadastro Agora'); ?>
                    <a href="<?php echo esc_url($rules_btn['url']); ?>" class="inline-block px-12 py-5 bg-[#c5a059] text-black font-bold uppercase tracking-widest text-[10px] rounded-sm hover:scale-105 transition-all">
                        <?php echo esc_html($rules_btn['label']); ?>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- CONCLUSÃO / DECISÃO -->
    <section class="min-h-screen flex items-center bg-black">
        <div class="container mx-auto px-6 lg:px-24 text-center space-y-12 md:space-y-16 flex flex-col items-center">
            <div class="max-w-4xl mx-auto aspect-video px-4 order-1"> <!-- IMAGE FIRST ON MOBILE -->
                 <?php echo render_elite_media('conclusion', $media); ?>
            </div>
            <div class="space-y-4 px-4 order-2"> <!-- TEXT SECOND ON MOBILE -->
                <h2 class="text-3xl md:text-7xl serif leading-tight">Você quer continuar comum...<br class="hidden md:block"><span class="italic text-white hover:text-[#c5a059] transition-colors">ou tornar-se inesquecível?</span></h2>
                <p class="text-white/40 max-w-xl mx-auto text-base md:text-lg font-light leading-relaxed">Gran Master é a patente que diferencia definitivamente as que dominam técnica das que dominam também posicionamento.</p>
            </div>
            <div class="pt-4 md:pt-10 px-6 order-3">
                <?php $final_btn = get_elite_button('conclusion', $buttons, 'Sim, eu quero ser Gran Master'); ?>
                <a href="<?php echo esc_url($final_btn['url']); ?>" class="inline-block w-full md:w-auto px-12 py-5 bg-[#c5a059] text-black font-bold uppercase tracking-[0.3em] md:tracking-[0.4em] text-[9px] md:text-[10px] rounded-sm hover:scale-105 transition-all">
                    <?php echo esc_html($final_btn['label']); ?>
                </a>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="py-12 md:py-20 px-6 md:px-24 flex flex-col md:flex-row justify-between items-center bg-black border-t border-white/5 opacity-40">
        <p class="text-[8px] md:text-[9px] uppercase tracking-[0.2em] md:tracking-[0.4em] outfit text-center md:text-left">© 2026 Elite Academy • Expressive Kollors Group</p>
        <div class="flex gap-4 md:gap-8 mt-6 md:mt-0">
            <span class="text-[8px] md:text-[9px] uppercase tracking-[0.2em] md:tracking-[0.4em] outfit">Privé</span>
            <span class="text-[8px] md:text-[9px] uppercase tracking-[0.2em] md:tracking-[0.4em] outfit">Curadoria de Excelência</span>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Swipers dynamically for each section that has a carousel
            const swiperContainers = document.querySelectorAll('.swiper');
            swiperContainers.forEach(container => {
                new Swiper(container, {
                    loop: true,
                    autoplay: { 
                        delay: 3000,
                        disableOnInteraction: false,
                    },
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
