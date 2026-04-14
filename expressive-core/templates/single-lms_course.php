<?php
/**
 * The template for displaying Course Overview (lms_course)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
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
        /* Strong Isolation CSS */
        header, footer, .site-header, .site-footer, #masthead, #colophon, #wpadminbar, .admin-bar #wpadminbar { 
            display: none !important; 
            height: 0 !important;
            overflow: hidden !important;
            visibility: hidden !important;
        }
        html { margin-top: 0 !important; }
        body { background-color: black !important; }
        #wpcontent, #wpbody-content { padding: 0 !important; margin: 0 !important; }
        .dashicons { font-family: dashicons !important; display: inline-block !important; }
    </style>
</head>
<body <?php body_class('bg-black text-white font-sans'); ?>>

<?php
$course_id = get_the_ID();
$modules = get_posts(array(
    'post_type' => 'lms_module',
    'meta_query' => array(array('key' => '_lms_course_id', 'value' => $course_id)),
    'posts_per_page' => -1,
    'orderby' => 'menu_order',
    'order' => 'ASC'
));

$lessons = get_posts(array(
    'post_type' => 'lms_lesson',
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

// Calculate Total Duration
$total_min = 0;
foreach($lessons as $l) {
    $total_min += intval(get_post_meta($l->ID, '_lms_duration', true));
}
$hours = floor($total_min / 60);
$mins = $total_min % 60;
$duration_str = ($hours > 0 ? $hours . 'h ' : '') . $mins . 'm';

// User Progress
$user_id = get_current_user_id();
$completed = get_user_meta($user_id, '_lms_completed_lessons', true);
if(!is_array($completed)) $completed = array();
$completed_count = 0;
foreach($lessons as $l) { if(in_array($l->ID, $completed)) $completed_count++; }
$progress_pct = count($lessons) > 0 ? round(($completed_count / count($lessons)) * 100) : 0;

// Access Check
$access_checker = new Expressive_Access();
$has_access = $access_checker->has_active_subscription($user_id);
?>



<div class="bg-black text-white min-h-screen font-sans">
    
    <!-- Navigation Header -->
    <nav class="h-16 border-b border-white/5 flex items-center justify-between px-6 bg-black/90 backdrop-blur-xl z-50 sticky top-0">
        <div class="flex items-center gap-4">
            <a href="<?php echo home_url('/area-de-membros'); ?>" class="group flex items-center gap-3 text-zinc-400 hover:text-gold-500 transition-all">
                <div class="w-8 h-8 rounded-full bg-white/5 border border-white/10 flex items-center justify-center group-hover:border-gold-500/30">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </div>
                <span class="text-[10px] font-bold uppercase tracking-widest">Painel do Membro</span>
            </a>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-[9px] font-bold uppercase tracking-widest text-zinc-600">Elite Learning Platform</span>
        </div>
    </nav>

    <!-- Hero Banner -->
    <div class="relative h-[400px] overflow-hidden border-b border-gold-500/20">
        <?php if(has_post_thumbnail()): ?>
            <?php the_post_thumbnail('full', array('class' => 'w-full h-full object-cover blur-sm opacity-40 scale-110')); ?>
        <?php endif; ?>
        <div class="absolute inset-0 bg-gradient-to-t from-black via-black/60 to-transparent"></div>
        
        <div class="absolute inset-0 flex items-center">
            <div class="max-w-6xl mx-auto px-6 w-full">
                <div class="flex flex-col md:flex-row items-center gap-10">
                    <!-- Thumbnail -->
                    <div class="w-64 aspect-[4/5] bg-zinc-900 rounded-2xl border border-gold-500/30 shadow-2xl overflow-hidden hidden md:block">
                        <?php the_post_thumbnail('medium_large', array('class' => 'w-full h-full object-cover')); ?>
                    </div>
                    
                    <div class="flex-1 text-center md:text-left">
                        <span class="inline-block px-4 py-1 rounded-full bg-gold-500/10 border border-gold-500/30 text-gold-500 text-[10px] font-bold uppercase tracking-widest mb-4">Treinamento de Elite</span>
                        <h1 class="text-4xl md:text-6xl font-serif italic text-gold-500 mb-6 drop-shadow-lg"><?php the_title(); ?></h1>
                        
                        <div class="flex flex-wrap justify-center md:justify-start gap-8">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-gold-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-[10px] text-zinc-500 uppercase font-bold tracking-tighter">Carga Horária</p>
                                    <p class="text-lg font-bold text-white"><?php echo $duration_str; ?></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-gold-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-[10px] text-zinc-500 uppercase font-bold tracking-tighter">Conteúdo</p>
                                    <p class="text-lg font-bold text-white"><?php echo count($lessons); ?> Aulas</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-gold-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-[10px] text-zinc-500 uppercase font-bold tracking-tighter">Seu Progresso</p>
                                    <p class="text-lg font-bold text-white"><?php echo $progress_pct; ?>% Completado</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Syllabus Section -->
    <div class="max-w-6xl mx-auto px-6 py-16">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
            
            <!-- Main Column: Lessons -->
            <div class="lg:col-span-2">
                <div class="flex justify-between items-end mb-10 border-b border-zinc-800 pb-6">
                    <div>
                        <h2 class="text-2xl font-serif text-white">Cronograma de Treino</h2>
                        <p class="text-sm text-zinc-500 mt-1">Siga a jornada para desbloquear seu potencial máximo.</p>
                    </div>
                    <?php if($has_access): ?>
                        <a href="<?php echo get_permalink($lessons[0]->ID); ?>" class="bg-gold-500 hover:bg-gold-400 text-black px-6 py-3 rounded-xl text-xs font-bold uppercase tracking-widest transition-all">
                            Continuar de onde parei
                        </a>
                    <?php else: ?>
                        <div class="px-6 py-3 bg-white/5 border border-white/10 text-zinc-600 rounded-xl text-[10px] font-bold uppercase tracking-widest select-none opacity-50">
                            Acesso Restrito
                        </div>
                    <?php endif; ?>
                </div>

                <div class="space-y-8">
                    <?php 
                    $lesson_number = 1;
                    
                    // Render Modules
                    if ($modules): 
                        foreach($modules as $module): 
                    ?>
                        <div class="module-group bg-zinc-900/20 border border-white/5 rounded-3xl overflow-hidden">
                            <!-- Module Header -->
                            <div class="p-6 bg-zinc-900/60 border-b border-white/5 flex items-center justify-between cursor-pointer" onclick="this.nextElementSibling.classList.toggle('hidden')">
                                <div class="flex items-center gap-6">
                                    <div class="w-16 h-16 rounded-2xl bg-black border border-white/10 overflow-hidden flex-shrink-0">
                                        <?php if (has_post_thumbnail($module->ID)): ?>
                                            <?php echo get_the_post_thumbnail($module->ID, array(64, 64), array('class' => 'object-cover w-full h-full')); ?>
                                        <?php else: ?>
                                            <div class="w-full h-full flex items-center justify-center text-zinc-600">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-serif italic text-gold-500 mb-1"><?php echo esc_html($module->post_title); ?></h3>
                                        <div class="flex items-center gap-4">
                                            <p class="text-[10px] text-zinc-500 uppercase tracking-widest font-bold">
                                                <?php echo isset($lessons_by_module[$module->ID]) ? count($lessons_by_module[$module->ID]) . ' aulas' : 'Em breve'; ?>
                                            </p>
                                            <?php 
                                            $m_duration = get_post_meta($module->ID, '_lms_duration', true); 
                                            if($m_duration): 
                                            ?>
                                                <span class="w-1 h-1 bg-zinc-700 rounded-full"></span>
                                                <p class="text-[10px] text-gold-500/60 uppercase tracking-widest font-bold flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    <?php echo $m_duration; ?> min
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <svg class="w-5 h-5 text-zinc-600 transition-transform group-hover:text-gold-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>

                            <!-- Lessons in Module -->
                            <div class="module-lessons p-4 space-y-2">
                                <?php 
                                if(isset($lessons_by_module[$module->ID])):
                                    foreach($lessons_by_module[$module->ID] as $lesson): 
                                        $is_done = in_array($lesson->ID, $completed);
                                        $l_duration = get_post_meta($lesson->ID, '_lms_duration', true);
                                ?>
                                    <a href="<?php echo get_permalink($lesson->ID); ?>" class="group block p-4 bg-zinc-900/40 hover:bg-zinc-900 rounded-2xl border border-white/5 border-l-2 <?php echo $is_done ? 'border-l-gold-500' : 'border-l-zinc-800 hover:border-l-gold-500/50'; ?> transition-all">
                                                        <div class="flex items-center gap-6">
                                                            <div class="w-10 h-10 rounded-xl bg-black flex items-center justify-center text-zinc-600 group-hover:text-gold-500 transition-colors border border-white/5">
                                                                <?php if(!$has_access): ?>
                                                                    <svg class="w-4 h-4 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                                                <?php elseif($is_done): ?>
                                                                    <svg class="w-5 h-5 text-gold-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                                <?php else: ?>
                                                                    <span class="text-md font-serif italic"><?php echo str_pad($lesson_number++, 2, '0', STR_PAD_LEFT); ?></span>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="flex-1">
                                                                <h4 class="text-xs text-white font-medium group-hover:text-gold-400 transition-colors"><?php echo $lesson->post_title; ?></h4>
                                                                <div class="flex items-center gap-4 mt-1">
                                                                    <span class="text-[9px] text-zinc-500 uppercase tracking-widest"><?php echo $l_duration ? $l_duration . ' min' : 'Duração não informada'; ?></span>
                                                                    <?php if($is_done): ?>
                                                                        <span class="px-2 py-0.5 rounded bg-gold-500/10 text-gold-500 text-[7px] font-bold uppercase tracking-widest">Vídeo Visualizado</span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-zinc-700 md:opacity-0 group-hover:opacity-100 transition-all">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                                            </div>
                                                        </div>
                                    </a>
                                <?php 
                                    endforeach; 
                                else:
                                ?>
                                    <p class="text-[10px] text-zinc-600 uppercase tracking-widest italic text-center py-4 border border-dashed border-white/5 rounded-xl">Módulo sem aulas publicadas</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php 
                        endforeach; 
                    endif; 
                    ?>

                    <!-- Render Orphan Lessons if any -->
                    <?php if(!empty($orphan_lessons)): ?>
                        <div class="module-group bg-zinc-900/10 border border-white/5 rounded-3xl overflow-hidden mt-8 opacity-70">
                            <div class="p-6 bg-zinc-900/40 border-b border-white/5 flex items-center justify-between">
                                <h3 class="text-lg font-serif italic text-zinc-500 mb-1">Aulas Adicionais</h3>
                            </div>
                            <div class="module-lessons p-4 space-y-2">
                                <?php 
                                foreach($orphan_lessons as $lesson): 
                                    $is_done = in_array($lesson->ID, $completed);
                                    $l_duration = get_post_meta($lesson->ID, '_lms_duration', true);
                                ?>
                                    <a href="<?php echo get_permalink($lesson->ID); ?>" class="group block p-4 bg-zinc-900/40 hover:bg-zinc-900 rounded-2xl border border-white/5 border-l-2 <?php echo $is_done ? 'border-l-gold-500' : 'border-l-zinc-800 hover:border-l-gold-500/50'; ?> transition-all">
                                        <!-- Same internal structure for orphan lesson -->
                                        <div class="flex items-center gap-6">
                                            <div class="w-10 h-10 rounded-xl bg-black flex items-center justify-center text-zinc-600 group-hover:text-gold-500 transition-colors border border-white/5">
                                                <?php if(!$has_access): ?>
                                                    <svg class="w-4 h-4 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002 -2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                                <?php elseif($is_done): ?>
                                                    <svg class="w-5 h-5 text-gold-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                <?php else: ?>
                                                    <span class="text-md font-serif italic"><?php echo str_pad($lesson_number++, 2, '0', STR_PAD_LEFT); ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex-1">
                                                <h4 class="text-xs text-white font-medium group-hover:text-gold-400 transition-colors"><?php echo $lesson->post_title; ?></h4>
                                                <div class="flex items-center gap-4 mt-1">
                                                    <span class="text-[9px] text-zinc-500 uppercase tracking-widest"><?php echo $l_duration ? $l_duration . ' min' : 'Duração não informada'; ?></span>
                                                </div>
                                            </div>
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-zinc-700 md:opacity-0 group-hover:opacity-100 transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Side Column: Info -->
            <div class="space-y-8">
                <div class="p-8 bg-zinc-900/60 rounded-3xl border border-white/5 sticky top-8">
                    <h3 class="font-serif italic text-xl text-gold-500 mb-4">Sobre o Curso</h3>
                    <div class="text-zinc-400 text-sm leading-relaxed mb-8">
                        <?php the_content(); ?>
                    </div>
                    
                    <div class="space-y-4 pt-6 border-t border-zinc-800">
                        <div class="flex justify-between text-[10px] font-bold uppercase tracking-widest">
                            <span class="text-zinc-500">Conclusão</span>
                            <span class="text-gold-500"><?php echo $progress_pct; ?>%</span>
                        </div>
                        <div class="w-full h-1.5 bg-zinc-800 rounded-full overflow-hidden">
                            <div class="h-full bg-gold-500 shadow-[0_0_10px_#D4AF37]" style="width: <?php echo $progress_pct; ?>%"></div>
                        </div>
                        
                        <!-- Certificate Achievement Card -->
                        <?php 
                        $cert = new Expressive_Certificate();
                        if ($progress_pct >= 75): ?>
                            <div class="mt-10 p-6 bg-gold-500/10 border border-gold-500/20 rounded-2xl text-center group hover:bg-gold-500/20 transition-all shadow-xl shadow-gold-500/5">
                                <div class="w-16 h-16 rounded-full bg-gold-500 flex items-center justify-center text-black mx-auto mb-4 shadow-lg shadow-gold-500/20 group-hover:scale-110 transition-transform">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138z"></path></svg>
                                </div>
                                <h4 class="text-sm font-bold text-white uppercase tracking-widest mb-2 font-serif italic">Maestria Alcançada</h4>
                                <p class="text-[10px] text-zinc-400 uppercase tracking-widest mb-6 leading-relaxed">Você já concluiu mais de 75% deste treinamento e garantiu sua credencial de Especialista Elite!</p>
                                
                                <?php echo $cert->render_certificate_button( array( 'course_id' => $course_id ) ); ?>
                                
                                <p class="text-[8px] text-zinc-600 mt-4 uppercase tracking-[0.2em] font-bold">Documento Verificado & Autêntico</p>
                            </div>
                        <?php else: ?>
                            <div class="mt-10 p-6 bg-zinc-900/40 border border-white/5 rounded-2xl text-center opacity-40">
                                <svg class="w-8 h-8 text-zinc-700 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                <p class="text-[9px] text-zinc-600 uppercase tracking-widest leading-relaxed">Conclua 75% da jornada para liberar seu Certificado de Especialista.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>


<?php wp_footer(); ?>
<?php include EXPRESSIVE_CORE_PATH . 'templates/parts/visitor-indicator.php'; ?>
</body>
</html>
