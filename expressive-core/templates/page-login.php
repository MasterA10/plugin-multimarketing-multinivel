<?php
/**
 * Template Name: LMS Login Page
 * 
 * Standalone template for a luxury login experience.
 */
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login de Elite - Expressive Core</title>
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
                        onyx: '#0F0F0F',
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
        .gold-glow { box-shadow: 0 0 20px rgba(212, 175, 55, 0.2); }
        input:focus { outline: none; border-color: #D4AF37; box-shadow: 0 0 0 2px rgba(212, 175, 55, 0.2); }
    </style>
</head>
<body class="bg-black text-white font-sans min-h-screen flex items-center justify-center p-6 bg-[url('https://www.transparenttextures.com/patterns/dark-matter.png')]">

    <div class="max-w-md w-full animate-fade-in">
        <!-- Logo / Branding -->
        <div class="text-center mb-10">
            <h1 class="font-serif text-4xl text-gold-500 italic mb-2">Elite Members</h1>
            <p class="text-gray-400 uppercase tracking-widest text-xs font-light">Acesso Exclusivo à Jornada</p>
        </div>

        <!-- Login Card -->
        <div class="glass p-8 rounded-2xl gold-glow">
            <h2 class="text-2xl font-semibold mb-6 flex items-center gap-3">
                <span class="w-2 h-8 bg-gold-500 rounded-full"></span>
                Bem-vindo de volta
            </h2>

            <?php if ( isset( $_GET['login'] ) && $_GET['login'] == 'failed' ) : ?>
                <div class="bg-red-900/30 border border-red-500/50 text-red-200 p-4 rounded-lg mb-6 text-sm">
                    Credenciais incorretas. Tente novamente ou recupere sua senha.
                </div>
            <?php endif; ?>

            <form name="loginform" id="loginform" action="<?php echo esc_url( site_url( 'wp-login.php', 'login_post' ) ); ?>" method="post">
                <div class="space-y-4">
                    <div>
                        <label for="user_login" class="block text-xs font-medium text-gray-400 uppercase tracking-wider mb-2">Usuário ou E-mail</label>
                        <input type="text" name="log" id="user_login" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white transition-all" placeholder="Seu acesso de elite...">
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label for="user_pass" class="text-xs font-medium text-gray-400 uppercase tracking-wider">Senha</label>
                            <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" class="text-[10px] text-gold-400 hover:text-gold-500 transition-colors uppercase tracking-widest">Esqueceu?</a>
                        </div>
                        <input type="password" name="pwd" id="user_pass" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white transition-all" placeholder="••••••••">
                    </div>

                    <div class="pt-4">
                        <input type="hidden" name="redirect_to" value="<?php echo site_url( '/area-de-membros/' ); ?>">
                        <button type="submit" name="wp-submit" id="wp-submit" class="w-full bg-gradient-to-r from-gold-600 to-gold-400 hover:from-gold-500 hover:to-gold-300 text-black font-bold py-4 rounded-xl transition-all transform hover:scale-[1.02] active:scale-[0.98]">
                            ACESSAR AGORA
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Footer Info -->
        <p class="text-center mt-8 text-gray-500 text-sm">
            Não é um membro ainda? <a href="#" class="text-gold-400 font-medium hover:underline">Adquirir meu acesso</a>
        </p>
    </div>

</body>
</html>
