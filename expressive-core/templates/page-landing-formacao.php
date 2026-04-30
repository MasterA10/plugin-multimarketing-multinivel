<?php
/**
 * Template Name: Elite Landing Page - Formação
 * Description: Premium Educational/Formation template for Micropigmentação e Estética Facial.
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
        return '<div class="w-full h-full bg-white/5 border border-white/10 rounded-[30px] flex items-center justify-center italic text-white/20 text-[10px]">Aguardando Mídia...</div>';
    }

    if ($mode === 'carousel' && count($ids) > 1) {
        $html = '<div class="swiper elite-swiper-' . $section_key . ' rounded-[30px] overflow-hidden border border-white/10 w-full h-full">';
        $html .= '<div class="swiper-wrapper">';
        foreach ($ids as $id) {
            $url = wp_get_attachment_url($id);
            $html .= '<div class="swiper-slide"><img src="' . esc_url($url) . '" class="w-full h-full object-cover"></div>';
        }
        $html .= '</div><div class="swiper-pagination"></div></div>';
        return $html;
    } else {
        $url = wp_get_attachment_url($ids[0]);
        return '<img src="' . esc_url($url) . '" class="w-full h-full object-cover rounded-[30px] border border-white/10 shadow-xl">';
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
        body { background-color: #000; color: #fff; font-family: 'Inter', sans-serif; letter-spacing: -0.01em; scroll-behavior: smooth; overflow-x: hidden; }
        .serif { font-family: 'Cormorant Garamond', serif; }
        .outfit { font-family: 'Outfit', sans-serif; }
        .bg-wrapper { position: fixed; inset: 0; z-index: -1; background: #000; }
        .mesh { position: absolute; width: 100%; height: 100%; background: radial-gradient(circle at 10% 10%, rgba(197, 160, 89, 0.1) 0%, transparent 40%), radial-gradient(circle at 90% 80%, rgba(255, 255, 255, 0.05) 0%, transparent 40%); filter: blur(80px); }
        .noise { position: fixed; inset: 0; z-index: -1; opacity: 0.05; pointer-events: none; background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E"); }
        .glass { background: var(--glass-bg); backdrop-filter: blur(20px); border: 1px solid var(--glass-border); }
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

        .section-padding { padding: 60px 0; }
        @media (min-width: 1024px) { .section-padding { padding: 100px 0; } }
        .card-hover { transition: all 0.4s ease; }
        .card-hover:hover { transform: translateY(-5px); border-color: var(--gold); background: rgba(197, 160, 89, 0.03); }
        .swiper-pagination-bullet-active { background: var(--gold) !important; }
    </style>
</head>
<body>

    <div class="bg-wrapper"><div class="mesh"></div><div class="noise"></div></div>

    <!-- Navegação -->
    <nav class="fixed w-full z-50 py-3 md:py-6 px-4 md:px-24 flex justify-between items-center backdrop-blur-md bg-black/40 border-b border-white/5">
        <div class="flex items-center gap-4">
            <div class="w-8 h-8 md:w-10 md:h-10 border border-[#c5a059]/40 flex items-center justify-center rounded-sm">
                <span class="text-[8px] md:text-[10px] gold-gradient-text font-bold uppercase outfit">Elite</span>
            </div>
            <span class="text-[8px] md:text-[9px] tracking-[0.4em] font-light uppercase opacity-40 outfit hidden sm:block">Academy</span>
        </div>
        <?php $hero_btn = get_elite_button('hero', $buttons, 'Quero me tornar uma Autoridade'); ?>
        <a href="<?php echo esc_url($hero_btn['url']); ?>" class="text-[8px] md:text-[9px] uppercase tracking-[0.15em] md:tracking-[0.3em] font-bold bg-[#c5a059] text-black px-4 md:px-6 py-2 md:py-3 hover:bg-[#e2c275] transition-all rounded-sm shadow-lg">
            Garantir Vaga
        </a>
    </nav>

    <!-- HERO SECTION -->
    <section class="min-h-screen flex items-center pt-24 md:pt-32">
        <div class="container mx-auto px-6 lg:px-24">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                <div class="lg:col-span-7 space-y-8 text-center lg:text-left">
                    <div class="space-y-4">
                        <span class="text-[#c5a059] text-[10px] uppercase tracking-[0.5em] font-bold">Com Cátia Araújo, Cley Fernandes e Paty Batista</span>
                        <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl serif leading-[1.1]">
                            Formação Completa em<br>
                            <span class="gold-gradient-text italic font-bold">Micropigmentação & Estética Facial</span>
                        </h1>
                        <p class="text-white/60 max-w-2xl mx-auto lg:mx-0 text-base md:text-xl font-light leading-relaxed">
                            Mentoras Elite Royal | Gran Master Diamante da Expressive Kollors | Educadoras da PMU Barber
                        </p>
                    </div>

                    <div class="pt-4 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                         <a href="<?php echo esc_url($hero_btn['url']); ?>" class="px-10 py-5 bg-[#c5a059] text-black font-bold uppercase tracking-[0.2em] text-[11px] rounded-sm hover:scale-105 transition-all shadow-2xl">
                             <?php echo esc_html($hero_btn['label']); ?>
                         </a>
                    </div>
                </div>
                <div class="lg:col-span-5 relative">
                    <div class="aspect-[4/5] rounded-[40px] overflow-hidden shadow-[0_0_80px_rgba(197,160,89,0.15)] skew-y-1">
                        <?php echo render_elite_media('hero', $media); ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- INTRODUÇÃO E AUTORIDADE -->
    <section id="intro" class="section-padding bg-white/[0.01]">
        <div class="container mx-auto px-6 lg:px-24">
            <div class="max-w-4xl mx-auto text-center space-y-16">
                <div class="space-y-6">
                    <h2 class="text-3xl md:text-6xl serif italic gold-gradient-text leading-tight">Bem-vindo ao próximo nível da sua carreira</h2>
                    <div class="w-20 h-px bg-[#c5a059]/40 mx-auto"></div>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center text-left">
                    <div class="aspect-square rounded-[40px] overflow-hidden border border-white/5">
                        <?php echo render_elite_media('intro', $media); ?>
                    </div>
                    <div class="space-y-6">
                        <p class="text-white/60 text-lg leading-relaxed">
                            As <span class="text-white font-medium">"Mentoras Elite Royal"</span> serão suas guias nesta jornada de excelência. Com reconhecimento internacional e técnicas exclusivas, elas prepararão você para dominar as mais avançadas práticas em micropigmentação e estética facial.
                        </p>
                        
                        <div class="space-y-4 pt-4">
                            <h4 class="text-[#c5a059] font-bold uppercase tracking-widest text-xs">O que torna nossa formação única?</h4>
                            <ul class="space-y-4 text-white/60 text-sm font-light">
                                <li><strong class="text-white">Certificação Internacional:</strong> Um diploma que abre portas globalmente nos mais altos padrões da indústria.</li>
                                <li><strong class="text-white">Metodologia Passo a Passo:</strong> Sistema didático comprovado, do básico ao avançado.</li>
                                <li><strong class="text-white">Suporte Direto:</strong> Acesso exclusivo e contínuo para tirar dúvidas.</li>
                                <li><strong class="text-white">Técnicas Exclusivas:</strong> Procedimentos inovadores para os públicos feminino e masculino.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- GRADE CURRICULAR -->
    <section id="grade" class="section-padding">
        <div class="container mx-auto px-6 lg:px-24 space-y-20">
            <div class="text-center space-y-4">
                <h2 class="text-4xl md:text-7xl serif italic gold-gradient-text leading-tight">Grade Curricular</h2>
                <p class="text-white/30 max-w-2xl mx-auto text-sm md:text-base font-light uppercase tracking-widest">Domine cada técnica e torne-se um profissional versátil no mercado de alto padrão.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php 
                $courses = [
                    ["Labial", "Micropigmentação Labial, Micro Labial, Neutralização Labial, Hidra Gloss Lips."],
                    ["Sobrancelhas", "Fio a Fio (Nano Blading), Shadow Brows, Neutralização e Correção, Reconstrução, Design e SPA das Sobrancelhas."],
                    ["Olhos", "Delineado Clássico e Delineado K-Glow Liner."],
                    ["Masculino", "Preenchimento e Reconstrução de Barba, Sobrancelhas Masculinas."],
                    ["Capilar", "Terapia Capilar para calvície/alopecia e Micropigmentação Capilar."],
                    ["Estética Facial/Corporal", "Limpeza de Pele, Microagulhamento, Jato de Plasma, Derma Planing, Estrias (Regeneração), Massagem Relaxante e Drenagem Linfática."],
                    ["Remoção", "Despigmentação Química e Mecânica."]
                ];
                foreach ($courses as $c) : ?>
                    <div class="glass p-8 border-white/5 space-y-4 card-hover">
                        <h4 class="text-[#c5a059] font-bold uppercase tracking-widest text-[11px] leading-tight flex items-center gap-2"><span class="w-1.5 h-1.5 bg-[#c5a059] rounded-full"></span> <?php echo $c[0]; ?></h4>
                        <p class="text-white/60 text-sm leading-relaxed"><?php echo $c[1]; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="aspect-[21/9] rounded-[40px] overflow-hidden glass border-white/5 shadow-2xl">
                 <?php echo render_elite_media('grade', $media); ?>
            </div>
        </div>
    </section>

    <!-- DIFERENCIAIS -->
    <section id="diferenciais" class="section-padding bg-black border-y border-white/5">
        <div class="container mx-auto px-6 lg:px-24">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                <div class="space-y-12">
                    <div class="space-y-4">
                        <span class="text-[#c5a059] text-[10px] uppercase tracking-[0.5em] font-bold">Seja parte da Elite</span>
                        <h2 class="text-4xl md:text-6xl serif gold-gradient-text leading-tight">Seus Diferenciais <br>no Mercado</h2>
                    </div>
                    
                    <div class="grid gap-8">
                         <div class="flex gap-6 items-start">
                            <div class="w-10 h-10 rounded-xl bg-[#c5a059]/20 flex items-center justify-center shrink-0 text-[#c5a059]">✓</div>
                            <div>
                                <h4 class="text-white font-bold uppercase tracking-widest text-xs">Selo de Qualidade</h4>
                                <p class="text-white/40 text-xs mt-1 leading-relaxed">Certificação que valida seu conhecimento globalmente.</p>
                            </div>
                         </div>
                         <div class="flex gap-6 items-start">
                            <div class="w-10 h-10 rounded-xl bg-[#c5a059]/20 flex items-center justify-center shrink-0 text-[#c5a059]">✓</div>
                            <div>
                                <h4 class="text-white font-bold uppercase tracking-widest text-xs">Status de Autoridade</h4>
                                <p class="text-white/40 text-xs mt-1 leading-relaxed">Cadastro oficial como autoridade Expressive Kollors ou PMU Barber.</p>
                            </div>
                         </div>
                         <div class="flex gap-6 items-start">
                            <div class="w-10 h-10 rounded-xl bg-[#c5a059]/20 flex items-center justify-center shrink-0 text-[#c5a059]">✓</div>
                            <div>
                                <h4 class="text-white font-bold uppercase tracking-widest text-xs">Visão de Mercado</h4>
                                <p class="text-white/40 text-xs mt-1 leading-relaxed">Conteúdo focado em tendências reais para um futuro próspero.</p>
                            </div>
                         </div>
                    </div>
                </div>
                <div class="aspect-square rounded-[40px] overflow-hidden border border-white/5">
                    <?php echo render_elite_media('diferenciais', $media); ?>
                </div>
            </div>
        </div>
    </section>

    <!-- METODOLOGIA -->
    <section id="metodologia" class="section-padding bg-white/[0.01]">
        <div class="container mx-auto px-6 lg:px-24">
             <div class="text-center space-y-4 mb-16">
                <h2 class="text-4xl md:text-7xl serif italic gold-gradient-text">Sua Jornada de Aprendizado</h2>
                <p class="text-white/30 max-w-2xl mx-auto font-light leading-relaxed">Nossa metodologia garante a sua confiança em todas as etapas.</p>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <?php 
                $methodology = [
                    ["Aulas Teóricas Completas", "Fundamentos, biossegurança e colorimetria."],
                    ["Prática em Pele Sintética", "Precisão e confiança antes do contato real."],
                    ["Modelos Reais", "Aplicação supervisionada para consolidar o domínio."],
                    ["Acompanhamento", "Feedback direto das mentoras e suporte contínuo."]
                ];
                $step = 1;
                foreach ($methodology as $m) : ?>
                    <div class="glass p-10 border-[#c5a059]/20 space-y-6 text-center rounded-[30px] card-hover">
                        <span class="text-5xl serif italic text-[#c5a059] opacity-50 block"><?php echo $step++; ?></span>
                        <h4 class="text-white font-bold uppercase tracking-widest text-xs"><?php echo $m[0]; ?></h4>
                        <p class="text-white/50 text-[11px] leading-relaxed"><?php echo $m[1]; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="mt-12 aspect-[21/9] rounded-[40px] overflow-hidden glass border-white/5 shadow-2xl">
                 <?php echo render_elite_media('metodologia', $media); ?>
            </div>
        </div>
    </section>
    
    <!-- FOCO ESTRATÉGICO -->
    <section id="estrategia" class="section-padding">
        <div class="container mx-auto px-6 lg:px-24">
             <div class="glass p-12 md:p-24 rounded-[60px] border-white/10 relative overflow-hidden bg-gradient-to-tr from-[#c5a059]/5 to-transparent">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                    <div class="aspect-[4/3] rounded-[40px] overflow-hidden border border-white/5">
                        <?php echo render_elite_media('estrategia', $media); ?>
                    </div>
                    <div class="space-y-8">
                        <div class="space-y-4">
                            <span class="text-[#c5a059] text-[10px] uppercase tracking-[0.5em] font-bold">Foco Estratégico</span>
                            <h2 class="text-4xl md:text-5xl serif gold-gradient-text leading-tight">A Ascensão da<br>Estética Masculina</h2>
                            <p class="text-white/80 font-light text-lg">Um mercado que cresce exponencialmente. Não fique de fora dessa revolução e expanda seu portfólio.</p>
                        </div>
                        
                        <ul class="space-y-4 text-white/60 text-sm font-light">
                            <li class="flex gap-4"><span class="text-[#c5a059]">★</span> <strong>Barba:</strong> Arte de desenhar e preencher com naturalidade.</li>
                            <li class="flex gap-4"><span class="text-[#c5a059]">★</span> <strong>Capilar:</strong> Soluções duradouras para calvície.</li>
                            <li class="flex gap-4"><span class="text-[#c5a059]">★</span> <strong>Sobrancelhas:</strong> Realce do olhar masculino com definição.</li>
                        </ul>
                    </div>
                </div>
             </div>
        </div>
    </section>

    <!-- AGENDA DE TURMAS -->
    <section id="agenda" class="section-padding bg-black border-y border-white/5">
        <div class="container mx-auto px-6 lg:px-24 space-y-16">
            <div class="text-center space-y-4">
                <h2 class="text-4xl md:text-7xl serif italic gold-gradient-text">Agenda de Turmas</h2>
                <p class="text-white/30 max-w-2xl mx-auto text-sm md:text-base font-light tracking-widest uppercase">Vagas limitadas para garantir atenção individualizada!</p>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Guarulhos -->
                <div class="glass p-8 rounded-[40px] border-white/10 hover:border-[#c5a059]/30 transition-all">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="text-[#c5a059] text-2xl">📍</span>
                        <h3 class="text-2xl font-bold uppercase tracking-widest text-white">Guarulhos – SP</h3>
                    </div>
                    <p class="text-[10px] uppercase tracking-widest text-white/40 mb-6">Educadoras: Cátia Araújo e Cley Fernandes</p>
                    <ul class="space-y-4 text-sm text-white/70">
                        <li class="flex justify-between border-b border-white/5 pb-2"><span>Barba</span> <span class="text-[#c5a059]">08/Jun e 05/Jul</span></li>
                        <li class="flex justify-between border-b border-white/5 pb-2"><span>Sobrancelhas (Fio a Fio)</span> <span class="text-[#c5a059]">14, 15 e 16/Jun</span></li>
                        <li class="flex justify-between border-b border-white/5 pb-2"><span>Labial</span> <span class="text-[#c5a059]">21, 22, 23/Jun e Jul</span></li>
                        <li class="flex justify-between border-b border-white/5 pb-2"><span>Sobrancelhas (Shadow)</span> <span class="text-[#c5a059]">19, 20 e 21/Jul</span></li>
                    </ul>
                </div>
                
                <!-- Caraguatatuba -->
                <div class="glass p-8 rounded-[40px] border-white/10 hover:border-[#c5a059]/30 transition-all">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="text-[#c5a059] text-2xl">📍</span>
                        <h3 class="text-2xl font-bold uppercase tracking-widest text-white">Caraguatatuba – SP</h3>
                    </div>
                    <p class="text-[10px] uppercase tracking-widest text-white/40 mb-6">Educadoras: Cley Fernandes e Cátia Araújo</p>
                    <ul class="space-y-4 text-sm text-white/70">
                        <li class="flex justify-between border-b border-white/5 pb-2"><span>Labial</span> <span class="text-[#c5a059]">17, 18 e 19/Maio</span></li>
                        <li class="flex justify-between border-b border-white/5 pb-2"><span>Sobrancelhas (Fio a Fio)</span> <span class="text-[#c5a059]">24, 25 e 26/Maio</span></li>
                        <li class="flex justify-between border-b border-white/5 pb-2"><span>Sobrancelhas (Shadow)</span> <span class="text-[#c5a059]">12, 13 e 14/Jul</span></li>
                        <li class="flex justify-between border-b border-white/5 pb-2"><span>Neutralização Labial</span> <span class="text-[#c5a059]">26, 27 e 28/Jul</span></li>
                        <li class="flex justify-between border-b border-white/5 pb-2"><span>Barba</span> <span class="text-[#c5a059]">09/Ago</span></li>
                    </ul>
                </div>
                
                <!-- Goiânia -->
                <div class="glass p-8 rounded-[40px] border-white/10 hover:border-[#c5a059]/30 transition-all">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="text-[#c5a059] text-2xl">📍</span>
                        <h3 class="text-2xl font-bold uppercase tracking-widest text-white">Goiânia – GO</h3>
                    </div>
                    <p class="text-[10px] uppercase tracking-widest text-white/40 mb-6">Educadora: Cley Fernandes</p>
                    <ul class="space-y-4 text-sm text-white/70">
                        <li class="flex justify-between border-b border-white/5 pb-2"><span>Labial</span> <span class="text-[#c5a059]">16, 17 e 18/Ago</span></li>
                        <li class="flex justify-between border-b border-white/5 pb-2"><span>Barba</span> <span class="text-[#c5a059]">19/Ago</span></li>
                        <li class="flex justify-between border-b border-white/5 pb-2"><span>Delineado</span> <span class="text-[#c5a059]">20/Ago</span></li>
                        <li class="flex justify-between border-b border-white/5 pb-2"><span>Sobrancelhas</span> <span class="text-[#c5a059]">23, 24 e 25/Ago</span></li>
                    </ul>
                </div>
                
                <!-- Teutônia -->
                <div class="glass p-8 rounded-[40px] border-white/10 hover:border-[#c5a059]/30 transition-all">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="text-[#c5a059] text-2xl">📍</span>
                        <h3 class="text-2xl font-bold uppercase tracking-widest text-white">Teutônia – RS</h3>
                    </div>
                    <p class="text-[10px] uppercase tracking-widest text-white/40 mb-6">Educadora: Paty Batista</p>
                    <ul class="space-y-4 text-sm text-white/70">
                        <li class="flex justify-between border-b border-white/5 pb-2"><span>Labial</span> <span class="text-[#c5a059]">12, 13 e 14/Jul</span></li>
                        <li class="flex justify-between border-b border-white/5 pb-2"><span>Reconstrução de Sobrancelhas</span> <span class="text-[#c5a059]">10/Ago</span></li>
                    </ul>
                </div>
            </div>
            
            <div class="aspect-video relative rounded-3xl overflow-hidden glass border-white/5 mt-12">
                 <?php echo render_elite_media('agenda', $media); ?>
            </div>
        </div>
    </section>

    <!-- PROVA SOCIAL E RESULTADOS -->
    <section id="prova_social" class="section-padding bg-white/[0.01]">
        <div class="container mx-auto px-6 lg:px-24">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-16 items-center">
                <div class="lg:col-span-6 space-y-12 text-center lg:text-left">
                    <div class="space-y-4">
                        <h2 class="text-4xl md:text-6xl serif gold-gradient-text italic">Histórias Reais, <br>Resultados Reais</h2>
                    </div>
                    
                    <div class="glass p-10 rounded-[40px] border-[#c5a059]/30 bg-[#c5a059]/5 relative">
                        <span class="text-[#c5a059] text-6xl font-serif absolute -top-4 -left-2 opacity-50">"</span>
                        <p class="text-white/80 italic text-lg leading-relaxed mt-4 relative z-10">
                            A formação foi um divisor de águas. As técnicas e o suporte me deram confiança para o mercado de luxo.
                        </p>
                        <p class="text-white/40 text-xs uppercase tracking-widest mt-6">— Aluno Formado</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-white/10">
                        <div>
                            <span class="text-[#c5a059] text-4xl outfit font-bold block mb-2">98%</span>
                            <span class="text-[9px] uppercase tracking-widest text-white/40">Satisfação dos alunos</span>
                        </div>
                        <div>
                            <span class="text-[#c5a059] text-4xl outfit font-bold block mb-2">+500</span>
                            <span class="text-[9px] uppercase tracking-widest text-white/40">Profissionais formados</span>
                        </div>
                        <div>
                            <span class="text-[#c5a059] text-4xl outfit font-bold block mb-2">+40%</span>
                            <span class="text-[9px] uppercase tracking-widest text-white/40">Aumento médio de renda</span>
                        </div>
                    </div>
                </div>
                
                <div class="lg:col-span-6">
                    <div class="aspect-[4/5] rounded-[40px] overflow-hidden glass border-white/5 shadow-2xl">
                         <?php echo render_elite_media('prova_social', $media); ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CHAMADA FINAL (CTA) -->
    <section id="cta" class="min-h-screen flex items-center relative bg-black">
        <div class="container mx-auto px-6 lg:px-24">
            <div class="max-w-4xl mx-auto text-center space-y-16">
                 <div class="space-y-8">
                    <h2 class="text-4xl md:text-7xl serif italic leading-tight">
                        <span class="gold-gradient-text font-bold">Transforme sua carreira</span> <br>
                        na estética de alto padrão.
                    </h2>
                    <p class="text-white/60 max-w-2xl mx-auto text-sm md:text-lg font-light leading-relaxed">O futuro da sua carreira começa aqui. Aproveite as últimas vagas.</p>
                 </div>

                 <?php $cta_btn = get_elite_button('cta', $buttons, 'Garantir minha vaga agora'); ?>
                 <div class="space-y-8">
                    <a href="<?php echo esc_url($cta_btn['url']); ?>" class="inline-block px-12 md:px-24 py-6 md:py-8 bg-[#c5a059] text-black font-bold uppercase tracking-[0.2em] text-xs md:text-sm rounded-full hover:bg-white transition-all shadow-[0_0_80px_rgba(197,160,89,0.3)] hover:shadow-[0_0_100px_rgba(255,255,255,0.4)]">
                        <?php echo esc_html($cta_btn['label']); ?>
                    </a>
                 </div>
                 
                 <div class="aspect-[21/9] rounded-[40px] overflow-hidden glass border-white/5 shadow-2xl max-h-64 mx-auto w-full max-w-2xl mt-12">
                     <?php echo render_elite_media('cta', $media); ?>
                 </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="py-12 px-6 md:px-24 flex flex-col md:flex-row justify-between items-center opacity-40 border-t border-white/5 space-y-6 md:space-y-0 text-center md:text-left">
        <div>
            <p class="text-[9px] uppercase tracking-[0.4em] outfit mb-2">© <?php echo date('Y'); ?> Elite Academy</p>
            <p class="text-[8px] uppercase tracking-[0.4em]">Formação Completa em Micropigmentação</p>
        </div>
        <div class="flex justify-center gap-8 italic font-light text-[9px] uppercase tracking-widest">
            <span>By Elite LMS</span>
            <span>Premium Formation</span>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const swiperContainers = document.querySelectorAll('.swiper');
            swiperContainers.forEach(container => {
                new Swiper(container, {
                    loop: true,
                    autoplay: { delay: 4000, disableOnInteraction: false },
                    speed: 1000,
                    pagination: { el: '.swiper-pagination', clickable: true },
                });
            });
        });
    </script>
    <?php wp_footer(); ?>
</body>
</html>
