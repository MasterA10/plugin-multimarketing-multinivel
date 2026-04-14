<?php
/**
 * The template for displaying the Live Mentoria Room (lms_live)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="m-0 p-0">
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        gold: { 400: '#F2D480', 500: '#D4AF37', 600: '#AA8C2C' }
                    },
                    fontFamily: {
                        serif: ['Playfair Display', 'serif'],
                        sans: ['Outfit', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        /* Elite Isolation */
        header, footer, .site-header, .site-footer, #masthead, #colophon, #wpadminbar, .admin-bar #wpadminbar { 
            display: none !important; 
            height: 0 !important;
            overflow: hidden !important;
            visibility: hidden !important;
        }
        html { margin-top: 0 !important; }
        body { background-color: #000 !important; }
        #wpcontent, #wpbody-content { padding: 0 !important; margin: 0 !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(212, 175, 55, 0.2); border-radius: 10px; }
        
        @keyframes goldFade {
            0% { border-color: rgba(212, 175, 55, 1); background: rgba(212, 175, 55, 0.1); transform: scale(1.02); }
            100% { border-color: rgba(255, 255, 255, 0.05); background: rgba(255, 255, 255, 0.05); transform: scale(1); }
        }
        .animate-gold-arrival { animation: goldFade 2s ease-out forwards; }
    </style>
</head>
<body <?php body_class('bg-black text-white font-sans'); ?>>

<?php
$live_id = get_the_ID();
$video_input = get_post_meta( $live_id, '_lms_youtube_id', true );
$duration = get_post_meta( $live_id, '_lms_duration', true ) ?: '60';
$professor_name = get_post_meta( $live_id, '_lms_professor_name', true );
$live_date = get_post_meta( $live_id, '_lms_live_date', true );
$live_time = get_post_meta( $live_id, '_lms_live_time', true );

$is_external = (strpos($video_input, 'http') !== false);

// Access Check
$access_checker = new Expressive_Access();
$has_access = $access_checker->has_active_subscription(get_current_user_id());
?>



<div class="bg-black text-white min-h-screen flex flex-col font-sans">
    
    <!-- Mentoria Header -->
    <nav class="h-16 border-b border-white/5 flex items-center justify-between px-6 bg-black z-50">
        <div class="flex items-center gap-4">
            <a href="<?php echo home_url('/area-de-membros'); ?>" class="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-zinc-400 hover:text-gold-500 transition-all">
                <span class="dashicons dashicons-arrow-left-alt2"></span>
            </a>
            <div>
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded bg-red-500/10 border border-red-500/30 text-red-500 text-[8px] font-bold uppercase tracking-widest mb-0.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span> AO VIVO
                </span>
                <h1 class="text-sm font-serif italic text-gold-500 line-clamp-1"><?php the_title(); ?></h1>
            </div>
        </div>
        
        <div class="flex items-center gap-6">
            <a href="<?php echo home_url('/area-de-membros'); ?>" class="group flex items-center gap-3 text-zinc-400 hover:text-gold-500 transition-all border-r border-white/10 pr-6 mr-2">
                <div class="w-8 h-8 rounded-full bg-white/5 border border-white/10 flex items-center justify-center group-hover:border-gold-500/30">
                    <span class="dashicons dashicons-grid-view"></span>
                </div>
                <span class="hidden md:block text-[10px] font-bold uppercase tracking-widest">Dashboard</span>
            </a>
            <a href="<?php echo home_url('/area-de-membros'); ?>" class="bg-zinc-800 hover:bg-gold-500 text-gold-500 hover:text-black border border-gold-500/30 px-6 py-2 rounded-full text-[10px] font-bold uppercase tracking-widest transition-all">
                Sair da Sala
            </a>
        </div>
    </nav>

    <main class="flex-1 p-4 md:p-8 max-w-7xl mx-auto w-full flex flex-col gap-8 overflow-y-auto custom-scrollbar">
        
        <!-- Theatre Mode Video Area -->
        <div class="w-full aspect-video bg-black rounded-3xl border border-white/5 shadow-2xl relative overflow-hidden group">
            <?php if($has_access): ?>
                <?php if($video_input): ?>
                    <?php if($is_external): ?>
                        <!-- External Meeting Link (Zoom/Meet/etc) -->
                        <div class="absolute inset-0 flex flex-col items-center justify-center bg-zinc-900/40 p-10 text-center">
                            <div class="w-24 h-24 rounded-full bg-gold-500/10 border border-gold-500/20 flex items-center justify-center text-gold-500 mb-8 shadow-2xl shadow-gold-500/10">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            </div>
                            <h2 class="text-3xl font-serif italic text-gold-500 mb-4">Sala de Mentoria Externa</h2>
                            <p class="text-zinc-400 max-w-md mx-auto mb-10 text-sm leading-relaxed">Esta mentoria está sendo realizada via plataforma externa de alto desempenho. Clique no botão abaixo para acessar o treinamento.</p>
                            
                            <a href="<?php echo esc_url($video_input); ?>" target="_blank" class="bg-gradient-to-r from-gold-600 to-gold-400 hover:from-gold-500 hover:to-gold-300 text-black px-12 py-5 rounded-2xl text-xs font-bold uppercase tracking-[0.2em] transition-all shadow-xl shadow-gold-500/20 hover:scale-105 active:scale-95">
                                Entrar na Mentoria Agora
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- YouTube Native Player Skin -->
                        <div class="absolute inset-0 scale-[1.04] pointer-events-none">
                            <iframe width="100%" height="100%" src="https://www.youtube.com/embed/<?php echo esc_attr($video_input); ?>?rel=0&modestbranding=1&showinfo=0&autoplay=1&controls=1&iv_load_policy=3" 
                                    frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen
                                    class="w-full h-full pointer-events-auto shadow-2xl"></iframe>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-zinc-600 bg-zinc-900/40">
                        <svg class="w-12 h-12 opacity-20 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        <p class="font-bold uppercase tracking-[0.2em] text-xs">Aguardando início da transmissão de elite...</p>
                        <p class="text-[10px] text-zinc-500 mt-2 font-serif italic">O mestre entrará em instantes.</p>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <!-- ELITE LOCKED LIVE UI -->
                <div class="absolute inset-0 bg-[#070707] flex flex-col items-center justify-center p-8 text-center overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-b from-red-500/5 via-transparent to-transparent opacity-30"></div>
                    <div class="absolute w-96 h-96 bg-gold-500/5 rounded-full blur-[100px] -bottom-20 -left-20"></div>

                    <div class="relative z-10 flex flex-col items-center max-w-md">
                        <div class="mb-6 relative">
                            <div class="w-16 h-16 rounded-full bg-gold-500/10 border border-gold-500/20 flex items-center justify-center text-gold-500 shadow-2xl shadow-gold-500/10">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            </div>
                            <div class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full border-4 border-black animate-pulse"></div>
                        </div>
                        
                        <h3 class="text-2xl font-serif italic text-white mb-2 tracking-tight">Mentoria em Andamento</h3>
                        <p class="text-[11px] text-zinc-500 uppercase tracking-widest mb-8 leading-relaxed font-medium">Esta sala de mentoria é exclusiva para alunos com assinatura ativa. O sinal de transmissão está bloqueado para seu perfil.</p>
                        
                        <a href="<?php echo home_url('/adquirir-acesso'); ?>" class="group relative px-10 py-5 bg-gold-500 hover:bg-gold-400 text-black font-bold text-[10px] uppercase tracking-widest rounded-2xl transition-all shadow-xl shadow-gold-500/10 hover:shadow-gold-500/20 hover:scale-105 active:scale-95 overflow-hidden">
                            <span class="relative z-10">Garantir Meu Acesso Agora</span>
                            <div class="absolute inset-0 bg-gradient-to-r from-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                        </a>
                        <div class="mt-6 flex items-center gap-2 text-[8px] text-red-500 uppercase tracking-widest font-black">
                            <span class="w-1 h-1 bg-red-500 rounded-full animate-ping"></span> TRANSMISSÃO BLOQUEADA
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Live Meta & Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
            
            <div class="lg:col-span-2 space-y-8">
                <div class="bg-zinc-900/40 p-10 rounded-3xl border border-white/5 backdrop-blur-sm">
                    <h3 class="text-[10px] font-bold uppercase tracking-[0.25em] text-zinc-500 mb-6">Pauta da Mentoria</h3>
                    <div class="prose prose-invert max-w-none text-zinc-300 leading-relaxed text-sm">
                        <?php the_content(); ?>
                    </div>
                </div>
                <!-- Elite Chat / Comments Section -->
                <div id="elite-chat-section" class="mt-4 bg-zinc-900/40 p-10 rounded-3xl border border-white/5 backdrop-blur-sm">
                    <h4 class="text-gold-500 font-serif italic text-lg mb-8 flex items-center gap-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                        Comunidade de Elite
                    </h4>
                    
                    <!-- Comment Form Container -->
                    <?php if (comments_open()): ?>
                        <?php if ($has_access): ?>
                            <div id="elite-comment-form-container" class="mb-12 p-6 bg-black/40 rounded-2xl border border-white/5 relative">
                                <form id="elite-ajax-comment-form" method="post">
                                    <textarea id="elite-comment-field" name="comment" required class="w-full bg-zinc-900 border border-white/10 rounded-xl p-4 text-white text-sm focus:border-gold-500/50 outline-none min-h-[120px]" placeholder="Compartilhe seu insight com a comunidade..."></textarea>
                                    <div class="flex justify-end mt-4">
                                        <button type="submit" class="bg-gold-500 hover:bg-gold-400 text-black px-8 py-3 rounded-xl text-[10px] font-bold uppercase tracking-widest transition-all cursor-pointer flex items-center gap-2">
                                            <span class="send-text">Enviar Comentário Elite</span>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                        </button>
                                    </div>
                                    <input type="hidden" name="post_id" value="<?php echo $live_id; ?>">
                                    <input type="hidden" name="action" value="lms_elite_comment_submit">
                                </form>
                                <div id="comment-status" class="absolute inset-0 bg-black/60 backdrop-blur-sm hidden flex items-center justify-center rounded-2xl z-10">
                                    <span class="text-gold-500 font-bold uppercase tracking-widest text-[10px]">Transmitindo Insight...</span>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Locked Comment Feedback -->
                            <div class="mb-12 p-10 bg-black/40 rounded-[32px] border border-gold-500/10 text-center relative overflow-hidden group">
                                <div class="absolute inset-0 bg-gradient-to-r from-gold-500/5 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                <svg class="w-10 h-10 text-gold-500/50 mx-auto mb-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                <h5 class="text-white font-serif italic text-lg mb-2">Insight Bloqueado</h5>
                                <p class="text-[10px] text-zinc-500 uppercase tracking-[0.2em] max-w-xs mx-auto mb-6">A participação ativa em mentorias é exclusiva para alunos. Ative seu acesso para enviar insights agora.</p>
                                <div class="w-full py-4 bg-zinc-900/40 border border-white/10 rounded-2xl text-[9px] text-gold-500/60 font-bold uppercase tracking-widest select-none">
                                    Acesso Somente Leitura Ativado
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Comments List -->
                    <div id="elite-comments-list" class="space-y-6">
                        <?php 
                        $comments = get_comments(array('post_id' => $live_id, 'status' => 'approve', 'order' => 'DESC'));
                        $last_id = 0;
                        if ($comments):
                            $last_id = intval($comments[0]->comment_ID);
                            foreach($comments as $comment): ?>
                                <div class="flex gap-5 p-6 bg-white/5 rounded-2xl border border-white/5 hover:border-gold-500/10 transition-all" data-comment-id="<?php echo $comment->comment_ID; ?>">
                                    <div class="w-10 h-10 rounded-full overflow-hidden bg-zinc-800 border border-white/10 flex-shrink-0">
                                        <?php echo get_avatar($comment, 40); ?>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between mb-2">
                                            <h5 class="text-xs font-bold text-white uppercase tracking-wider"><?php echo esc_html($comment->comment_author); ?></h5>
                                            <span class="text-[9px] text-zinc-500 uppercase tracking-widest"><?php echo get_comment_date('H:i - d/m', $comment); ?></span>
                                        </div>
                                        <div class="text-zinc-400 text-sm leading-relaxed">
                                            <?php echo wpautop(esc_html($comment->comment_content)); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach;
                        else: ?>
                            <div id="no-comments-msg" class="text-center py-10">
                                <p class="text-xs text-zinc-600 uppercase tracking-[0.2em]">Seja o primeiro a interagir nesta mentoria estratégica.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" id="last-comment-id" value="<?php echo $last_id; ?>">
                </div>
            </div>

            <aside class="space-y-6">
                <!-- Elite Status Card -->
                <div class="bg-zinc-900/40 p-8 rounded-3xl border border-white/5 flex flex-col items-center text-center">
                    <div class="w-16 h-16 rounded-full bg-gold-500/10 border border-gold-500/20 flex items-center justify-center text-gold-500 mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                    </div>
                    <h4 class="text-sm font-bold uppercase tracking-widest text-white mb-2">Sala de Mentoria</h4>
                    <p class="text-[10px] text-zinc-500 leading-relaxed uppercase tracking-widest mb-4">Acesso Exclusivo</p>
                    <?php if($professor_name): ?>
                        <div class="pt-4 border-t border-white/5 w-full">
                            <p class="text-[9px] text-zinc-500 uppercase tracking-widest mb-1">Mentor Responsável</p>
                            <p class="text-xs font-serif italic text-gold-500"><?php echo esc_html($professor_name); ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="p-8 bg-gold-500 rounded-3xl text-black">
                    <h4 class="font-serif italic text-lg mb-2">Treine seu Foco</h4>
                    <p class="text-xs font-medium opacity-80">Mentorias ao vivo geram 3x mais retenção de aprendizado estratégico.</p>
                </div>
            </aside>

        </div>
    </main>

</div>

<script>
    // Elite Chat Fallback: Ensure lms_vars is accessible even in isolated templates
    if (typeof lms_vars === 'undefined') {
        var lms_vars = {
            ajax_url: '<?php echo admin_url("admin-ajax.php"); ?>',
            nonce: '<?php echo wp_create_nonce("lms_engine_nonce"); ?>'
        };
    }

    jQuery(document).ready(function($) {
        // 1. Elite Chat: AJAX Submit
        $('#elite-ajax-comment-form').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const status = $('#comment-status');
            const list = $('#elite-comments-list');
            const field = $('#elite-comment-field');

            status.removeClass('hidden');

            $.post(lms_vars.ajax_url, {
                action: 'lms_elite_comment_submit',
                nonce: lms_vars.nonce,
                post_id: form.find('input[name="post_id"]').val(),
                comment: field.val()
            }, function(response) {
                status.addClass('hidden');
                if(response.success) {
                    $('#no-comments-msg').hide();
                    list.prepend(response.data.html);
                    field.val('');
                    const currentLast = parseInt($('#last-comment-id').val());
                    if(response.data.comment_id > currentLast) {
                        $('#last-comment-id').val(response.data.comment_id);
                    }
                } else {
                    alert(response.data);
                }
            });
        });

        // 2. Elite Chat: Polling Engine (Every 8 seconds for Lives - higher fidelity)
        setInterval(function() {
            const lastId = $('#last-comment-id').val();
            const postId = <?php echo $live_id; ?>;

            $.get(lms_vars.ajax_url, {
                action: 'lms_elite_comment_fetch',
                nonce: lms_vars.nonce,
                post_id: postId,
                last_id: lastId
            }, function(response) {
                if(response.success && response.data.comments.length > 0) {
                    $('#no-comments-msg').hide();
                    response.data.comments.forEach(function(comment) {
                        if($('[data-comment-id="' + comment.id + '"]').length === 0) {
                            const $newMsg = $(comment.html);
                            $newMsg.addClass('animate-gold-arrival');
                            $('#elite-comments-list').prepend($newMsg);
                            $('#last-comment-id').val(comment.id);
                        }
                    });
                }
            });
        }, 8000);
    });
</script>

<?php wp_footer(); ?>
</body>
</html>
