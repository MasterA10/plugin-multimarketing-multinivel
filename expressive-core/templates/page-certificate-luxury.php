<?php
/**
 * Gran Master Premium Certificate Template - REFINED LUXURY VERSION
 * Optimized for High-Resolution Printing, Long Name Support, and Aesthetic Balance
 * Expected variables: $user_data, $course_title, $date
 */
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificado Gran Master - <?php echo esc_html( $user_data->display_name ); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700;900&family=Playfair+Display:ital,wght@1,400;1,700&family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Aged Burnished Gold Palette */
            --elite-gold: #c5a059;
            --elite-gold-light: #e6d2a4;
            --elite-gold-dark: #8e6d31;
            --elite-black-deep: #050505;
            --elite-bg: #0d0d0d;
        }

        @page {
            size: A4 landscape;
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
            background: #000;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            -webkit-print-color-adjust: exact;
        }

        .cert-outer-wrapper {
            position: relative;
            width: 1123px; 
            height: 794px;
            background: var(--elite-bg);
            box-sizing: border-box;
            box-shadow: 0 0 120px rgba(0,0,0,0.9);
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            border: 1px solid rgba(197, 160, 89, 0.1);
        }

        /* Sophisticated Grain Texture Background */
        .silk-texture {
            position: absolute;
            inset: 0;
            opacity: 0.05;
            z-index: 1;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
            pointer-events: none;
        }

        /* Central Aura Glow */
        .aura-glow {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 80%; height: 80%;
            background: radial-gradient(circle, rgba(197, 160, 89, 0.08) 0%, transparent 70%);
            z-index: 2;
        }

        /* Refined Double Filigree Border */
        .cert-border-svg {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            z-index: 20;
            pointer-events: none;
        }

        .cert-inner-content {
            position: relative;
            z-index: 10;
            width: 80%;
            height: 80%;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            color: #fff;
            padding: 40px 0;
        }

        .header-section { margin-top: 40px; }
        
        .title-pre {
            font-family: 'Outfit', sans-serif;
            text-transform: uppercase;
            letter-spacing: 14px;
            font-size: 0.7rem;
            color: var(--elite-gold);
            font-weight: 600;
            margin-bottom: 12px;
            opacity: 0.8;
        }

        .main-title {
            font-family: 'Cinzel', serif;
            font-size: 3.2rem;
            font-weight: 900;
            letter-spacing: 18px;
            background: linear-gradient(to right, var(--elite-gold-dark), var(--elite-gold-light), var(--elite-gold-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
            line-height: 1;
        }

        .academy-name {
            font-family: 'Cinzel', serif;
            font-size: 0.9rem;
            letter-spacing: 6px;
            margin-top: 15px;
            color: rgba(255,255,255,0.4);
            text-transform: uppercase;
        }

        .award-text {
            font-family: 'Outfit', sans-serif;
            font-size: 1rem;
            color: rgba(255,255,255,0.5);
            margin-top: 40px;
            letter-spacing: 3px;
            font-weight: 300;
            text-transform: uppercase;
        }

        /* Dynamic Student Name Handling */
        .name-container {
            width: 100%;
            max-width: 1020px;
            margin: 20px 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .student-name {
            font-family: 'Playfair Display', serif;
            font-size: 5.5rem; 
            color: #fff;
            font-style: italic;
            display: inline-block;
            white-space: nowrap;
            text-shadow: 0 4px 10px rgba(0,0,0,0.5);
            transition: transform 0.3s ease;
            transform-origin: center;
        }

        .name-line {
            width: 300px;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--elite-gold), transparent);
            margin: 5px auto 0;
            opacity: 0.6;
        }

        .course-label {
            font-family: 'Outfit', sans-serif;
            font-size: 0.8rem;
            color: rgba(255,255,255,0.4);
            margin-top: 30px;
            text-transform: uppercase;
            letter-spacing: 4px;
        }

        .course-title {
            font-family: 'Cinzel', serif;
            font-size: 1.8rem;
            color: var(--elite-gold-light);
            margin-top: 12px;
            font-weight: 700;
            letter-spacing: 3px;
        }

        /* Heraldic Seal SVG - REPOSITIONED HIGHER */
        .cert-seal {
            margin: 15px 0 30px;
            width: 150px;
            height: 150px;
            position: relative;
        }

        .seal-svg { width: 100%; height: 100%; filter: drop-shadow(0 0 20px rgba(0,0,0,0.6)); }

        /* Signatures Grid */
        .cert-footer {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding: 0 60px 40px;
        }

        .signature-group {
            text-align: center;
            width: 260px;
        }

        .signature-svg-box {
            height: 45px;
            margin-bottom: 8px;
        }

        .sig-path { stroke: var(--elite-gold); stroke-width: 1.5; fill: none; opacity: 0.5; }

        .signature-line {
            border-top: 1px solid rgba(197, 160, 89, 0.25);
            padding-top: 12px;
        }

        .signature-name {
            font-family: 'Cinzel', serif;
            font-size: 0.75rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .signature-role {
            font-family: 'Outfit', sans-serif;
            font-size: 0.55rem;
            color: rgba(255,255,255,0.3);
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .meta-bottom {
            margin-bottom: 20px;
            font-family: 'Outfit', sans-serif;
            font-size: 0.55rem;
            color: rgba(255,255,255,0.2);
            letter-spacing: 5px;
            text-transform: uppercase;
        }

        /* Print Actions */
        .actions-overlay {
            position: fixed;
            top: 25px; right: 25px;
            z-index: 1000;
        }

        .btn-elite {
            background: var(--elite-gold);
            color: #000;
            border: none;
            padding: 14px 28px;
            border-radius: 8px;
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 0.7rem;
            cursor: pointer;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            transition: 0.3s;
            text-decoration: none;
        }

        .btn-elite:hover { background: var(--elite-gold-light); transform: translateY(-2px); }

        @media print {
            .actions-overlay { display: none; }
            body { background: #000; }
            .cert-outer-wrapper { box-shadow: none; border: none; }
        }

    </style>
</head>
<body>

    <div class="actions-overlay">
        <a href="javascript:window.print()" class="btn-elite">Imprimir Certificado</a>
    </div>

    <div class="cert-outer-wrapper">
        <div class="silk-texture"></div>
        <div class="aura-glow"></div>

        <!-- REFINED MINIMALIST BORDER -->
        <svg class="cert-border-svg" viewBox="0 0 1123 794">
            <defs>
                <linearGradient id="goldStroke" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" style="stop-color:#8e6d31;stop-opacity:1" />
                    <stop offset="50%" style="stop-color:#e6d2a4;stop-opacity:1" />
                    <stop offset="100%" style="stop-color:#8e6d31;stop-opacity:1" />
                </linearGradient>
            </defs>
            
            <!-- Outer Double Line -->
            <rect x="35" y="35" width="1053" height="724" fill="none" stroke="url(#goldStroke)" stroke-width="0.8" opacity="0.4" />
            <rect x="45" y="45" width="1033" height="704" fill="none" stroke="url(#goldStroke)" stroke-width="1.5" />
            
            <!-- Elegant Corner Corners -->
            <!-- TL -->
            <path d="M35 120 L35 35 L120 35" fill="none" stroke="url(#goldStroke)" stroke-width="4" />
            <path d="M20 20 L40 20 L40 40" fill="none" stroke="url(#goldStroke)" stroke-width="1" opacity="0.3" transform="translate(15, 15)"/>
            
            <!-- TR -->
            <path d="M1003 35 L1088 35 L1088 120" fill="none" stroke="url(#goldStroke)" stroke-width="4" />
            
            <!-- BL -->
            <path d="M35 674 L35 759 L120 759" fill="none" stroke="url(#goldStroke)" stroke-width="4" />
            
            <!-- BR -->
            <path d="M1003 759 L1088 759 L1088 674" fill="none" stroke="url(#goldStroke)" stroke-width="4" />
        </svg>

        <div class="cert-inner-content">
            <header class="header-section">
                <div class="title-pre">Consagração de Excelência</div>
                <h1 class="main-title">GRAN MASTER</h1>
                <div class="academy-name">CCP Elite Academy &bull; Brazil </div>
            </header>

            <p class="award-text">Concedemos solenemente a titularidade a</p>
            
            <div class="name-container">
                <div class="student-name" id="student-name-text"><?php echo esc_html( $display_name ); ?></div>
            </div>
            <div class="name-line"></div>

            <p class="course-label">Pela conclusão da formação oficial de maestria em</p>
            <div class="course-title"><?php echo esc_html( $course_title ); ?></div>

            <div class="cert-seal">
                <svg class="seal-svg" viewBox="0 0 200 200">
                    <circle cx="100" cy="100" r="85" fill="#111" stroke="var(--elite-gold)" stroke-width="1" />
                    <!-- Clean Inner Decorative Circle -->
                    <circle cx="100" cy="100" r="50" fill="none" stroke="var(--elite-gold)" stroke-width="1.5" opacity="0.4" />
                    
                    <defs>
                        <path id="sealPath" d="M 100, 100 m -62, 0 a 62,62 0 1,1 124,0 a 62,62 0 1,1 -124,0" />
                    </defs>
                    <text font-family="'Cinzel', serif" font-size="10" fill="var(--elite-gold)" letter-spacing="2">
                        <textPath xlink:href="#sealPath" startOffset="0%">
                            OFFICIAL • GRAN MASTER • ELITE ACADEMY • AUTHENTIC
                        </textPath>
                    </text>
                    <!-- Center GM Monogram - REFINED -->
                    <text x="100" y="112" font-family="'Cinzel', serif" font-size="34" font-weight="900" fill="var(--elite-gold-light)" text-anchor="middle">GM</text>
                    <circle cx="100" cy="100" r="48" fill="none" stroke="var(--elite-gold)" stroke-width="0.5" opacity="0.3" />
                </svg>
            </div>

            <!-- Signatures Removed by Design Specification -->

            <div class="meta-bottom">Validade Internacional &bull; Data de Outorga: <?php echo $date; ?></div>
        </div>
    </div>

    <script>
        /**
         * Student Name Auto-Fit Logic
         * Dynamically scales the name if it exceeds the 1020px container.
         */
        function autoFitStudentName() {
            const container = document.querySelector('.name-container');
            const nameText = document.getElementById('student-name-text');
            
            if (!container || !nameText) return;

            const containerWidth = container.offsetWidth;
            const textWidth = nameText.scrollWidth;

            if (textWidth > containerWidth) {
                const scale = containerWidth / (textWidth + 20); // 20px buffer
                nameText.style.transform = `scale(${scale})`;
            }
        }

        // Run on load and after fonts are ready
        window.addEventListener('load', autoFitStudentName);
        if (document.fonts) {
            document.fonts.ready.then(autoFitStudentName);
        }
    </script>
</body>
</html>
