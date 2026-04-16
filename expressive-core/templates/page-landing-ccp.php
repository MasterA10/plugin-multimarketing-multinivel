<?php
/**
 * Template Name: Elite Landing Page - CCP Academy
 * Description: Premium Educational/Formation template for CCP Academy.
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
                <span class="text-[8px] md:text-[10px] gold-gradient-text font-bold uppercase outfit">CCP</span>
            </div>
            <span class="text-[8px] md:text-[9px] tracking-[0.4em] font-light uppercase opacity-40 outfit hidden sm:block">Academy PMU Beauty</span>
        </div>
        <?php $hero_btn = get_elite_button('hero', $buttons, 'Quero me tornar Mestre'); ?>
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
                        <span class="text-[#c5a059] text-[10px] uppercase tracking-[0.5em] font-bold">Comunidade CCP Academy</span>
                        <h1 class="text-4xl sm:text-6xl md:text-7xl lg:text-8xl serif leading-[1.1]">
                            Evolua da Iniciação <br>
                            <span class="gold-gradient-text italic font-bold">Ao Nível Mestre</span>
                        </h1>
                        <p class="text-white/60 max-w-2xl mx-auto lg:mx-0 text-base md:text-xl font-light leading-relaxed">
                            Você não entra em um curso. Você entra em uma <span class="text-white font-medium">formação estruturada</span> para construir segurança, autoridade e reconhecimento real.
                        </p>
                    </div>
                    
                    <div class="bg-white/5 border border-white/10 p-6 rounded-2xl inline-block max-w-xl">
                         <p class="text-white/40 text-[11px] md:text-xs uppercase tracking-widest leading-relaxed">
                            Se você deseja viver da micropigmentação com segurança e conquistar a confiança total dos seus clientes, a CCP é o seu próximo passo.
                         </p>
                    </div>

                    <div class="pt-4 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                         <a href="#offer" class="px-10 py-5 bg-[#c5a059] text-black font-bold uppercase tracking-[0.2em] text-[11px] rounded-sm hover:scale-105 transition-all shadow-2xl">
                             Começar minha Jornada
                         </a>
                         <a href="#pillars" class="px-10 py-5 glass border-white/10 text-white font-bold uppercase tracking-[0.2em] text-[11px] rounded-sm hover:bg-white/5 transition-all">
                             Como Funciona
                         </a>
                    </div>
                </div>
                <div class="lg:col-span-5 relative">
                    <div class="aspect-[4/5] rounded-[40px] overflow-hidden shadow-[0_0_80px_rgba(197,160,89,0.15)] skew-y-1">
                        <?php echo render_elite_media('hero', $media); ?>
                    </div>
                    <div class="absolute -bottom-6 -right-6 p-8 glass rounded-3xl border-white/10 bg-black/60 backdrop-blur-2xl hidden md:block">
                         <span class="text-[8px] uppercase tracking-widest text-[#c5a059] block mb-2 font-bold">Formação Exclusiva</span>
                         <p class="text-white text-lg serif italic">"Transformando técnica em autoridade real."</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- A GRANDE DIFERENÇA (INTRO) -->
    <section id="intro" class="section-padding bg-white/[0.01]">
        <div class="container mx-auto px-6 lg:px-24">
            <div class="max-w-4xl mx-auto text-center space-y-16">
                <div class="space-y-6">
                    <h2 class="text-3xl md:text-6xl serif italic gold-gradient-text leading-tight">A maioria aprende técnica. <br>Poucas constroem autoridade.</h2>
                    <div class="w-20 h-px bg-[#c5a059]/40 mx-auto"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 text-left">
                    <div class="space-y-6 glass p-10 rounded-[40px] border-white/5 bg-red-500/5 group">
                        <h4 class="text-white/80 font-bold uppercase tracking-widest text-xs flex items-center gap-3">
                            <span class="w-2 h-2 rounded-full bg-red-400"></span> O Problema Atual
                        </h4>
                        <p class="text-white/40 text-sm leading-relaxed">
                            O mercado está repleto de profissionais que sabem executar apenas o básico. Poucos conseguem se posicionar estrategicamente para gerar diferenciação e confiança real.
                        </p>
                    </div>
                    <div class="space-y-6 glass p-10 rounded-[40px] border-[#c5a059]/30 bg-[#c5a059]/5 group">
                        <h4 class="text-[#c5a059] font-bold uppercase tracking-widest text-xs flex items-center gap-3">
                            <span class="w-2 h-2 rounded-full bg-[#c5a059]"></span> A Solução CCP
                        </h4>
                        <p class="text-white/80 text-sm leading-relaxed">
                            Na CCP, você vai além da técnica. Você constrói uma base sólida, desenvolve domínio completo, ganha visão estratégica e conquista autoridade real no mercado.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- OS 5 PILARES -->
    <section id="pillars" class="section-padding">
        <div class="container mx-auto px-6 lg:px-24 space-y-20">
            <div class="text-center space-y-4">
                <h2 class="text-4xl md:text-7xl serif italic gold-gradient-text leading-tight">Como Funciona a CCP Academy</h2>
                <p class="text-white/30 max-w-2xl mx-auto text-sm md:text-base font-light uppercase tracking-widest">Uma estrutura pensada na sua evolução contínua.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <?php 
                $pillars = [
                    ["Aulas ao Vivo", "Toda segunda-feira, 1h de instrução direta com interação em tempo real."],
                    ["Comunidade Ativa", "Chat exclusivo para networking e troca com colegas e mentores."],
                    ["Biblioteca", "Módulos e aulas gravadas para facilitar seu aprendizado progressivo."],
                    ["Ranking", "Acompanhamento contínuo para você visualizar seu crescimento técnico."],
                    ["Mentorias", "Direcionamento personalizado para acelerar sua jornada e resultados."]
                ];
                $i = 1;
                foreach ($pillars as $p) : ?>
                    <div class="glass p-8 border-white/5 space-y-6 card-hover text-center md:text-left flex flex-col justify-between">
                        <div class="space-y-6">
                            <span class="w-10 h-10 border border-[#c5a059]/30 flex items-center justify-center rounded-full text-[#c5a059] text-xs font-bold mx-auto md:mx-0"><?php echo $i++; ?></span>
                            <h4 class="text-white font-bold uppercase tracking-widest text-[11px] leading-tight"><?php echo $p[0]; ?></h4>
                            <p class="text-white/30 text-[11px] leading-relaxed"><?php echo $p[1]; ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="aspect-[21/9] rounded-[40px] overflow-hidden glass border-white/5 shadow-2xl">
                 <?php echo render_elite_media('pillars', $media); ?>
            </div>
        </div>
    </section>

    <!-- COMPARATIVO FREE VS PREMIUM -->
    <section id="comparison" class="section-padding bg-white/[0.01]">
        <div class="container mx-auto px-6 lg:px-24">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                <div class="space-y-10">
                    <div class="space-y-6">
                        <h2 class="text-4xl md:text-6xl serif gold-gradient-text italic">Escolha o seu Nível de Acesso</h2>
                        <p class="text-white/40 text-base leading-relaxed">Você pode começar agora na comunidade gratuita ou garantir o acesso completo à formação Premium.</p>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex items-center gap-4 group">
                            <div class="w-2 h-2 bg-[#c5a059] rounded-full group-hover:scale-150 transition-all"></div>
                            <span class="text-white/60 text-xs uppercase tracking-widest">Instrução Técnica Plena</span>
                        </div>
                        <div class="flex items-center gap-4 group">
                            <div class="w-2 h-2 bg-[#c5a059] rounded-full group-hover:scale-150 transition-all"></div>
                            <span class="text-white/60 text-xs uppercase tracking-widest">Correção Técnica Individual</span>
                        </div>
                        <div class="flex items-center gap-4 group">
                            <div class="w-2 h-2 bg-[#c5a059] rounded-full group-hover:scale-150 transition-all"></div>
                            <span class="text-white/60 text-xs uppercase tracking-widest">Certificação Oficial por Módulos</span>
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- FREE -->
                    <div class="glass p-10 border-white/5 space-y-8 rounded-[40px] bg-white/[0.02]">
                        <div class="text-center space-y-2">
                             <h5 class="text-white/40 text-[10px] uppercase tracking-[0.3em] font-bold">Comunidade</h5>
                             <p class="text-2xl outfit font-medium text-white italic">Free</p>
                        </div>
                        <ul class="space-y-4 text-[11px] text-white/30 uppercase tracking-widest font-light">
                            <li>• Chat Básico</li>
                            <li>• Calendário Oficial</li>
                            <li>• Ranking Participação</li>
                            <li>• Info temas aulas</li>
                        </ul>
                    </div>
                    <!-- PREMIUM -->
                    <div class="glass p-10 border-[#c5a059]/30 space-y-8 rounded-[40px] bg-[#c5a059]/5 scale-105 shadow-[0_0_50px_rgba(197,160,89,0.1)]">
                         <div class="text-center space-y-2">
                             <h5 class="text-[#c5a059] text-[10px] uppercase tracking-[0.3em] font-bold">Formação Plena</h5>
                             <p class="text-2xl outfit font-medium text-[#c5a059] italic">Premium</p>
                        </div>
                        <ul class="space-y-4 text-[11px] text-white/80 uppercase tracking-widest font-medium">
                            <li class="text-[#c5a059]">★ Aulas ao vivo + Gravadas</li>
                            <li class="text-[#c5a059]">★ Correção Técnica</li>
                            <li class="text-[#c5a059]">★ Certificação Oficial</li>
                            <li class="text-[#c5a059]">★ Materiais de Apoio</li>
                            <li class="text-[#c5a059]">★ Mentorias Inclusas</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- MÉTODO E REGRAS -->
    <section id="method" class="section-padding">
        <div class="container mx-auto px-6 lg:px-24">
             <div class="glass p-12 md:p-24 rounded-[60px] border-white/10 relative overflow-hidden">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                    <div class="space-y-12">
                        <div class="space-y-4">
                            <span class="text-[#c5a059] text-[10px] uppercase tracking-[0.5em] font-bold">A Regra do Jogo</span>
                            <h2 class="text-4xl md:text-6xl serif gold-gradient-text leading-tight">Evolução Real <br>& Compromisso</h2>
                            <p class="text-white/60 font-light text-lg">"Aqui não existe consumo passivo. Existe evolução através da prática."</p>
                        </div>
                        
                        <div class="grid gap-8">
                             <div class="flex gap-6 items-start">
                                <div class="w-10 h-10 rounded-xl bg-[#c5a059]/20 flex items-center justify-center shrink-0 text-[#c5a059]">✓</div>
                                <div>
                                    <h4 class="text-white font-bold uppercase tracking-widest text-xs">Avaliação Técnica</h4>
                                    <p class="text-white/40 text-xs mt-1 leading-relaxed">Seu desempenho é medido através de critérios objetivos para garantir que você está absorvendo o método.</p>
                                </div>
                             </div>
                             <div class="flex gap-6 items-start">
                                <div class="w-10 h-10 rounded-xl bg-[#c5a059]/20 flex items-center justify-center shrink-0 text-[#c5a059]">✓</div>
                                <div>
                                    <h4 class="text-white font-bold uppercase tracking-widest text-xs">Presença Mínima</h4>
                                    <p class="text-white/40 text-xs mt-1 leading-relaxed">É necessária a presença mínima de 75% nas aulas ao vivo para garantir a qualidade do aprendizado e certificação.</p>
                                </div>
                             </div>
                        </div>
                    </div>
                    <div class="aspect-square rounded-[40px] overflow-hidden border border-white/5">
                        <?php echo render_elite_media('method', $media); ?>
                    </div>
                </div>
             </div>
        </div>
    </section>

    <!-- DIFERENCIAIS E CERTIFICAÇÃO -->
    <section id="differentials" class="section-padding bg-black py-24 md:py-32">
        <div class="container mx-auto px-6 lg:px-24 grid grid-cols-1 lg:grid-cols-12 gap-16 items-start">
            <div class="lg:col-span-5 space-y-12">
                <div class="space-y-6 text-center lg:text-left">
                    <h2 class="text-4xl md:text-6xl serif italic gold-gradient-text leading-tight">Certificação <br>que Transforma</h2>
                    <p class="text-white/40 font-light leading-relaxed">Seu crescimento é pautado em metas claras e validação por desempenho real.</p>
                </div>
                
                <div class="grid grid-cols-1 gap-4">
                     <?php 
                     $diffs = ["Certificados por Módulos", "Validação por Desempenho", "Capacidade para Ministrar", "Carga Horária Acadêmica"];
                     foreach ($diffs as $d) : ?>
                         <div class="p-6 glass border-white/5 flex justify-between items-center group hover:bg-[#c5a059]/5 transition-all">
                             <span class="text-white/80 uppercase text-[10px] tracking-widest font-medium"><?php echo $d; ?></span>
                             <span class="text-[#c5a059]">★</span>
                         </div>
                     <?php endforeach; ?>
                </div>
            </div>
            <div class="lg:col-span-1 hidden lg:block h-full border-l border-white/5 mx-auto"></div>
            <div class="lg:col-span-6 space-y-8">
                <div class="aspect-video rounded-[30px] overflow-hidden">
                    <?php echo render_elite_media('differentials', $media); ?>
                </div>
                <div class="bg-white/[0.02] p-10 rounded-[40px] border border-white/5 space-y-6">
                    <h5 class="text-[#c5a059] text-[10px] uppercase tracking-[0.5em] font-bold">Certificado Anual</h5>
                    <p class="text-white/60 text-sm leading-relaxed serif italic">"Certificado de conclusão com carga horária válida para faculdades e currículo acadêmico, reconhecendo sua trajetória de maestria."</p>
                </div>
            </div>
        </div>
    </section>

    <!-- PARA QUEM É? -->
    <section id="audience" class="section-padding bg-white/[0.01]">
        <div class="container mx-auto px-6 lg:px-24 space-y-20">
             <div class="text-center space-y-4">
                <h2 class="text-4xl md:text-7xl serif italic gold-gradient-text">Para Quem é a CCP?</h2>
                <p class="text-white/30 max-w-2xl mx-auto font-light leading-relaxed">Identifique em qual momento da sua carreira você está e descubra como vamos te elevar.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php 
                $audiences = [
                    ["Iniciantes", "Quem busca uma base sólida e o direcionamento correto desde o primeiro dia."],
                    ["Em Evolução", "Quem já atua, mas busca aperfeiçoamento técnico e melhor posicionamento."],
                    ["Busca Autoridade", "Quem deseja ser reconhecida como referência e ter diferencial competitivo."],
                    ["Futuras Educadoras", "Profissionais que querem se preparar para ensinar e palestrar."]
                ];
                foreach ($audiences as $a) : ?>
                    <div class="glass p-10 border-white/5 space-y-6 card-hover group text-center">
                        <div class="w-14 h-14 rounded-full border border-[#c5a059]/20 flex items-center justify-center text-[#c5a059] group-hover:bg-[#c5a059] group-hover:text-black transition-all mx-auto">★</div>
                        <h4 class="text-white font-bold uppercase tracking-widest text-xs"><?php echo $a[0]; ?></h4>
                        <p class="text-white/30 text-[11px] leading-relaxed italic"><?php echo $a[1]; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- OFERTA E INVESTIMENTO -->
    <section id="offer" class="section-padding bg-black border-y border-white/5">
        <div class="container mx-auto px-6 lg:px-24">
            <div class="max-w-5xl mx-auto glass p-10 md:p-20 rounded-[60px] border-[#c5a059]/30 bg-gradient-to-br from-[#c5a059]/10 to-transparent relative overflow-hidden">
                <div class="absolute top-0 right-0 p-10 hidden md:block opacity-20">
                     <span class="text-[80px] serif italic gold-gradient-text opacity-50 select-none">Elite</span>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div class="space-y-10">
                        <div class="space-y-4">
                            <span class="text-[#c5a059] text-[10px] uppercase tracking-[0.6em] font-bold">Lançamento Exclusivo</span>
                            <h2 class="text-5xl md:text-7xl serif leading-tight">Sua Maestria <br><span class="gold-gradient-text italic font-bold">Começa Aqui</span></h2>
                        </div>
                        
                        <div class="space-y-6">
                            <div class="flex items-baseline gap-4">
                                <span class="text-white/40 text-xl font-light line-through">R$ 59,97</span>
                                <span class="text-6xl md:text-7xl outfit font-medium text-white tracking-tighter">R$ 49,97<span class="text-lg text-white/40">/mês</span></span>
                            </div>
                            <p class="text-[#c5a059] text-[10px] uppercase tracking-widest font-bold">* Valor garantido pelos primeiros 3 meses.</p>
                        </div>

                         <div class="aspect-video relative rounded-3xl overflow-hidden glass border-white/5 shadow-2xl skew-x-1">
                             <?php echo render_elite_media('offer', $media); ?>
                         </div>
                    </div>
                    
                    <div class="space-y-12">
                        <div class="space-y-6">
                            <h4 class="text-white font-bold uppercase tracking-widest text-xs">O que você recebe:</h4>
                            <ul class="text-white/60 text-xs md:text-sm space-y-5 font-light leading-relaxed">
                                <li class="flex gap-4"><span>✨</span> Aulas ao vivo com chat exclusivo</li>
                                <li class="flex gap-4"><span>✨</span> Área de membros personalizada</li>
                                <li class="flex gap-4"><span>✨</span> Correção técnica e suporte VIP</li>
                                <li class="flex gap-4"><span>✨</span> Biblioteca de aulas e PDFs/E-books</li>
                                <li class="flex gap-4"><span>✨</span> Certificações progressivas e anuais</li>
                                <li class="flex gap-4"><span>✨</span> Mentorias e comunidade premium</li>
                            </ul>
                        </div>

                        <?php $offer_btn = get_elite_button('offer', $buttons, 'Quero me tornar Mestre em Micropigmentação'); ?>
                        <a href="<?php echo esc_url($offer_btn['url']); ?>" class="block w-full text-center py-6 bg-[#c5a059] text-black font-bold uppercase tracking-[0.2em] text-xs hover:bg-white transition-all rounded-xl shadow-[0_20px_50px_rgba(197,160,89,0.3)]">
                            <?php echo esc_html($offer_btn['label']); ?>
                        </a>
                        
                        <div class="flex justify-center gap-6 opacity-40 grayscale">
                             <span class="text-[9px] uppercase tracking-widest">Visa</span>
                             <span class="text-[9px] uppercase tracking-widest">Master</span>
                             <span class="text-[9px] uppercase tracking-widest">Pix</span>
                             <span class="text-[9px] uppercase tracking-widest">Boleto</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CHAMADA FINAL (CTA) -->
    <section id="conclusion" class="min-h-screen flex items-center relative">
        <div class="container mx-auto px-6 lg:px-24">
            <div class="max-w-4xl mx-auto text-center space-y-16">
                 <div class="space-y-8">
                    <h2 class="text-4xl md:text-8xl serif italic leading-tight">
                        Você quer ser mais uma no mercado ou quer se tornar <br>
                        <span class="gold-gradient-text font-bold">Uma Referência?</span>
                    </h2>
                    <p class="text-white/40 max-w-2xl mx-auto text-sm md:text-lg font-light uppercase tracking-[0.4em] leading-relaxed">A CCP Academy é a sua oportunidade de construir autoridade e evoluir tecnicamente.</p>
                 </div>

                 <?php $cta_btn = get_elite_button('cta', $buttons, 'Quero ser Mestre Agora!'); ?>
                 <div class="space-y-8">
                    <a href="<?php echo esc_url($cta_btn['url']); ?>" class="inline-block px-12 md:px-24 py-6 md:py-8 bg-white text-black font-bold uppercase tracking-[0.3em] text-xs md:text-sm rounded-full hover:bg-[#c5a059] hover:text-white transition-all shadow-[0_0_80px_rgba(255,255,255,0.15)]">
                        <?php echo esc_html($cta_btn['label']); ?>
                    </a>
                    
                    <div class="pt-8">
                         <p class="text-[10px] uppercase tracking-[0.5em] text-white/30">Posicione-se como uma profissional de elite.</p>
                    </div>
                 </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="py-20 px-6 md:px-24 flex flex-col md:flex-row justify-between items-center opacity-30 border-t border-white/5 space-y-6 md:space-y-0">
        <div class="text-center md:text-left">
            <p class="text-[8px] md:text-[9px] uppercase tracking-[0.4em] outfit">© 2026 CCP Academy PMU Beauty</p>
            <p class="text-[8px] uppercase tracking-[0.4em] mt-2">Educação e Formação Mestre</p>
        </div>
        <div class="flex gap-8 italic font-light text-[9px] uppercase tracking-widest">
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

</body>
</html>
