<?php
/**
 * Frontend Template: Elite Link Hub (Link-in-Bio)
 * Optimized for Mobile
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $post;
$post_id = $post->ID;

// Meta Data
$bio_title = get_post_meta($post_id, '_lms_bio_title', true) ?: get_the_title();
$bio_subtitle = get_post_meta($post_id, '_lms_bio_subtitle', true);
$photo_id = get_post_meta($post_id, '_lms_bio_photo', true);
$show_crown = get_post_meta($post_id, '_lms_bio_show_crown', true);
$links = get_post_meta($post_id, '_lms_bio_links', true) ?: array();

$photo_url = $photo_id ? wp_get_attachment_image_url($photo_id, 'large') : '';
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <title><?php echo esc_html($bio_title); ?> | Link Hub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Playfair+Display:ital,wght@1,400;1,700&display=swap" rel="stylesheet">
    <style>
        :root {
            --elite-gold: #D4AF37;
            --elite-gold-light: #F2D480;
            --elite-dark: #050505;
            --elite-black: #000000;
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(212, 175, 55, 0.15);
        }

        /* Aggressive Reset */
        html, body, div, span, applet, object, iframe, h1, h2, h3, h4, h5, h6, p, blockquote, pre, a, abbr, acronym, address, big, cite, code, del, dfn, em, img, ins, kbd, q, s, samp, small, strike, strong, sub, sup, tt, var, b, u, i, center, dl, dt, dd, ol, ul, li, fieldset, form, label, legend, table, caption, tbody, tfoot, thead, tr, th, td, article, aside, canvas, details, embed, figure, figcaption, footer, header, hgroup, menu, nav, output, ruby, section, summary, time, mark, audio, video {
            margin: 0; padding: 0; border: 0; font-size: 100%; font: inherit; vertical-align: baseline;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        html {
            background: var(--elite-black) !important;
        }

        body {
            background-color: var(--elite-black) !important;
            background-image: 
                radial-gradient(circle at 50% -20%, rgba(212, 175, 55, 0.15), transparent 80%),
                url('data:image/svg+xml,%3Csvg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg"%3%3Cfilter id="noiseFilter"%3%3CfeTurbulence type="fractalNoise" baseFrequency="0.65" numOctaves="3" stitchTiles="stitch"/%3%3C/filter%3%3Crect width="100%25" height="100%25" filter="url(%23noiseFilter)"/%3%3C/svg%3%3E') !important;
            background-attachment: fixed !important;
            color: #fff !important;
            font-family: 'Outfit', sans-serif !important;
            min-height: 100vh !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            overflow-x: hidden !important;
            padding-bottom: 60px !important;
            line-height: 1.2 !important;
        }

        .bio-container {
            width: 100%;
            max-width: 450px;
            padding: 40px 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        /* --- Profile Section --- */
        .profile-wrap {
            position: relative;
            margin-bottom: 30px;
            margin-top: <?php echo $show_crown ? '40px' : '0'; ?>;
        }

        .photo-container {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            position: relative;
            z-index: 2;
            padding: 4px;
            background: #000;
        }

        .profile-photo {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #000;
        }

        <?php if ($show_crown) : ?>
        /* Crown & Spinning Effect */
        .profile-wrap::before {
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
            animation: rotate-gold 3s linear infinite;
            z-index: 1;
        }

        @keyframes rotate-gold {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .majestic-crown {
            position: absolute;
            top: -35px;
            left: 50%;
            transform: translateX(-50%) scale(1.2);
            width: 50px;
            z-index: 10;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.8));
            animation: crown-float 2s ease-in-out infinite alternate;
        }

        @keyframes crown-float {
            from { transform: translateX(-50%) translateY(0) scale(1.2); }
            to { transform: translateX(-50%) translateY(-5px) scale(1.25); }
        }
        <?php endif; ?>

        /* --- Header Text --- */
        .bio-info {
            margin-bottom: 40px;
            animation: fadeInDown 0.8s ease-out;
        }

        .bio-name {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(to right, var(--elite-gold), var(--elite-gold-light), var(--elite-gold));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
            font-style: italic;
        }

        .bio-description {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.6);
            line-height: 1.5;
            max-width: 300px;
            margin: 0 auto;
        }

        /* --- Links Section --- */
        .links-list {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .link-item {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 18px 24px;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            letter-spacing: 0.5px;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
            animation: fadeInUp 0.5s ease-out backwards;
        }

        .link-item:active {
            transform: scale(0.96);
            background: rgba(212, 175, 55, 0.1);
            border-color: var(--elite-gold);
        }

        .link-item::after {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.05), transparent);
            transition: 0.5s;
        }

        .link-item:hover::after {
            left: 100%;
        }

        /* --- Footer --- */
        .bio-footer {
            margin-top: auto;
            padding-top: 40px;
            opacity: 0.3;
            font-size: 0.75rem;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        /* --- Animations --- */
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .link-item:nth-child(1) { animation-delay: 0.1s; }
        .link-item:nth-child(2) { animation-delay: 0.2s; }
        .link-item:nth-child(3) { animation-delay: 0.3s; }
        .link-item:nth-child(4) { animation-delay: 0.4s; }
        .link-item:nth-child(5) { animation-delay: 0.5s; }

        .link-icon {
            margin-right: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            color: var(--elite-gold-light);
        }

    </style>
</head>
<body>
    <?php
    function get_link_icon_svg($icon_name) {
        $svgs = array(
            'instagram' => '<svg fill="currentColor" viewBox="0 0 24 24"><path d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 a4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z"></path></svg>',
            'whatsapp'  => '<svg fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"></path></svg>',
            'facebook' => '<svg fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"></path></svg>',
            'shopping' => '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>',
            'lms'      => '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 14l9-5-9-5-9 5 9 5z"></path><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" /></svg>',
            'website'  => '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>',
            'link'     => '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.828a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>',
        );

        return isset($svgs[$icon_name]) ? $svgs[$icon_name] : $svgs['link'];
    }
    ?>

    <div class="bio-container">
        <!-- Profile -->
        <div class="profile-wrap">
            <?php if ($show_crown) : ?>
                <svg class="majestic-crown" viewBox="0 0 100 80">
                    <defs>
                        <linearGradient id="gold-grad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#D4AF37;stop-opacity:1" />
                            <stop offset="50%" style="stop-color:#F2D480;stop-opacity:1" />
                            <stop offset="100%" style="stop-color:#D4AF37;stop-opacity:1" />
                        </linearGradient>
                    </defs>
                    <path d="M10 70 L5 20 L30 45 L50 5 L70 45 L95 20 L90 70 Z" fill="url(#gold-grad)" />
                    <circle cx="50" cy="5" r="4" fill="#F2D480" />
                    <circle cx="5" cy="20" r="3" fill="#F2D480" />
                    <circle cx="95" cy="20" r="3" fill="#F2D480" />
                    <rect x="10" y="70" width="80" height="8" rx="2" fill="#F2D480" />
                    <circle cx="30" cy="74" r="2" fill="#000" />
                    <circle cx="50" cy="74" r="2" fill="#000" />
                    <circle cx="70" cy="74" r="2" fill="#000" />
                </svg>
            <?php endif; ?>

            <div class="photo-container">
                <?php if ($photo_url) : ?>
                    <img src="<?php echo esc_url($photo_url); ?>" alt="<?php echo esc_attr($bio_title); ?>" class="profile-photo">
                <?php else : ?>
                    <!-- Default Initial Logo -->
                    <div style="width: 100%; height: 100%; background: #111; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; color: var(--elite-gold); font-size: 2rem;">
                        <?php echo substr($bio_title, 0, 1); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Info -->
        <header class="bio-info">
            <h1 class="bio-name"><?php echo esc_html($bio_title); ?></h1>
            <?php if ($bio_subtitle) : ?>
                <p class="bio-description"><?php echo nl2br(esc_html($bio_subtitle)); ?></p>
            <?php endif; ?>
        </header>

        <!-- Links -->
        <main class="links-list">
            <?php foreach ($links as $link) : 
                $icon = isset($link['icon']) ? $link['icon'] : 'link';
            ?>
                <a href="<?php echo esc_url($link['url']); ?>" class="link-item" target="_blank" rel="noopener noreferrer">
                    <span class="link-icon"><?php echo get_link_icon_svg($icon); ?></span>
                    <?php echo esc_html($link['label']); ?>
                </a>
            <?php endforeach; ?>
        </main>

        <footer class="bio-footer">
            Elite LMS &bull; Academia PMU Beauty
        </footer>
    </div>

</body>
</html>
<?php wp_footer(); ?>
