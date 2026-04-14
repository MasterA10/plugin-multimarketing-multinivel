<?php
/**
 * The template for displaying the Elite Player (lms_lesson)
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
        .prose h1, .prose h2, .prose h3 { color: #D4AF37 !important; font-family: 'Playfair Display', serif !important; }

        @keyframes goldFade {
            0% { border-color: rgba(212, 175, 55, 1); background: rgba(212, 175, 55, 0.1); transform: scale(1.02); }
            100% { border-color: rgba(255, 255, 255, 0.05); background: rgba(255, 255, 255, 0.05); transform: scale(1); }
        }
        .animate-gold-arrival { animation: goldFade 2s ease-out forwards; }
    </style>
</head>
<body <?php body_class('bg-black text-white font-sans'); ?>>

<?php
$lesson_id = get_the_ID();
$youtube_id = get_post_meta( $lesson_id, '_lms_youtube_id', true );
$course_id = get_post_meta( $lesson_id, '_lms_course_id', true );
$duration = get_post_meta( $lesson_id, '_lms_duration', true ) ?: '15';
$professor_name = get_post_meta( $lesson_id, '_lms_professor_name', true );
$lesson_date = get_post_meta( $lesson_id, '_lms_lesson_date', true );

// Sidebar Lessons
$lessons = array();
if ($course_id) {
    $lessons = get_posts(array(
        'post_type' => 'lms_lesson',
        'meta_query' => array(array('key' => '_lms_course_id', 'value' => $course_id)),
        'posts_per_page' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC'
    ));

    $modules = get_posts(array(
        'post_type' => 'lms_module',
        'meta_query' => array(array('key' => '_lms_course_id', 'value' => $course_id)),
        'posts_per_page' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC'
    ));

    $lessons_by_module = array();
    $orphan_lessons = array();

    foreach($lessons as $l) {
        $m_id = get_post_meta($l->ID, '_lms_module_id', true);
        if($m_id) {
            $lessons_by_module[$m_id][] = $l;
        } else {
            $orphan_lessons[] = $l;
        }
    }
}

// User Progress
$user_id = get_current_user_id();
$completed = get_user_meta($user_id, '_lms_completed_lessons', true);
if(!is_array($completed)) $completed = array();
$is_watched = in_array($lesson_id, $completed);
// Access Check
$access_checker = new Expressive_Access();
$has_access = $access_checker->has_active_subscription($user_id);

// Supporting Materials
$supporting_files = get_post_meta( $lesson_id, '_lms_files_data', true );
if ( ! is_array( $supporting_files ) ) $supporting_files = array();
?>


<div class="bg-black text-white min-h-screen flex flex-col font-sans relative">
    
    <!-- LOCKED WATERMARK (Optional subtle cue) -->
    <?php if(!$has_access): ?>
        <div class="fixed top-20 left-10 opacity-10 pointer-events-none rotate-12 z-0 hidden lg:block">
            <span class="text-8xl font-black text-gold-500 uppercase tracking-widest select-none">Preview</span>
        </div>
    <?php endif; ?>

    <!-- Top Navigation -->
    <nav class="h-16 border-b border-white/5 flex items-center justify-between px-6 bg-black/90 backdrop-blur-xl z-50 sticky top-0">
        <div class="flex items-center gap-4">
            <a href="<?php echo $course_id ? get_permalink($course_id) : '#'; ?>" class="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-zinc-400 hover:text-gold-500 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <div>
                <h2 class="text-xs font-bold uppercase tracking-[0.2em] text-zinc-500 line-clamp-1">
                    <?php echo $course_id ? get_the_title($course_id) : 'Treinamento de Elite'; ?>
                </h2>
                <h1 class="text-sm font-serif italic text-gold-500 line-clamp-1"><?php the_title(); ?></h1>
            </div>
        </div>
        
        <div class="flex items-center gap-6">
            <!-- Hamburger Menu Button (Mobile Only) -->
            <button onclick="toggleSyllabus()" class="lg:hidden w-10 h-10 rounded-full bg-gold-500/10 border border-gold-500/30 flex items-center justify-center text-gold-500 hover:bg-gold-500 hover:text-black transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
            </button>

            <a href="<?php echo home_url('/area-de-membros'); ?>" class="group flex items-center gap-3 text-zinc-400 hover:text-gold-500 transition-all border-r border-white/10 pr-6 mr-2 hidden md:flex">
                <div class="w-8 h-8 rounded-full bg-white/5 border border-white/10 flex items-center justify-center group-hover:border-gold-500/30">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                </div>
                <span class="hidden md:block text-[10px] font-bold uppercase tracking-widest">Dashboard</span>
            </a>
            <a href="<?php echo $course_id ? get_permalink($course_id) : '#'; ?>" class="bg-zinc-800 hover:bg-gold-500 text-gold-500 hover:text-black border border-gold-500/30 px-6 py-2 rounded-full text-[10px] font-bold uppercase tracking-widest transition-all hidden sm:block">
                Ver Roteiro
            </a>
        </div>
    </nav>

    <div class="flex flex-col lg:flex-row">
        
        <!-- Main Area: Content & Video -->
        <main class="flex-1 bg-[#0a0a0a] p-4 md:p-10 space-y-10">
            
            <!-- Video Player (Cinema Mode) -->
            <div class="max-w-6xl mx-auto w-full">
                <div class="aspect-video bg-black rounded-3xl border border-white/5 shadow-2xl overflow-hidden relative group">
                    <?php if($has_access): ?>
                        <?php if($youtube_id): ?>
                            <div class="absolute inset-0 scale-[1.04] pointer-events-none">
                                <iframe width="100%" height="100%" src="https://www.youtube.com/embed/<?php echo esc_attr($youtube_id); ?>?rel=0&modestbranding=1&showinfo=0&autoplay=0&controls=1&iv_load_policy=3" 
                                        frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen
                                        class="w-full h-full pointer-events-auto"></iframe>
                            </div>
                        <?php else: ?>
                            <div class="absolute inset-0 flex flex-col items-center justify-center text-zinc-600 space-y-4">
                                <span class="dashicons dashicons-video-alt3 text-6xl opacity-20"></span>
                                <p class="font-bold uppercase tracking-widest text-xs">Aguardando sinal de vídeo...</p>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- ELITE LOCKED CONTENT UI -->
                        <div class="absolute inset-0 bg-[#070707] flex flex-col items-center justify-center p-8 text-center overflow-hidden">
                            <!-- Background Decoration -->
                            <div class="absolute inset-0 bg-gradient-to-t from-gold-500/5 via-transparent to-transparent opacity-50"></div>
                            <div class="absolute w-96 h-96 bg-gold-500/5 rounded-full blur-[100px] -top-20 -right-20"></div>

                            <div class="relative z-10 flex flex-col items-center max-w-md">
                                <div class="w-16 h-16 rounded-full bg-gold-500/10 border border-gold-500/20 flex items-center justify-center text-gold-500 mb-6 shadow-2xl shadow-gold-500/10 scale-110">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                </div>
                                <h3 class="text-2xl font-serif italic text-white mb-2 tracking-tight">Estratégia Exclusiva</h3>
                                <p class="text-[11px] text-zinc-500 uppercase tracking-widest mb-8 leading-relaxed">Este treinamento de alto impacto está disponível apenas para membros ativos da Elite.</p>
                                
                                <a href="<?php echo home_url('/adquirir-acesso'); ?>" class="group relative px-10 py-4 bg-gold-500 hover:bg-gold-400 text-black font-bold text-[10px] uppercase tracking-widest rounded-2xl transition-all shadow-xl shadow-gold-500/10 hover:shadow-gold-500/20 hover:scale-105 active:scale-95 overflow-hidden">
                                    <span class="relative z-10">Desbloquear Minha Jornada</span>
                                    <div class="absolute inset-0 bg-gradient-to-r from-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                                </a>
                                <p class="mt-4 text-[8px] text-zinc-600 uppercase tracking-widest italic">Acesso vitalício após a confirmação do pagamento</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Global Action Bar -->
                <div class="mt-8 flex flex-col md:flex-row items-center justify-between gap-6 p-8 bg-zinc-900/40 rounded-3xl border border-white/5 backdrop-blur-sm">
                    <div class="flex-1">
                        <h3 class="text-xl font-serif italic text-white mb-2"><?php the_title(); ?></h3>
                         <p class="text-xs text-zinc-500 uppercase tracking-widest flex items-center gap-4">
                              <span class="flex items-center gap-1.5">
                                 <span class="w-1.5 h-1.5 rounded-full <?php echo $is_watched ? 'bg-gold-500' : 'bg-zinc-700'; ?>"></span>
                                 <?php echo $is_watched ? 'Treino Concluído' : 'Disponível'; ?>
                              </span>
                              <?php if($professor_name): ?>
                                <span class="flex items-center gap-1.5 border-l border-white/10 pl-4">
                                    <span class="text-zinc-600">PROF:</span>
                                    <span class="text-gold-500 font-serif italic lowercase first-letter:uppercase"><?php echo esc_html($professor_name); ?></span>
                                </span>
                              <?php endif; ?>
                         </p>
                    </div>
                    
                    <?php if($has_access): ?>
                        <button id="elite-complete-btn" 
                                data-post-id="<?php echo $lesson_id; ?>"
                                class="group relative overflow-hidden bg-zinc-800 hover:bg-gold-500 text-gold-500 hover:text-black border border-gold-500/30 hover:border-gold-500 px-10 py-5 rounded-2xl text-[10px] font-bold uppercase tracking-widest transition-all shadow-xl hover:shadow-gold-500/20 <?php echo $is_watched ? 'border-gold-500 bg-gold-500/10' : ''; ?>">
                            <span class="relative z-10 flex items-center gap-3">
                                <?php if ($is_watched): ?>
                                    <svg class="w-5 h-5 text-gold-500 group-hover:text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span class="group-hover:hidden">Aula Concluída</span>
                                    <span class="hidden group-hover:block">Desmarcar Aula</span>
                                <?php else: ?>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Marcar como Finalizada
                                <?php endif; ?>
                            </span>
                        </button>
                    <?php else: ?>
                        <div class="flex items-center gap-2 px-6 py-4 bg-white/5 border border-white/10 rounded-2xl text-zinc-500 opacity-50 grayscale select-none">
                           <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                           <span class="text-[9px] font-bold uppercase tracking-widest">Botão de Progresso Bloqueado</span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Description Area -->
                <div class="mt-12 bg-zinc-900/40 p-10 rounded-3xl border border-white/5 prose prose-invert max-w-none">
                    <h4 class="text-gold-500 font-serif italic text-lg mb-6 border-b border-white/5 pb-4">Notas de Aula & Estratégia</h4>
                    <div class="text-zinc-300 leading-relaxed text-sm">
                        <?php the_content(); ?>
                    </div>
                </div>

                <!-- Supporting Materials (PDF, E-books, etc) -->
                <?php if ( ! empty( $supporting_files ) ) : ?>
                    <div class="mt-12 bg-zinc-900/40 p-10 rounded-3xl border border-white/5 backdrop-blur-sm">
                        <div class="flex items-center justify-between mb-8 pb-4 border-b border-white/5">
                            <h4 class="text-gold-500 font-serif italic text-lg flex items-center gap-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                Materiais Complementares
                            </h4>
                            <?php if ( ! $has_access ) : ?>
                                <span class="text-[8px] bg-red-500 text-white px-3 py-1 rounded-full font-black uppercase tracking-widest animate-pulse">Acesso Bloqueado</span>
                            <?php endif; ?>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php foreach ( $supporting_files as $file ) : 
                                $file_ext = pathinfo( get_attached_file( $file['id'] ), PATHINFO_EXTENSION );
                                $is_pdf = ( strtolower( $file_ext ) === 'pdf' );
                                ?>
                                <div class="group p-5 bg-black/40 rounded-2xl border border-white/5 hover:border-gold-500/20 transition-all flex items-center justify-between relative overflow-hidden">
                                    <div class="flex items-center gap-4 relative z-10">
                                        <div class="w-12 h-12 rounded-xl bg-zinc-900 flex items-center justify-center text-zinc-500 group-hover:text-gold-500 transition-colors border border-white/5">
                                            <?php if ( ! $has_access ) : ?>
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                            <?php else : ?>
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <h5 class="text-[11px] font-bold text-white uppercase tracking-wider line-clamp-1"><?php echo esc_html( $file['name'] ); ?></h5>
                                            <p class="text-[8px] text-zinc-600 uppercase tracking-widest mt-1">
                                                <?php echo strtoupper( $file_ext ); ?> • MATERIAL EXCLUSIVO
                                            </p>
                                        </div>
                                    </div>

                                    <?php if ( $has_access ) : ?>
                                        <a href="<?php echo home_url( '/?lms_download=' . $file['id'] ); ?>" class="relative z-10 w-10 h-10 rounded-full bg-gold-500 text-black flex items-center justify-center hover:scale-110 active:scale-95 transition-all shadow-lg shadow-gold-500/20">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        </a>
                                    <?php else : ?>
                                        <div class="relative z-10 w-10 h-10 rounded-full bg-zinc-800 text-zinc-600 flex items-center justify-center opacity-50 cursor-not-allowed">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Decorative subtle background -->
                                    <div class="absolute inset-0 bg-gradient-to-r from-gold-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if ( ! $has_access ) : ?>
                            <div class="mt-8 p-6 bg-gold-500/5 border border-gold-500/10 rounded-2xl text-center">
                                <p class="text-[9px] text-gold-500/80 uppercase tracking-[0.2em] font-medium mb-4">Atualize seu acesso para baixar as ferramentas e apostilas desta estratégia.</p>
                                <a href="<?php echo home_url( '/adquirir-acesso' ); ?>" class="text-[8px] text-black bg-gold-500 font-black uppercase tracking-[0.3em] px-6 py-2 rounded-lg hover:bg-gold-400 transition-all">Liberar Downloads Agora</a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>


                <!-- Elite Chat / Comments Section -->
                <div id="elite-chat-section" class="mt-12 bg-zinc-900/40 p-10 rounded-3xl border border-white/5 backdrop-blur-sm">
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
                                    <input type="hidden" name="post_id" value="<?php echo $lesson_id; ?>">
                                    <input type="hidden" name="action" value="lms_elite_comment_submit">
                                </form>
                                <div id="comment-status" class="absolute inset-0 bg-black/60 backdrop-blur-sm hidden flex items-center justify-center rounded-2xl z-10">
                                    <span class="text-gold-500 font-bold uppercase tracking-widest text-[10px]">Processando Insight...</span>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Locked Comment Feedback -->
                            <div class="mb-12 p-10 bg-black/40 rounded-[32px] border border-gold-500/10 text-center relative overflow-hidden group">
                                <div class="absolute inset-0 bg-gradient-to-r from-gold-500/5 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                <svg class="w-10 h-10 text-gold-500/50 mx-auto mb-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                <h5 class="text-white font-serif italic text-lg mb-2">Comunidade Privada</h5>
                                <p class="text-[10px] text-zinc-500 uppercase tracking-[0.2em] max-w-xs mx-auto mb-6">Apenas alunos ativos podem interagir e ganhar autoridade no chat da Elite.</p>
                                <div class="w-full py-4 bg-zinc-900/40 border border-white/5 rounded-2xl text-[9px] text-gold-500/60 font-bold uppercase tracking-widest select-none">
                                    Modo de Observador Ativado
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Comments List -->
                    <div id="elite-comments-list" class="space-y-6">
                        <?php 
                        $comments = get_comments(array('post_id' => $lesson_id, 'status' => 'approve', 'order' => 'DESC')); // Show latest first for AJAX logic convenience
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
                                            <div class="flex items-center gap-3">
                                                <span class="text-[9px] text-zinc-500 uppercase tracking-widest"><?php echo get_comment_date('H:i - d/m', $comment); ?></span>
                                                <?php if ( current_user_can( 'manage_options' ) ) : ?>
                                                    <button class="delete-elite-comment text-red-500/50 hover:text-red-500 transition-colors" data-comment-id="<?php echo $comment->comment_ID; ?>" title="Apagar Insight">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="text-zinc-400 text-sm leading-relaxed">
                                            <?php echo wpautop(esc_html($comment->comment_content)); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach;
                        else: ?>
                            <div id="no-comments-msg" class="text-center py-10">
                                <p class="text-xs text-zinc-600 uppercase tracking-[0.2em]">Seja o primeiro a comentar nesta aula estratégica.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" id="last-comment-id" value="<?php echo $last_id; ?>">
                </div>
            </div>

        </main>

        <!-- Sidebar Container (Mobile Overlay & Sidebar) -->
        <div id="elite-syllabus-overlay" onclick="toggleSyllabus()" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[55] hidden lg:hidden opacity-0 transition-opacity duration-300"></div>

        <!-- Sidebar: Syllabus -->
        <aside id="elite-syllabus-sidebar" class="w-80 lg:w-96 bg-black border-l border-white/5 fixed lg:sticky top-0 lg:top-16 bottom-0 right-0 z-[60] lg:z-40 transform translate-x-full lg:translate-x-0 transition-transform duration-300 overflow-y-auto custom-scrollbar shadow-2xl lg:shadow-none">
            <div class="p-6 border-b border-white/5 bg-zinc-900/20 flex items-center justify-between">
                <div>
                    <h3 class="text-[10px] font-bold uppercase tracking-[0.25em] text-gold-500 mb-1">Cronograma de Especialização</h3>
                    <p class="text-zinc-500 text-xs italic font-serif">Módulos de Domínio</p>
                </div>
                <!-- Close Button (Mobile Only) -->
                <button onclick="toggleSyllabus()" class="lg:hidden text-zinc-500 hover:text-gold-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div class="flex-1 overflow-y-auto custom-scrollbar p-2">
                <div class="space-y-4">
                    <?php 
                    $lesson_number = 1;
                    if(!empty($modules)): 
                        foreach($modules as $module): 
                            $module_lessons = isset($lessons_by_module[$module->ID]) ? $lessons_by_module[$module->ID] : array();
                            // Optional: auto-expand module if the current lesson is inside it
                            $is_expanded = false;
                            foreach($module_lessons as $ml) {
                                if($ml->ID == $lesson_id) $is_expanded = true;
                            }
                    ?>
                    <div class="mb-2">
                        <!-- Module Header -->
                        <div class="px-3 py-2 text-[10px] font-bold uppercase tracking-widest text-gold-500 bg-zinc-900/40 rounded-t-xl border-b border-white/5 mx-2 flex justify-between items-center cursor-pointer" onclick="this.nextElementSibling.classList.toggle('hidden')">
                            <span class="truncate"><?php echo esc_html($module->post_title); ?></span>
                            <svg class="w-3 h-3 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                        
                        <!-- Module Lessons -->
                        <div class="space-y-1 mx-2 bg-black border border-white/5 border-t-0 rounded-b-xl px-1 pb-1 pt-1 <?php echo $is_expanded ? '' : 'hidden'; ?>">
                            <?php foreach($module_lessons as $l): 
                                $is_active = (get_the_ID() == $l->ID);
                                $is_done = in_array($l->ID, $completed);
                            ?>
                            <a href="<?php echo get_permalink($l->ID); ?>" class="group block p-3 rounded-xl transition-all <?php echo $is_active ? 'bg-gold-500/10 border border-gold-500/20' : 'hover:bg-white/5'; ?>">
                                <div class="flex items-center gap-3">
                                    <div class="w-6 h-6 rounded flex items-center justify-center text-[9px] font-bold border transition-all <?php echo $is_active ? 'bg-gold-500 border-gold-500 text-black shadow-lg shadow-gold-500/20' : ($is_done ? 'bg-gold-500/20 border-gold-500/20 text-gold-500' : 'bg-black border-white/10 text-zinc-600 group-hover:border-gold-500/30 group-hover:text-gold-400'); ?>">
                                        <?php if(!$has_access): ?>
                                            <svg class="w-3 h-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                        <?php elseif($is_done && !$is_active): ?>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        <?php else: ?>
                                            <?php echo str_pad($lesson_number++, 2, '0', STR_PAD_LEFT); ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-[11px] font-semibold leading-tight truncate group-hover:text-gold-400 transition-colors <?php echo $is_active ? 'text-gold-500' : ($is_done ? 'text-zinc-400' : 'text-zinc-500'); ?>">
                                            <?php echo $l->post_title; ?>
                                        </h4>
                                    </div>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php 
                        endforeach; 
                    endif; 

                    // Render orphans if any
                    if(!empty($orphan_lessons)):
                    ?>
                    <div class="mb-2">
                        <div class="px-3 py-2 text-[10px] font-bold uppercase tracking-widest text-zinc-500 bg-zinc-900/20 rounded-t-xl border-b border-white/5 mx-2">
                            Aulas Sem Módulo
                        </div>
                        <div class="space-y-1 mx-2 bg-black border border-white/5 border-t-0 rounded-b-xl px-1 pb-1 pt-1">
                            <?php foreach($orphan_lessons as $l): 
                                $is_active = (get_the_ID() == $l->ID);
                                $is_done = in_array($l->ID, $completed);
                            ?>
                            <a href="<?php echo get_permalink($l->ID); ?>" class="group block p-3 rounded-xl transition-all <?php echo $is_active ? 'bg-gold-500/10 border border-gold-500/20' : 'hover:bg-white/5'; ?>">
                                <div class="flex items-center gap-3">
                                    <div class="w-6 h-6 rounded flex items-center justify-center text-[9px] font-bold border transition-all <?php echo $is_active ? 'bg-gold-500 border-gold-500 text-black shadow-lg shadow-gold-500/20' : ($is_done ? 'bg-gold-500/20 border-gold-500/20 text-gold-500' : 'bg-black border-white/10 text-zinc-600 group-hover:border-gold-500/30 group-hover:text-gold-400'); ?>">
                                        <?php if(!$has_access): ?>
                                            <svg class="w-3 h-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                        <?php elseif($is_done && !$is_active): ?>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        <?php else: ?>
                                            <?php echo str_pad($lesson_number++, 2, '0', STR_PAD_LEFT); ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-[11px] font-semibold leading-tight truncate group-hover:text-gold-400 transition-colors <?php echo $is_active ? 'text-gold-500' : ($is_done ? 'text-zinc-400' : 'text-zinc-500'); ?>">
                                            <?php echo $l->post_title; ?>
                                        </h4>
                                    </div>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </aside>

    </div>

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
        // Toggle Sidebar Function
        window.toggleSyllabus = function() {
            const sidebar = $('#elite-syllabus-sidebar');
            const overlay = $('#elite-syllabus-overlay');
            
            if(sidebar.hasClass('translate-x-full')) {
                // Open
                sidebar.removeClass('translate-x-full');
                overlay.removeClass('hidden').addClass('opacity-100');
                $('body').addClass('overflow-hidden');
            } else {
                // Close
                sidebar.addClass('translate-x-full');
                overlay.removeClass('opacity-100').addClass('opacity-0');
                setTimeout(() => overlay.addClass('hidden'), 300);
                $('body').removeClass('overflow-hidden');
            }
        }

        // 1. Completion Tracker
        $('#elite-complete-btn').on('click', function() {
            const btn = $(this);
            const postId = btn.data('post-id');
            btn.addClass('opacity-50 pointer-events-none').find('span').text('Processando...');
            $.post(lms_vars.ajax_url, {
                action: 'lms_mark_lesson_complete',
                nonce: lms_vars.nonce,
                lesson_id: postId
            }, function(response) {
                if(response.success) {
                    btn.html('<span class="relative z-10 flex items-center gap-3"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Módulo Finalizado</span>');
                    window.location.reload();
                } else {
                    alert('Erro ao processar: ' + response.data);
                    btn.removeClass('opacity-50 pointer-events-none').find('span').text('Tentar Novamente');
                }
            });
        });

        // 2. Elite Chat: AJAX Submit
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
                    // Update last ID if higher
                    const currentLast = parseInt($('#last-comment-id').val());
                    if(response.data.comment_id > currentLast) {
                        $('#last-comment-id').val(response.data.comment_id);
                    }
                } else {
                    alert(response.data);
                }
            });
        });

        // 3. Elite Chat: Polling Engine (Every 10 seconds)
        setInterval(function() {
            const lastId = $('#last-comment-id').val();
            const postId = <?php echo $lesson_id; ?>;

            $.get(lms_vars.ajax_url, {
                action: 'lms_elite_comment_fetch',
                nonce: lms_vars.nonce,
                post_id: postId,
                last_id: lastId
            }, function(response) {
                if(response.success && response.data.comments.length > 0) {
                    $('#no-comments-msg').hide();
                    response.data.comments.forEach(function(comment) {
                        // Check if already in list (prevent self-duplication if lag occurs)
                        if($('[data-comment-id="' + comment.id + '"]').length === 0) {
                            const $newMsg = $(comment.html);
                            $newMsg.addClass('animate-gold-arrival');
                            $('#elite-comments-list').prepend($newMsg);
                            $('#last-comment-id').val(comment.id);
                        }
                    });
                }
            });
        }, 10000); // 10s polling for high-performance balance
        // 4. Elite Chat: Admin Deletion
        $(document).on('click', '.delete-elite-comment', function() {
            const btn = $(this);
            const commentId = btn.data('comment-id');
            const commentBubble = btn.closest('[data-comment-id]');

            if(!confirm('Tem certeza que deseja apagar este insight permanentemente?')) return;

            btn.addClass('opacity-50 pointer-events-none');

            $.post(lms_vars.ajax_url, {
                action: 'lms_elite_comment_delete',
                nonce: lms_vars.nonce,
                comment_id: commentId
            }, function(response) {
                if(response.success) {
                    commentBubble.fadeOut(300, function() { $(this).remove(); });
                } else {
                    alert(response.data);
                    btn.removeClass('opacity-50 pointer-events-none');
                }
            });
        });
    });
</script>


<?php wp_footer(); ?>
</body>
</html>
