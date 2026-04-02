<?php
/**
 * Luxury Certificate Template (Reusable)
 * Expected variables: $user_data, $course_title, $date
 */
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Certificado de Conclusão - <?php echo esc_html( $user_data->display_name ); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,700&display=swap" rel="stylesheet">
    <style>
        body { margin: 0; padding: 0; background: #000; font-family: 'Playfair Display', serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; overflow: hidden; }
        .certificate-container { 
            width: 1100px; height: 770px; background: #0a0a0a; border: 20px solid #D4AF37; 
            border-image: linear-gradient(45deg, #D4AF37, #F2D480, #D4AF37, #B8962E, #D4AF37) 1;
            color: #fff; text-align: center; position: relative; padding: 80px; box-sizing: border-box;
            box-shadow: 0 0 100px rgba(212, 175, 55, 0.15);
            background-image: radial-gradient(circle at center, #111 0%, #000 100%);
        }
        
        /* Subtle Gold Pattern Overlay */
        .certificate-container::before {
            content: ''; position: absolute; inset: 0; opacity: 0.03;
            background-image: url('https://www.transparenttextures.com/patterns/carbon-fibre.png');
            pointer-events: none;
        }

        h1 { color: #D4AF37; font-size: 4rem; letter-spacing: 12px; text-transform: uppercase; margin: 0; font-weight: 700; }
        .subtitle { font-weight: normal; margin-top: 50px; font-size: 1.4rem; color: #aaa; text-transform: uppercase; letter-spacing: 4px; }
        .student-name { font-size: 4.5rem; color: #fff; margin: 30px 0; border-bottom: 2px solid #D4AF37; display: inline-block; padding: 0 60px; font-style: italic; }
        .course-name { color: #D4AF37; font-style: italic; font-size: 2.2rem; margin-top: 40px; font-weight: 700; }
        
        .footer { position: absolute; bottom: 80px; left: 0; width: 100%; display: flex; justify-content: space-around; align-items: flex-end; }
        .signature-box { display: flex; flex-col; items: center; }
        .signature-line { border-top: 1px solid rgba(212, 175, 55, 0.4); width: 250px; padding-top: 15px; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 2px; color: #888; }
        
        .seal { width: 120px; height: 120px; border: 4px double #D4AF37; border-radius: 50%; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 5px; color: #D4AF37; position: absolute; bottom: 60px; left: 50%; transform: translateX(-50%); }
        .seal-inner { border: 1px solid #D4AF37; border-radius: 50%; width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; transform: rotate(-15deg); }
        .seal-text { font-size: 8px; font-weight: 900; }

        .print-btn { 
            position: fixed; top: 30px; right: 30px; background: #D4AF37; color: #000; border: none; 
            padding: 15px 30px; border-radius: 50px; cursor: pointer; font-weight: 900; 
            text-transform: uppercase; letter-spacing: 2px; font-size: 10px;
            box-shadow: 0 10px 30px rgba(212, 175, 55, 0.3); transition: all 0.3s; z-index: 100;
        }
        .print-btn:hover { transform: translateY(-3px); box-shadow: 0 15px 40px rgba(212, 175, 55, 0.4); }

        @media print { 
            .print-btn { display: none; } 
            body { background: #000; }
            .certificate-container { -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">Imprimir / Exportar PDF Alta Definição</button>
    <div class="certificate-container">
        <h1>Certificado de Elite</h1>
        <div class="subtitle">Expressive Core Academy</div>
        
        <p style="margin-top: 60px; font-size: 1.1rem; color: #888; letter-spacing: 2px;">Concedido solenemente a</p>
        <div class="student-name"><?php echo esc_html( $user_data->display_name ); ?></div>
        
        <p style="margin-top: 40px; font-size: 1.1rem; color: #888; letter-spacing: 2px;">Por completar com excelência e maestria a</p>
        <div class="course-name"><?php echo esc_html( $course_title ); ?></div>
        
        <div class="seal">
            <div class="seal-inner">
                <span class="seal-text">AUTENTICIDADE</span>
                <span style="font-size: 14px; font-weight: 900;">ELITE</span>
                <span class="seal-text">VERIFICADA</span>
            </div>
        </div>

        <p style="margin-top: 140px; color: #555; text-transform: uppercase; font-size: 0.6rem; letter-spacing: 3px;">Data de Emissão: <?php echo $date; ?></p>
        
        <div class="footer">
            <div class="signature-box">
                <div class="signature-line">Diretoria Acadêmica</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Conselho Marco Zero</div>
            </div>
        </div>
    </div>
</body>
</html>
