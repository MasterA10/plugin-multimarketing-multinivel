<?php
/**
 * Template Name: Elite Editor (Zero Gutenberg)
 * 
 * Custom management interface for creating and editing all LMS content.
 */
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Acesso negado.' );
}

$action = isset( $_GET['action'] ) ? $_GET['action'] : 'new';
$post_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
$current_page = isset( $_GET['page'] ) ? $_GET['page'] : 'elite-content';

// Determine Content Type
$post_obj = ($post_id > 0) ? get_post($post_id) : null;
$post_type = $post_obj ? $post_obj->post_type : (isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'lms_course');
$is_calendar = ($current_page === 'elite-calendar' || $post_type === 'lms_live');

if ($is_calendar) $post_type = 'lms_live';

// Load Data
$title = $post_obj ? $post_obj->post_title : '';
$content = $post_obj ? $post_obj->post_content : '';
$youtube_id = $post_obj ? get_post_meta($post_id, '_lms_youtube_id', true) : '';
$course_id = $post_obj ? get_post_meta($post_id, '_lms_course_id', true) : (isset($_GET['course_id']) ? intval($_GET['course_id']) : '');
$module_id = $post_obj ? get_post_meta($post_id, '_lms_module_id', true) : (isset($_GET['module_id']) ? intval($_GET['module_id']) : '');
$duration = $post_obj ? get_post_meta($post_id, '_lms_duration', true) : '';
$professor_name = $post_obj ? get_post_meta($post_id, '_lms_professor_name', true) : '';
$live_date = $post_obj ? get_post_meta($post_id, '_lms_live_date', true) : '';
$live_time = $post_obj ? get_post_meta($post_id, '_lms_live_time', true) : '';
$lesson_date = $post_obj ? get_post_meta($post_id, '_lms_lesson_date', true) : '';

// Titles
$type_label = ($post_type === 'lms_course') ? 'Curso de Elite' : (($post_type === 'lms_module') ? 'Módulo de Elite' : (($post_type === 'lms_lesson') ? 'Aula de Elite' : 'Mentoria de Elite'));
$editor_title = ($post_id > 0) ? 'Editar ' . $type_label : 'Criar ' . $type_label;
?>

<div class="elite-admin-wrap bg-[#111] text-white min-h-screen p-8 rounded-xl shadow-2xl mr-4 mt-4 font-sans max-w-5xl animate-fade-in">
    
    <!-- Editor Header -->
    <header class="flex justify-between items-center mb-10 border-b border-white/5 pb-8">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-gold-500/10 rounded-xl flex items-center justify-center text-gold-500 border border-gold-500/20">
                <span class="dashicons <?php echo ($post_type === 'lms_course') ? 'dashicons-welcome-learn-more' : 'dashicons-edit'; ?>" style="font-size: 24px; width: 24px; height: 24px;"></span>
            </div>
            <div>
                <h1 id="dynamic-editor-title" class="font-serif italic text-3xl mb-1 leading-tight" style="color: #D4AF37 !important;"><?php echo $editor_title; ?></h1>
                <p class="text-[10px] uppercase tracking-[0.25em] font-medium" style="color: rgba(242, 212, 128, 0.6) !important;">Ambiente de Criação de Elite (Zero Gutenberg)</p>
            </div>
        </div>
        <div class="flex gap-4">
            <a href="<?php echo admin_url('admin.php?page=' . $current_page); ?>" class="px-6 py-3 border border-white/10 rounded-xl text-xs font-bold uppercase tracking-widest text-gray-400 hover:text-white transition-all">
                Cancelar / Voltar
            </a>
            <button type="button" onclick="document.getElementById('elite-editor-form').submit();" class="bg-gradient-to-r from-gold-600 to-gold-400 hover:from-gold-500 hover:to-gold-300 text-black px-8 py-3 rounded-xl text-xs font-bold uppercase tracking-widest transition-all shadow-lg hover:shadow-gold-500/20 active:scale-95" style="background: linear-gradient(to right, #D4AF37, #F2D480) !important; color: #000 !important; font-weight: 800 !important;">
                <?php echo ($post_id > 0) ? 'Atualizar e Salvar' : 'Publicar Agora'; ?>
            </button>
        </div>
    </header>

    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" id="elite-editor-form">
        <input type="hidden" name="action" value="lms_save_elite_content">
        <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
        <input type="hidden" name="post_type" id="hidden-post-type" value="<?php echo $post_type; ?>">
        <input type="hidden" name="redirect_page" value="<?php echo $current_page; ?>">
        <?php wp_nonce_field('lms_save_elite_content_nonce', 'lms_nonce'); ?>

        <?php if ($post_id <= 0 && !$is_calendar): ?>
        <!-- Smart Type Selector (Only for new records) -->
        <div class="mb-10 flex gap-4 p-2 bg-black/40 rounded-2xl border border-white/5 w-fit">
            <button type="button" onclick="setEditorType('lms_course')" class="type-btn px-8 py-3 rounded-xl text-[10px] font-bold uppercase tracking-widest transition-all <?php echo ($post_type === 'lms_course') ? 'bg-gold-500 text-black shadow-lg shadow-gold-500/20' : 'text-gray-400 border border-white/10 hover:border-gold-500/50'; ?>" data-type="lms_course" style="<?php echo ($post_type === 'lms_course') ? 'background-color: #D4AF37 !important; color: #000 !important;' : ''; ?>">Arquiteto de Curso</button>
            <button type="button" onclick="setEditorType('lms_module')" class="type-btn px-8 py-3 rounded-xl text-[10px] font-bold uppercase tracking-widest transition-all <?php echo ($post_type === 'lms_module') ? 'bg-gold-500 text-black shadow-lg shadow-gold-500/20' : 'text-gray-400 border border-white/10 hover:border-gold-500/50'; ?>" data-type="lms_module" style="<?php echo ($post_type === 'lms_module') ? 'background-color: #D4AF37 !important; color: #000 !important;' : ''; ?>">Criador de Módulo</button>
            <button type="button" onclick="setEditorType('lms_lesson')" class="type-btn px-8 py-3 rounded-xl text-[10px] font-bold uppercase tracking-widest transition-all <?php echo ($post_type === 'lms_lesson') ? 'bg-gold-500 text-black shadow-lg shadow-gold-500/20' : 'text-gray-400 border border-white/10 hover:border-gold-500/50'; ?>" data-type="lms_lesson" style="<?php echo ($post_type === 'lms_lesson') ? 'background-color: #D4AF37 !important; color: #000 !important;' : ''; ?>">Criador de Aula</button>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            
            <!-- Main Content Area -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Identification Section -->
                <div class="glass p-8 rounded-3xl border border-white/5 space-y-6">
                    <h3 class="text-sm font-bold uppercase tracking-widest flex items-center gap-2" style="color: #D4AF37 !important;">
                        <span class="w-1.5 h-4 bg-gold-500 rounded-full"></span>
                        Identidade do Conteúdo
                    </h3>
                    
                    <div class="space-y-2">
                        <label id="label-post-title" class="text-[10px] text-gray-500 uppercase tracking-widest"><?php 
                            if ($post_type === 'lms_course') echo 'Título do Curso';
                            elseif ($post_type === 'lms_module') echo 'Título do Módulo';
                            elseif ($post_type === 'lms_lesson') echo 'Título da Aula';
                            else echo 'Título da Mentoria (Live)';
                        ?></label>
                        <input type="text" name="post_title" value="<?php echo esc_attr($title); ?>" required class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-4 text-white focus:border-gold-500/50 transition-all outline-none font-serif text-lg italic" placeholder="Título impactante e luxuoso...">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] text-gray-500 uppercase tracking-widest">Professor / Mentor Responsável</label>
                            <input type="text" name="lms_professor_name" value="<?php echo esc_attr($professor_name); ?>" class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-4 text-white focus:border-gold-500/50 transition-all outline-none font-sans text-sm" placeholder="Nome do Professor...">
                        </div>

                        <!-- Schedule Section (Only for Lessons and Lives) -->
                        <div id="wrapper-scheduling" class="space-y-2 <?php echo ($post_type === 'lms_course' || $post_type === 'lms_module') ? 'hidden' : ''; ?>">
                            <label class="text-[10px] text-gray-500 uppercase tracking-widest">Agendamento (Calendário)</label>
                            <div class="flex gap-4">
                                <div class="flex-1 <?php echo ($post_type === 'lms_live') ? 'hidden' : ''; ?>" id="wrapper-lesson-date">
                                    <input type="date" name="lms_lesson_date" value="<?php echo esc_attr($lesson_date); ?>" class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-4 text-white focus:border-gold-500/50 outline-none text-xs">
                                </div>
                                <div class="flex-1 <?php echo ($post_type !== 'lms_live') ? 'hidden' : ''; ?>" id="wrapper-live-date">
                                    <input type="date" name="lms_live_date" value="<?php echo esc_attr($live_date); ?>" class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-4 text-white focus:border-gold-500/50 outline-none text-xs">
                                </div>
                                <div class="w-32 <?php echo ($post_type !== 'lms_live') ? 'hidden' : ''; ?>" id="wrapper-live-time">
                                    <input type="time" name="lms_live_time" value="<?php echo esc_attr($live_time); ?>" class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-4 text-white focus:border-gold-500/50 outline-none text-xs">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[11px] font-bold uppercase tracking-widest" style="color: rgba(212, 175, 55, 0.8) !important;"><?php echo ($post_type === 'lms_live') ? 'Pauta Estratégica' : 'Descrição detalhada'; ?></label>
                        <textarea name="post_content" class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-4 text-white focus:border-gold-500/50 transition-all outline-none min-h-[300px] leading-relaxed text-sm" placeholder="<?php echo ($post_type === 'lms_live') ? 'Defina os pontos de destaque desta mentoria ao vivo...' : 'Escreva o conteúdo estratégico aqui...'; ?>"><?php echo esc_textarea($content); ?></textarea>
                    </div>

                    <!-- Materials Manager Section (Lessons Only) -->
                    <div id="section-materials" class="glass p-8 rounded-3xl border border-white/5 space-y-6 <?php echo ($post_type !== 'lms_lesson') ? 'hidden' : ''; ?>">
                        <div class="flex justify-between items-center">
                            <h3 class="text-sm font-bold uppercase tracking-widest flex items-center gap-2" style="color: #D4AF37 !important;">
                                <span class="w-1.5 h-4 bg-gold-500 rounded-full"></span>
                                Materiais de Apoio (PDF, Apostilas)
                            </h3>
                            <button type="button" id="elite-add-file-btn" class="text-[10px] font-bold uppercase tracking-widest bg-gold-500/10 text-gold-500 border border-gold-500/20 px-4 py-2 rounded-lg hover:bg-gold-500 hover:text-black transition-all">
                                Adicionar Material
                            </button>
                        </div>

                        <div id="elite-materials-list" class="space-y-3">
                            <?php
                            $files = $post_id ? get_post_meta( $post_id, '_lms_files_data', true ) : array();
                            if ( ! is_array( $files ) ) $files = array();

                            foreach ( $files as $index => $file ) :
                            ?>
                                <div class="elite-file-item flex items-center gap-4 bg-white/5 p-4 rounded-xl border border-white/5 group">
                                    <span class="dashicons dashicons-media-document text-gray-500"></span>
                                    <input type="text" name="lms_files[<?php echo $index; ?>][name]" value="<?php echo esc_attr( $file['name'] ); ?>" class="flex-1 bg-transparent border-none text-sm text-white focus:ring-0" placeholder="Título do Material...">
                                    <input type="hidden" name="lms_files[<?php echo $index; ?>][id]" value="<?php echo esc_attr( $file['id'] ); ?>">
                                    <span class="text-[9px] text-gray-600 uppercase font-mono max-w-[100px] truncate"><?php echo basename( get_attached_file( $file['id'] ) ); ?></span>
                                    <button type="button" class="elite-remove-file text-red-500/50 hover:text-red-500 transition-colors">
                                        <span class="dashicons dashicons-dismiss" style="font-size: 16px;"></span>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <input type="hidden" id="elite-files-next-index" value="<?php echo count( $files ); ?>">
                        <p class="text-[9px] text-gray-600 italic">Estes arquivos serão exibidos para download abaixo do vídeo da aula.</p>
                    </div>
                </div>

            </div>

            <!-- Side Configuration Area -->
            <div class="space-y-8">
                
                <!-- Media Section -->
                <div id="section-media" class="bg-white/5 p-8 rounded-3xl border border-white/10 space-y-6 <?php echo ($post_type === 'lms_course') ? 'opacity-50 pointer-events-none' : ''; ?>">
                    <h3 class="text-sm font-bold uppercase tracking-widest flex items-center gap-2" style="color: #D4AF37 !important;">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path></svg>
                        Multimedia
                    </h3>
                    
                    <div id="wrapper-youtube-id" class="space-y-2 <?php echo ($post_type === 'lms_module' || $post_type === 'lms_course') ? 'hidden' : ''; ?>">
                        <label class="text-[11px] font-bold uppercase tracking-widest" style="color: rgba(212, 175, 55, 0.8) !important;">Vídeo do YouTube (ID ou URL)</label>
                        <input type="text" name="lms_youtube_id" value="<?php echo esc_attr($youtube_id); ?>" class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-gold-400 font-mono text-xs focus:border-gold-500/50 transition-all outline-none placeholder-gray-700" placeholder="Ex: dQw4w9WgXcQ">
                        <p class="text-[9px] text-gray-500 leading-relaxed italic">Coloque o ID ou a URL; o sistema resolve para você.</p>
                    </div>

                    <div id="wrapper-duration" class="space-y-2 pt-4 border-t border-white/5">
                        <label class="text-[11px] font-bold uppercase tracking-widest" style="color: rgba(212, 175, 55, 0.8) !important;">
                            Duração (Minutos) 
                            <?php if($post_type === 'lms_module') echo '<span class="text-xs italic lowercase">(calculado pela soma das aulas)</span>'; ?>
                        </label>
                        <div class="flex items-center gap-3">
                            <input type="number" name="lms_duration" value="<?php echo esc_attr($duration); ?>" <?php if($post_type === 'lms_module') echo 'readonly'; ?> class="w-24 bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-gold-400 font-bold focus:border-gold-500/50 transition-all outline-none <?php if($post_type === 'lms_module') echo 'opacity-50 cursor-not-allowed'; ?>" placeholder="0">
                            <span class="text-[10px] text-gray-400 uppercase font-bold tracking-widest">MINUTOS</span>
                        </div>
                        <p class="text-[9px] text-gray-500 italic mt-1">Isso será contabilizado nas Horas de Treino do aluno.</p>
                    </div>
                </div>

                <!-- Thumbnail Section (Hidden for lessons) -->
                <div id="section-thumbnail" class="bg-white/5 p-8 rounded-3xl border border-white/10 space-y-6 <?php echo ($post_type === 'lms_lesson') ? 'hidden' : ''; ?>">
                    <h3 class="text-sm font-bold uppercase tracking-widest flex items-center gap-2" style="color: #D4AF37 !important;">
                        <span class="dashicons dashicons-format-image"></span>
                        Capa de Elite
                    </h3>
                    <div id="elite-media-trigger" class="aspect-[4/5] w-full bg-black/40 rounded-2xl border border-white/10 border-dashed flex flex-col items-center justify-center group cursor-pointer hover:border-gold-500/50 transition-all overflow-hidden relative">
                            <?php if (has_post_thumbnail($post_id)): ?>
                            <?php echo get_the_post_thumbnail($post_id, 'medium', array('class' => 'w-full h-full object-cover', 'id' => 'elite-preview-img')); ?>
                            <div class="absolute inset-0 bg-black/60 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all">
                                <span class="text-[10px] font-bold uppercase tracking-widest text-white">Trocar Capa</span>
                            </div>
                            <?php else: ?>
                            <img id="elite-preview-img" src="" class="hidden w-full h-full object-cover">
                            <div id="elite-placeholder-icon" class="flex flex-col items-center">
                                <span class="dashicons dashicons-format-image text-gold-500/30 mb-2" style="font-size: 32px; width: 32px; height: 32px;"></span>
                                <span class="text-[10px] text-gold-400 font-bold uppercase tracking-widest">Selecionar Imagem</span>
                            </div>
                            <?php endif; ?>
                            <input type="hidden" name="_thumbnail_id" id="_thumbnail_id_field" value="<?php echo get_post_thumbnail_id($post_id); ?>">
                    </div>
                </div>

                <!-- Association Section -->
                <div id="section-association" class="bg-white/5 p-8 rounded-3xl border border-white/10 space-y-6 <?php echo ($post_type === 'lms_course') ? 'hidden' : ''; ?>">
                    <h3 class="text-sm font-bold uppercase tracking-widest flex items-center gap-2" style="color: #D4AF37 !important;">
                        <span class="dashicons dashicons-networking"></span>
                        Vínculo Global
                    </h3>
                    
                    <div class="space-y-2 <?php echo ($post_type === 'lms_lesson') ? 'hidden' : ''; ?>" id="wrapper-course-id">
                        <label class="text-[10px] text-gray-500 uppercase tracking-widest">Pertence ao Curso:</label>
                        <select name="lms_course_id" id="lms_course_id" class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-xs text-white focus:border-gold-500/50 outline-none">
                            <option value="">-- Selecione o Curso --</option>
                            <?php 
                            $courses = get_posts(array('post_type' => 'lms_course', 'posts_per_page' => -1));
                            foreach ($courses as $c): ?>
                                <option value="<?php echo $c->ID; ?>" <?php selected($course_id, $c->ID); ?>><?php echo esc_html($c->post_title); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="space-y-2 <?php echo ($post_type !== 'lms_lesson') ? 'hidden' : ''; ?>" id="wrapper-module-id">
                        <label class="text-[10px] text-gray-500 uppercase tracking-widest">Pertence ao Módulo (Vinculado a um Curso):</label>
                        <select name="lms_module_id" id="lms_module_id" class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-xs text-white focus:border-gold-500/50 outline-none">
                            <option value="">-- Selecione o Módulo --</option>
                            <?php 
                            $modules = get_posts(array('post_type' => 'lms_module', 'posts_per_page' => -1));
                            foreach ($modules as $m): 
                                $m_course_id = get_post_meta($m->ID, '_lms_course_id', true);
                                $course_name = $m_course_id ? get_the_title($m_course_id) : 'Sem Curso';
                            ?>
                                <option value="<?php echo $m->ID; ?>" <?php selected($module_id, $m->ID); ?>><?php echo esc_html($m->post_title . ' (' . $course_name . ')'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Visibility -->
                <div class="bg-white/5 p-8 rounded-3xl border border-white/10">
                    <label class="text-[10px] text-gray-500 uppercase tracking-widest block mb-3">Visibilidade</label>
                    <select name="post_status" class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-xs text-white focus:border-gold-500/50 outline-none">
                        <option value="publish" <?php selected(get_post_status($post_id), 'publish'); ?>>Publicado (Ativo)</option>
                        <option value="draft" <?php selected(get_post_status($post_id), 'draft'); ?>>Rascunho (Privado)</option>
                    </select>
                </div>

                <!-- RBAC Level -->
                <?php $visibility_role = $post_obj ? get_post_meta($post_id, '_lms_visibility_role', true) : 'all'; ?>
                <div class="bg-white/5 p-8 rounded-3xl border border-white/10 group hover:border-gold-500/30 transition-all shadow-[0_0_15px_rgba(212,175,55,0.05)] hover:shadow-[0_0_20px_rgba(212,175,55,0.15)]">
                    <label class="text-[10px] text-gold-500 uppercase font-bold tracking-widest block mb-3 flex items-center gap-2">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        Nível de Acesso (RBAC)
                    </label>
                    <select name="lms_visibility_role" class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-xs text-white focus:border-gold-500/50 outline-none">
                        <option value="all" <?php selected($visibility_role, 'all'); ?>>Aberto (Todos os Membros)</option>
                        <option value="educadora" <?php selected($visibility_role, 'educadora'); ?>>Apenas Educadoras (Exclusivo)</option>
                        <option value="autoridade" <?php selected($visibility_role, 'autoridade'); ?>>Apenas Autoridades (Base)</option>
                    </select>
                    <p class="text-[9px] text-gray-500 italic mt-3 leading-relaxed">
                        Ao restringir, esse material sequer aparecerá no dashboard para usuários da licença não autorizada.
                    </p>
                </div>

            </div>
        </div>
    </form>

</div>

<script>
    function setEditorType(type) {
        // Update hidden field
        document.getElementById('hidden-post-type').value = type;
        
        // Update buttons
        document.querySelectorAll('.type-btn').forEach(btn => {
            if(btn.dataset.type === type) {
                btn.style.backgroundColor = '#D4AF37';
                btn.style.color = '#000';
                btn.classList.add('shadow-lg', 'shadow-gold-500/20');
                btn.classList.remove('text-gray-400', 'border-white/10');
            } else {
                btn.style.backgroundColor = 'transparent';
                btn.style.color = 'rgba(255,255,255,0.4)';
                btn.classList.remove('shadow-lg', 'shadow-gold-500/20');
                btn.classList.add('text-gray-400', 'border-white/10');
            }
        });

        // Update Labels & Visibility
        const titleLabel = document.getElementById('label-post-title');
        const mediaSection = document.getElementById('section-media');
        const assocSection = document.getElementById('section-association');
        const thumbSection = document.getElementById('section-thumbnail');
        const dynamicTitle = document.getElementById('dynamic-editor-title');
        
        const wrapCourseId = document.getElementById('wrapper-course-id');
        const wrapModuleId = document.getElementById('wrapper-module-id');
        
        const wrapScheduling = document.getElementById('wrapper-scheduling');
        const wrapLessonDate = document.getElementById('wrapper-lesson-date');
        const wrapLiveDate = document.getElementById('wrapper-live-date');
        const wrapLiveTime = document.getElementById('wrapper-live-time');

        if(type === 'lms_course') {
            titleLabel.innerText = 'Título do Curso';
            mediaSection.classList.add('opacity-50', 'pointer-events-none');
            assocSection.classList.add('hidden');
            thumbSection.classList.remove('hidden');
            dynamicTitle.innerText = 'Criar Curso de Elite';
            wrapScheduling.classList.add('hidden');
            document.getElementById('wrapper-youtube-id').classList.add('hidden');
        } else if(type === 'lms_module') {
            titleLabel.innerText = 'Título do Módulo';
            mediaSection.classList.remove('opacity-50', 'pointer-events-none');
            document.getElementById('wrapper-youtube-id').classList.add('hidden');
            document.getElementById('wrapper-duration').querySelector('input').readOnly = true;
            document.getElementById('wrapper-duration').querySelector('input').classList.add('opacity-50', 'cursor-not-allowed');
            
            assocSection.classList.remove('hidden');
            thumbSection.classList.remove('hidden');
            wrapCourseId.classList.remove('hidden');
            wrapModuleId.classList.add('hidden');
            dynamicTitle.innerText = 'Criar Módulo de Elite';
            wrapScheduling.classList.add('hidden');
        } else if(type === 'lms_lesson') {
            titleLabel.innerText = 'Título da Aula';
            mediaSection.classList.remove('opacity-50', 'pointer-events-none');
            document.getElementById('wrapper-youtube-id').classList.remove('hidden');
            document.getElementById('wrapper-duration').querySelector('input').readOnly = false;
            document.getElementById('wrapper-duration').querySelector('input').classList.remove('opacity-50', 'cursor-not-allowed');
            
            assocSection.classList.remove('hidden');
            thumbSection.classList.add('hidden');
            wrapCourseId.classList.add('hidden');
            wrapModuleId.classList.remove('hidden');
            dynamicTitle.innerText = 'Criar Aula de Elite';
            wrapScheduling.classList.remove('hidden');
            wrapLessonDate.classList.remove('hidden');
            wrapLiveDate.classList.add('hidden');
            wrapLiveTime.classList.add('hidden');
            
            // Show Materials Section
            document.getElementById('section-materials').classList.remove('hidden');
        } else {
            // Live / Mentoria
            titleLabel.innerText = 'Título da Mentoria (Live)';
            wrapScheduling.classList.remove('hidden');
            wrapLessonDate.classList.add('hidden');
            wrapLiveDate.classList.remove('hidden');
            wrapLiveTime.classList.remove('hidden');
            
            // Hide Materials for Lives (optional/standard)
            document.getElementById('section-materials').classList.add('hidden');
        }
    }

    jQuery(document).ready(function($) {
        // Media Library Integration
        $('#elite-media-trigger').on('click', function(e) {
            e.preventDefault();
            var frame = wp.media({
                title: 'Selecionar Capa de Elite',
                button: { text: 'Usar esta Imagem' },
                multiple: false
            });
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                
                // Get pre-determined sizes for preview
                var imgUrl = (attachment.sizes && attachment.sizes.medium) ? attachment.sizes.medium.url : attachment.url;
                
                // Update hidden field for saving
                $('#_thumbnail_id_field').val(attachment.id);
                
                // Update and Show Preview with animation
                const $preview = $('#elite-preview-img');
                $preview.attr('src', imgUrl)
                        .removeAttr('srcset')
                        .removeAttr('sizes')
                        .removeClass('hidden')
                        .addClass('animate-modal-in')
                        .show();
                
                $('#elite-placeholder-icon').hide();

                // Visual Feedback: pulse background
                $('#elite-media-trigger').addClass('border-gold-500 bg-gold-500/10');
                setTimeout(() => {
                    $('#elite-media-trigger').removeClass('bg-gold-500/10');
                    $preview.removeClass('animate-modal-in');
                }, 1000);
            });
            frame.open();
        });

        // Materials Manager JS Integration
        var materialsFrame;
        $('#elite-add-file-btn').on('click', function(e) {
            e.preventDefault();
            if (materialsFrame) { materialsFrame.open(); return; }
            
            materialsFrame = wp.media({
                title: 'Selecionar Materiais de Apoio',
                button: { text: 'Adicionar à Aula' },
                multiple: true
            });

            materialsFrame.on('select', function() {
                var selections = materialsFrame.state().get('selection');
                var nextIndex = parseInt($('#elite-files-next-index').val());
                
                selections.map(function(attachment) {
                    attachment = attachment.toJSON();
                    var html = `
                        <div class="elite-file-item flex items-center gap-4 bg-white/5 p-4 rounded-xl border border-white/5 group animate-fade-in">
                            <span class="dashicons dashicons-media-document text-gray-500"></span>
                            <input type="text" name="lms_files[${nextIndex}][name]" value="${attachment.title}" class="flex-1 bg-transparent border-none text-sm text-white focus:ring-0" placeholder="Título do Material...">
                            <input type="hidden" name="lms_files[${nextIndex}][id]" value="${attachment.id}">
                            <span class="text-[9px] text-gray-600 uppercase font-mono max-w-[100px] truncate">${attachment.filename}</span>
                            <button type="button" class="elite-remove-file text-red-500/50 hover:text-red-500 transition-colors">
                                <span class="dashicons dashicons-dismiss" style="font-size: 16px;"></span>
                            </button>
                        </div>
                    `;
                    $('#elite-materials-list').append(html);
                    nextIndex++;
                });
                $('#elite-files-next-index').val(nextIndex);
            });
            materialsFrame.open();
        });

        $(document).on('click', '.elite-remove-file', function(e) {
            e.preventDefault();
            $(this).closest('.elite-file-item').fadeOut(300, function() { $(this).remove(); });
        });
    });
</script>

<style>
    #wpcontent { background: #000 !important; padding-left: 0 !important; }
    #wpbody-content { padding-bottom: 0 !important; }
    .elite-admin-wrap { font-family: 'Outfit', sans-serif !important; }
    .elite-admin-wrap h1, .elite-admin-wrap h3, .elite-admin-wrap input, .elite-admin-wrap textarea { 
        font-family: 'Playfair Display', serif !important; 
    }
    .glass { background: rgba(255, 255, 255, 0.02); }
    .animate-fade-in { animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    .elite-admin-wrap input, 
    .elite-admin-wrap textarea, 
    .elite-admin-wrap select {
        color: white !important;
        background-color: rgba(0,0,0,0.6) !important;
        border: 1px solid rgba(255,255,255,0.1) !important;
    }
    .elite-admin-wrap input::placeholder, 
    .elite-admin-wrap textarea::placeholder {
        color: rgba(255,255,255,0.2) !important;
    }
    .elite-admin-wrap select option {
        background: #111 !important;
        color: white !important;
    }
</style>
