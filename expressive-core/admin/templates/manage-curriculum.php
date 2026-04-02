<?php
/**
 * Template Name: Elite Curriculum Manager
 * 
 * Manage lesson order and quick details for a specific course.
 */
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Acesso negado.' );
}

$course_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
$course = get_post($course_id);

if (!$course || $course->post_type !== 'lms_course') {
    wp_die('Curso não encontrado.');
}

// Fetch Modules
$modules = get_posts(array(
    'post_type'      => 'lms_module',
    'meta_query'     => array(array('key' => '_lms_course_id', 'value' => $course_id)),
    'posts_per_page' => -1,
    'orderby'        => 'menu_order',
    'order'          => 'ASC'
));

// Fetch Lessons
$all_course_lessons = get_posts(array(
    'post_type'      => 'lms_lesson',
    'meta_query'     => array(array('key' => '_lms_course_id', 'value' => $course_id)),
    'posts_per_page' => -1,
    'orderby'        => 'menu_order',
    'order'          => 'ASC'
));

$lessons_by_module = array();
$orphan_lessons = array();

foreach($all_course_lessons as $l) {
    $m_id = get_post_meta($l->ID, '_lms_module_id', true);
    if($m_id && get_post_type($m_id) === 'lms_module') {
        $lessons_by_module[$m_id][] = $l;
    } else {
        $orphan_lessons[] = $l;
    }
}

$current_page = isset( $_GET['page'] ) ? $_GET['page'] : 'elite-content';
?>

<div class="elite-admin-wrap bg-[#111] text-white min-h-screen p-8 rounded-xl shadow-2xl mr-4 mt-4 font-sans max-w-5xl animate-fade-in">
    
    <!-- Header -->
    <header class="flex justify-between items-center mb-10 border-b border-white/5 pb-8">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-gold-500/10 rounded-xl flex items-center justify-center text-gold-500 border border-gold-500/20">
                <span class="dashicons dashicons-networking" style="font-size: 24px; width: 24px; height: 24px;"></span>
            </div>
            <div>
                <h1 class="font-serif italic text-3xl mb-1 leading-tight" style="color: #D4AF37 !important;">Matriz de Módulos & Aulas</h1>
                <p class="text-[10px] uppercase tracking-[0.25em] font-medium" style="color: rgba(242, 212, 128, 0.6) !important;"><?php echo esc_html($course->post_title); ?></p>
            </div>
        </div>
        <div class="flex gap-4">
            <a href="<?php echo admin_url('admin.php?page=' . $current_page); ?>" class="px-6 py-3 border border-white/10 rounded-xl text-xs font-bold uppercase tracking-widest text-gray-400 hover:text-white transition-all">
                Voltar à Listagem
            </a>
            <button id="save-curriculum-order" class="bg-gradient-to-r from-gold-600 to-gold-400 hover:from-gold-500 hover:to-gold-300 text-black px-8 py-3 rounded-xl text-xs font-bold uppercase tracking-widest transition-all shadow-lg hover:shadow-gold-500/20 active:scale-95" style="background: linear-gradient(to right, #D4AF37, #F2D480) !important; color: #000 !important; font-weight: 800 !important;">
                Salvar Ordem Global
            </button>
        </div>
    </header>

    <div class="glass p-8 rounded-3xl border border-white/5 mb-10">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h3 class="text-sm font-bold uppercase tracking-widest flex items-center gap-2" style="color: #D4AF37 !important;">
                    <span class="w-1.5 h-4 bg-gold-500 rounded-full"></span>
                    Estrutura do Treinamento
                </h3>
                <p class="text-xs text-gray-500 mt-1 italic">Arraste os módulos para reordená-los. Para reordenar aulas, arraste-as dentro de seu módulo.</p>
            </div>
            <div class="flex gap-2">
                <a href="<?php echo admin_url('admin.php?page=' . $current_page . '&action=new&type=lms_module&course_id=' . $course_id); ?>" class="px-4 py-2 bg-gradient-to-r from-gold-600 to-gold-400 text-black shadow-lg shadow-gold-500/20 text-[9px] font-bold uppercase tracking-widest rounded-lg hover:scale-105 transition-all">
                    + Novo Módulo
                </a>
            </div>
        </div>

        <div id="modules-sortable" class="space-y-6">
            <?php if ($modules): foreach ($modules as $module): ?>
                <div class="module-item bg-onyx/40 border border-white/10 rounded-2xl overflow-hidden shadow-2xl relative group pb-4" data-module-id="<?php echo $module->ID; ?>">
                    
                    <!-- Module Header -->
                    <div class="flex items-center gap-4 p-4 bg-black/60 border-b border-white/5 cursor-move">
                        <div class="text-zinc-600 group-hover:text-gold-500 transition-colors">
                            <span class="dashicons dashicons-menu" style="font-size: 18px;"></span>
                        </div>
                        
                        <div class="w-10 h-10 rounded-lg bg-onyx border border-white/10 overflow-hidden flex-shrink-0">
                            <?php if (has_post_thumbnail($module->ID)): ?>
                                <?php echo get_the_post_thumbnail($module->ID, array(40, 40), array('class' => 'object-cover w-full h-full')); ?>
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center text-zinc-600">
                                    <span class="dashicons dashicons-category text-xs"></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="flex-1">
                            <h4 class="text-sm font-serif italic text-white group-hover:text-gold-400 transition-colors"><?php echo esc_html($module->post_title); ?></h4>
                            <div class="flex items-center gap-3 mt-0.5">
                                <span class="text-[8px] text-zinc-500 uppercase tracking-widest font-bold">Módulo #<?php echo $module->ID; ?></span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="<?php echo admin_url('admin.php?page=' . $current_page . '&action=new&type=lms_lesson&course_id=' . $course_id . '&module_id=' . $module->ID); ?>" class="p-2 border border-gold-500/20 text-gold-500 hover:bg-gold-500/10 rounded-lg transition-all text-[9px] font-bold uppercase" title="Adicionar Aula a este Módulo">
                                + Aula
                            </a>
                            <a href="<?php echo admin_url('admin.php?page=' . $current_page . '&action=edit&id=' . $module->ID); ?>" class="p-2 hover:bg-gold-500/10 text-gray-400 hover:text-gold-500 rounded-lg transition-all" title="Editar Módulo">
                                <span class="dashicons dashicons-edit text-sm"></span>
                            </a>
                        </div>
                    </div>

                    <!-- Lessons List inside Module -->
                    <div class="lessons-sortable px-4 mt-4 space-y-2 min-h-[50px]" id="lessons-for-<?php echo $module->ID; ?>">
                        <?php 
                        if (isset($lessons_by_module[$module->ID])): 
                            foreach ($lessons_by_module[$module->ID] as $lesson): 
                        ?>
                            <div class="curriculum-item flex items-center gap-4 p-3 bg-white/5 border border-white/5 rounded-xl hover:border-gold-500/30 transition-all cursor-move lesson-card" data-lesson-id="<?php echo $lesson->ID; ?>">
                                <div class="text-zinc-700 hover:text-gold-500 transition-colors">
                                    <span class="dashicons dashicons-menu" style="font-size: 14px;"></span>
                                </div>
                                <div class="flex-1">
                                    <h5 class="text-xs font-semibold text-white"><?php echo esc_html($lesson->post_title); ?></h5>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-[8px] text-zinc-500 uppercase tracking-widest">Aula #<?php echo $lesson->ID; ?> | <?php echo get_post_meta($lesson->ID, '_lms_duration', true) ?: '0'; ?> Minutos</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 opacity-50 hover:opacity-100 transition-all">
                                    <a href="<?php echo admin_url('admin.php?page=' . $current_page . '&action=edit&id=' . $lesson->ID); ?>" class="p-1.5 hover:bg-gold-500/10 text-gray-400 hover:text-gold-500 rounded-lg transition-all" title="Editar Aula">
                                        <span class="dashicons dashicons-edit text-xs"></span>
                                    </a>
                                </div>
                            </div>
                        <?php 
                            endforeach; 
                        else:
                        ?>
                            <div class="text-[9px] text-zinc-600 uppercase tracking-widest italic px-2 py-1">Sem aulas neste módulo ainda.</div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; else: ?>
                <div class="py-10 text-center glass rounded-2xl border border-white/5 text-gray-500 italic">
                    Nenhum módulo criado. Crie seu primeiro módulo para estruturar as aulas.
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Orphan Lessons -->
        <?php if (!empty($orphan_lessons)): ?>
        <div class="mt-12 pt-8 border-t border-red-500/20">
            <h4 class="text-sm font-bold uppercase tracking-widest text-red-500 mb-4 flex items-center gap-2">
                <span class="dashicons dashicons-warning"></span>
                Aulas "Órfãs" (Sem Módulo)
            </h4>
            <p class="text-xs text-zinc-500 mb-4">Estas aulas estão ligadas ao curso, mas precisam de um módulo na nova estrutura. Entre nelas e vincule a um módulo.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($orphan_lessons as $lesson): ?>
                    <div class="flex items-center justify-between p-3 bg-red-900/10 border border-red-500/30 rounded-xl lesson-card" data-lesson-id="<?php echo $lesson->ID; ?>">
                        <div class="flex-1">
                            <h5 class="text-xs font-semibold text-white"><?php echo esc_html($lesson->post_title); ?></h5>
                            <span class="text-[8px] text-zinc-500 uppercase tracking-widest">Aula #<?php echo $lesson->ID; ?></span>
                        </div>
                        <a href="<?php echo admin_url('admin.php?page=' . $current_page . '&action=edit&id=' . $lesson->ID); ?>" class="px-3 py-1.5 bg-red-500/20 text-red-400 border border-red-500/30 rounded text-[9px] font-bold uppercase hover:bg-red-500 hover:text-white transition-all">
                            Vincular
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        
        // Sortable Modules
        $("#modules-sortable").sortable({
            placeholder: "ui-state-highlight",
            opacity: 0.8,
            cursor: "move",
            axis: "y" // Restrict to Y axis
        });

        // Sortable Lessons within modules
        $(".lessons-sortable").sortable({
            placeholder: "ui-state-highlight",
            opacity: 0.8,
            cursor: "move",
            axis: "y"
            // connectWith: ".lessons-sortable" // Future feature: drag between modules?
        });

        // Save Order via AJAX
        $('#save-curriculum-order').on('click', function(e) {
            e.preventDefault();
            const btn = $(this);
            const originalText = btn.text();
            
            const moduleOrder = [];
            const lessonOrder = []; // We capture absolute order for all lessons top to bottom.
            
            $('.module-item').each(function() {
                const id = $(this).attr('data-module-id');
                if (id) moduleOrder.push(id);
            });

            // Iterate lessons as they appear visually (top to bottom across all modules)
            // This assigns global menu_order properly.
            $('.lesson-card').each(function() {
                const id = $(this).attr('data-lesson-id');
                if (id) lessonOrder.push(id);
            });

            if (moduleOrder.length === 0 && lessonOrder.length === 0) {
                alert('Matriz vazia.');
                return;
            }

            btn.text('Processando...').addClass('opacity-50 pointer-events-none');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'lms_update_module_and_lesson_order',
                    nonce: '<?php echo wp_create_nonce("lms_save_elite_content_nonce"); ?>',
                    module_order: moduleOrder,
                    lesson_order: lessonOrder
                },
                success: function(response) {
                    if(response.success) {
                        btn.text('Salvo com Sucesso!').css('background', '#10b981').css('color', 'white');
                        setTimeout(() => {
                            btn.text(originalText).css('background', '').css('color', '').removeClass('opacity-50 pointer-events-none');
                        }, 2000);
                    } else {
                        alert('Erro ao salvar ordem: ' + (response.data || 'Erro desconhecido'));
                        btn.text('Erro! Tentar novamente').removeClass('opacity-50 pointer-events-none');
                    }
                },
                error: function() {
                    alert('Erro de comunicação com o servidor. Tente novamente.');
                    btn.text('Erro fatal').removeClass('opacity-50 pointer-events-none');
                }
            });
        });
    });
</script>

<style>
    #wpcontent { background: #000 !important; padding-left: 0 !important; }
    #wpbody-content { padding-bottom: 0 !important; }
    .elite-admin-wrap { font-family: 'Outfit', sans-serif !important; }
    .elite-admin-wrap h1, .elite-admin-wrap h3, .elite-admin-wrap h4 { font-family: 'Playfair Display', serif !important; }
    .glass { background: rgba(255, 255, 255, 0.02); }
    .animate-fade-in { animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .ui-state-highlight { background: rgba(212, 175, 55, 0.1); border: 1px dashed rgba(212, 175, 55, 0.5); min-height: 50px; border-radius: 12px; margin-bottom: 8px; }
</style>
