<?php
/**
 * Template Name: Academia PMU - Equipe
 * Description: Template luxuoso para exibição da diretoria e educadores da Academia PMU Beauty.
 */

if ( ! isset( $is_shortcode ) || ! $is_shortcode ) {
    ?>
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Equipe Academia - CCP Academy Beauty</title>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&family=Playfair+Display:ital,wght@0,700;1,700&display=swap" rel="stylesheet">
        <?php wp_head(); ?>
    </head>
    <body <?php body_class(); ?>>
    <?php
}

// Fetch all members
$args = array(
    'post_type'      => 'academy_member',
    'posts_per_page' => -1,
    'status'         => 'publish'
);
$query = new WP_Query($args);
$members_by_tier = array(
    'lideranca'    => array(),
    'grand_master' => array(),
    'convidado'    => array()
);

if ($query->have_posts()) {
    while ($query->have_posts()) {
        $query->the_post();
        $tier = get_post_meta(get_the_ID(), '_academy_member_tier', true) ?: 'convidado';
        
        // Normalize old grandmaster slug if it exists
        if ($tier === 'grandmaster') $tier = 'grand_master';

        if (isset($members_by_tier[$tier])) {
            $members_by_tier[$tier][] = array(
                'name'       => get_the_title(),
                'photo'      => get_the_post_thumbnail_url(get_the_ID(), 'large') ?: 'https://via.placeholder.com/400x400?text=Sem+Foto',
                'role'       => get_post_meta(get_the_ID(), '_academy_member_role', true),
                'background' => get_post_meta(get_the_ID(), '_academy_member_background', true),
                'instagram'  => get_post_meta(get_the_ID(), '_academy_member_instagram', true),
                'description'=> get_the_content(),
            );
        }
    }
    wp_reset_postdata();
}

// Section Config
$sections = array(
    'lideranca'    => 'Direção e Liderança',
    'grand_master' => 'Educadores Grand Master Diamantes',
    'convidado'    => 'Educadores Convidados'
);
?>

<div class="expressive-academy-team-page">
    <style>
        :root {
            --elite-gold: #D4AF37;
            --elite-gold-light: #F2D480;
            --elite-black: #0a0a0a;
            --elite-dark: #111111;
            --elite-silver: #C0C0C0;
        }

        .expressive-academy-team-page {
            background-color: var(--elite-black);
            color: #fff;
            padding: 80px 20px;
            font-family: 'Outfit', sans-serif;
            min-height: 100vh;
        }

        .academy-container {
            max-width: 1300px;
            margin: 0 auto;
        }

        .academy-header {
            text-align: center;
            margin-bottom: 100px;
        }

        .academy-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 4rem;
            color: var(--elite-gold);
            margin-bottom: 15px;
            letter-spacing: -1px;
            font-weight: 700;
        }

        .academy-header p {
            color: #888;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 6px;
            max-width: 600px;
            margin: 0 auto;
        }

        .academy-section {
            margin-bottom: 120px;
        }

        .section-title {
            text-align: center;
            margin-bottom: 60px;
            position: relative;
        }

        .section-title h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            display: inline-block;
            background: linear-gradient(to right, var(--elite-gold), var(--elite-gold-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
            font-style: italic;
        }

        .section-title::after {
            content: '';
            display: block;
            width: 100px;
            height: 1px;
            background: linear-gradient(to right, transparent, var(--elite-gold), transparent);
            margin: 20px auto 0;
        }

        .academy-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 40px;
        }

        .member-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
        }

        .member-card {
            background: var(--elite-dark);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 24px;
            overflow: hidden;
            transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .member-card:hover {
            transform: translateY(-10px);
            border-color: var(--elite-gold);
            box-shadow: 0 20px 50px rgba(212, 175, 55, 0.15);
            z-index: 10;
        }

        /* LIDERANÇA TIER: Majestic Circular Design */
        .tier-lideranca .member-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
            margin: 0 auto;
            position: relative;
        }

        @media (max-width: 1200px) {
            .member-grid, .tier-lideranca .member-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .member-grid, .tier-lideranca .member-grid {
                grid-template-columns: 1fr;
            }
            .academy-container {
                padding: 0 20px;
            }
        }

        .tier-lideranca .member-card {
            border: 1px solid rgba(212, 175, 55, 0.2);
            background: linear-gradient(135deg, #0d0d0d 0%, #050505 100%);
            box-shadow: 0 0 50px rgba(0,0,0,0.8);
            z-index: 2;
            text-align: center;
        }

        .tier-lideranca .member-photo-wrap {
            width: 250px;
            height: 250px;
            margin: 80px auto 40px;
            padding-top: 0;
            border-radius: 50%;
            position: relative;
            z-index: 2;
            overflow: visible !important; /* Prevent crown clipping */
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Spinning Gold Stoke (Thinner & Refined) */
        .tier-lideranca .member-photo-wrap::before {
            content: '';
            position: absolute;
            top: -6px; left: -6px; right: -6px; bottom: -6px;
            background: conic-gradient(from 0deg, 
                transparent 20%, 
                var(--elite-gold) 45%, 
                var(--elite-gold-light) 50%, 
                var(--elite-gold) 55%, 
                transparent 80%
            );
            border-radius: 50%;
            animation: rotate-gold 2.5s linear infinite;
            z-index: -1;
        }

        @keyframes rotate-gold {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Stroke Mask / Inner Ring */
        .tier-lideranca .member-photo-wrap::after {
            content: '';
            position: absolute;
            top: -3px; left: -3px; right: -3px; bottom: -3px;
            background: #000;
            border-radius: 50%;
            z-index: -1;
        }

        .tier-lideranca .member-photo {
            width: 100% !important;
            height: 100% !important;
            position: relative !important;
            border-radius: 50%;
            filter: grayscale(0%);
            border: 4px solid #000;
            object-fit: cover;
            display: block;
        }

        /* Imposing Crown over Photo */
        .majestic-crown-wrap {
            position: absolute;
            top: -45px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            z-index: 10;
            filter: drop-shadow(0 5px 15px rgba(0,0,0,0.5));
            animation: crown-float 3s ease-in-out infinite alternate;
        }

        @keyframes crown-float {
            from { transform: translateX(-50%) translateY(0) scale(1.1); }
            to { transform: translateX(-50%) translateY(-10px) scale(1.15); }
        }

        .majestic-crown-svg {
            width: 100%;
            height: auto;
            fill: url(#gold-gradient);
        }

        .member-card:hover {
            transform: translateY(-15px);
            border-color: var(--elite-gold-light);
            box-shadow: 0 40px 80px rgba(212, 175, 55, 0.3);
            z-index: 10;
        }
        
        /* Centralizar info na liderança */
        .tier-lideranca .member-info {
            padding: 30px 50px 50px;
            align-items: center;
            background: transparent;
        }

        .tier-lideranca .member-instagram a {
            justify-content: center;
        }

        .tier-lideranca .member-background {
            border-left: none;
            border-top: 1px solid var(--elite-gold);
            padding-left: 0;
            padding-top: 20px;
            text-align: center;
        }
        
        /* General styles for other tiers still apply or are reset */
        .member-photo-wrap {
            position: relative;
            padding-top: 100%;
            overflow: hidden;
        }
        
        .member-photo {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            object-fit: cover;
            filter: grayscale(100%) contrast(1.1);
            transition: 1s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .member-card:hover .member-photo {
            filter: grayscale(0%) contrast(1);
            transform: scale(1.1);
        }

        .tier-badge {
            position: absolute;
            top: 25px;
            right: 25px;
            background: rgba(0,0,0,0.8);
            backdrop-filter: blur(10px);
            padding: 10px 20px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: var(--elite-gold);
            border: 1px solid var(--elite-gold);
            z-index: 5;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .tier-lideranca .tier-badge {
            display: none; /* Removed as crown and circle already show status */
        }

        .tier-lideranca .tier-badge {
            background: linear-gradient(to right, var(--elite-gold), var(--elite-gold-light));
            color: #000;
            box-shadow: 0 0 30px rgba(212, 175, 55, 0.6);
            border: none;
        }

        .crown-icon {
            width: 18px;
            height: 18px;
            fill: currentColor;
        }

        .member-info {
            padding: 50px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            background: linear-gradient(to bottom, transparent, rgba(0,0,0,0.6));
        }

        .member-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: #fff;
            margin: 0 0 10px 0;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .tier-lideranca .member-name {
            font-size: 2.5rem;
            background: linear-gradient(to right, #fff, var(--elite-gold-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }

        .member-role {
            color: var(--elite-gold);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 2px;
            margin-bottom: 20px;
        }

        .member-instagram {
            margin-bottom: 25px;
        }

        .member-instagram a {
            color: #fff;
            text-decoration: none;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            opacity: 0.6;
            transition: 0.3s;
            border: 1px solid rgba(255,255,255,0.1);
            padding: 6px 15px;
            border-radius: 30px;
            background: rgba(255,255,255,0.03);
        }

        .member-instagram a:hover {
            color: var(--elite-gold);
            opacity: 1;
            border-color: var(--elite-gold);
            background: rgba(212, 175, 55, 0.05);
        }

        .member-background {
            font-size: 0.85rem;
            color: #ccc;
            margin-bottom: 25px;
            font-style: italic;
            border-left: 1px solid var(--elite-gold);
            padding-left: 20px;
            line-height: 1.6;
        }

        .member-description {
            font-size: 0.95rem;
            line-height: 1.8;
            color: #aaa;
        }

        /* CONVIDADO TIER: Simpler */
        .tier-convidado .member-card {
            border-color: rgba(255,255,255,0.03);
            background: transparent;
        }
        .tier-convidado .tier-badge {
            color: var(--elite-silver);
            border-color: var(--elite-silver);
        }

        @media (max-width: 768px) {
            .academy-header h1 { font-size: 2.5rem; }
            .member-grid, .tier-lideranca .member-grid { 
                grid-template-columns: 1fr; 
                max-width: 100%;
            }
            .tier-lideranca .member-name { font-size: 1.8rem; }
            .tier-lideranca .member-photo-wrap {
                width: 200px;
                height: 200px;
            }
        }
    </style>

    <div class="academy-container">
        <!-- SVG Gradient Definitions -->
        <svg width="0" height="0" style="position:absolute">
            <defs>
                <linearGradient id="gold-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" style="stop-color:#D4AF37;stop-opacity:1" />
                    <stop offset="50%" style="stop-color:#F2D480;stop-opacity:1" />
                    <stop offset="100%" style="stop-color:#D4AF37;stop-opacity:1" />
                </linearGradient>
            </defs>
        </svg>

        <header class="academy-header">
            <h1>Academia PMU Beauty</h1>
            <p>A maior e mais exclusiva rede de educadores em Micropigmentação.</p>
        </header>

        <?php foreach ($sections as $tier_id => $section_title) : ?>
            <?php if (!empty($members_by_tier[$tier_id])) : ?>
                <section class="academy-section tier-<?php echo $tier_id; ?>">
                    <div class="section-title">
                        <h2><?php echo esc_html($section_title); ?></h2>
                    </div>
                    
                    <div class="member-grid">
                        <?php foreach ($members_by_tier[$tier_id] as $member) : ?>
                            <article class="member-card">
                                <?php if ($tier_id !== 'lideranca') : ?>
                                    <div class="tier-badge">
                                        <?php 
                                            if ($tier_id === 'grand_master') echo 'MASTER ELITE';
                                            else echo 'CONVIDADO';
                                        ?>
                                    </div>
                                <?php endif; ?>

                                <div class="member-photo-wrap">
                                    <?php if ($tier_id === 'lideranca') : ?>
                                        <!-- Imposing Crown for Queens -->
                                        <div class="majestic-crown-wrap">
                                            <svg class="majestic-crown-svg" viewBox="0 0 100 80">
                                                <path d="M10 70 L5 20 L30 45 L50 5 L70 45 L95 20 L90 70 Z" />
                                                <circle cx="50" cy="5" r="4" fill="#F2D480" />
                                                <circle cx="5" cy="20" r="3" fill="#F2D480" />
                                                <circle cx="95" cy="20" r="3" fill="#F2D480" />
                                                <rect x="10" y="70" width="80" height="8" rx="2" fill="#F2D480" />
                                                <circle cx="30" cy="74" r="2" fill="#000" />
                                                <circle cx="50" cy="74" r="2" fill="#000" />
                                                <circle cx="70" cy="74" r="2" fill="#000" />
                                            </svg>
                                        </div>
                                    <?php endif; ?>

                                    <img src="<?php echo esc_url($member['photo']); ?>" alt="<?php echo esc_attr($member['name']); ?>" class="member-photo" loading="lazy">
                                </div>

                                <div class="member-info">
                                    <h3 class="member-name"><?php echo esc_html($member['name']); ?></h3>
                                    <div class="member-role"><?php echo esc_html($member['role']); ?></div>
                                    
                                    <?php if (!empty($member['instagram'])) : ?>
                                        <div class="member-instagram">
                                            <a href="https://instagram.com/<?php echo esc_attr($member['instagram']); ?>" target="_blank" rel="noopener noreferrer">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true" style="width: 16px; height: 16px;">
                                                    <path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 a4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" />
                                                </svg> @<?php echo esc_html($member['instagram']); ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($member['background'])) : ?>
                                        <div class="member-background">
                                            <?php echo nl2br(esc_html($member['background'])); ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="member-description">
                                        <?php echo wp_kses_post($member['description']); ?>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if (empty($members_by_tier['lideranca']) && empty($members_by_tier['grand_master']) && empty($members_by_tier['convidado'])) : ?>
            <div style="text-align: center; padding: 100px 0; color: #555;">
                <p>Nenhum membro cadastrado ainda.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php 
if ( ! isset( $is_shortcode ) || ! $is_shortcode ) {
    wp_footer();
    ?>
    </body>
    </html>
    <?php
}
?>
