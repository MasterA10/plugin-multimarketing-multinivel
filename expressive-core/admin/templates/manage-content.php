<?php
/**
 * Template Name: Admin Content Manager
 * 
 * Custom management interface for Courses and Lessons.
 */
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Acesso negado.' );
}

$current_page = isset( $_GET['page'] ) ? $_GET['page'] : 'elite-content';
$is_calendar = ($current_page === 'elite-calendar');
$post_type = $is_calendar ? 'lms_live' : 'lms_course';
$title = $is_calendar ? 'Mentorias & Lives' : 'Gerenciar Cursos & Aulas';
$icon = $is_calendar ? 'dashicons-calendar-alt' : 'dashicons-welcome-learn-more';

// Query posts
$posts = get_posts(array(
    'post_type' => $post_type,
    'posts_per_page' => -1,
    'post_status' => 'any'
));
?>

<div class="elite-admin-wrap bg-[#111] text-white min-h-screen p-8 rounded-xl shadow-2xl mr-4 mt-4 font-sans">
    
    <!-- Header -->
    <header class="flex justify-between items-center mb-10 border-b border-white/5 pb-8">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-gold-500/10 rounded-xl flex items-center justify-center text-gold-500 border border-gold-500/20">
                <span class="dashicons <?php echo $icon; ?>" style="font-size: 24px; width: 24px; height: 24px;"></span>
            </div>
            <div>
                <h1 class="font-serif italic text-3xl mb-1 leading-tight" style="color: #D4AF37 !important;"><?php echo $title; ?></h1>
                <p class="text-[10px] uppercase tracking-[0.25em] font-medium" style="color: rgba(242, 212, 128, 0.6) !important;">Gestão de Conteúdo de Elite</p>
            </div>
        </div>
        <div>
            <div class="flex gap-4">
                <?php if ($is_calendar): ?>
                    <a href="<?php echo admin_url('admin.php?page=' . $current_page . '&action=new&type=lms_live'); ?>" class="bg-gradient-to-r from-gold-600 to-gold-400 hover:from-gold-500 hover:to-gold-300 text-black px-8 py-3 rounded-xl text-xs font-bold uppercase tracking-widest transition-all shadow-lg hover:shadow-gold-500/20" style="background: linear-gradient(to right, #D4AF37, #F2D480) !important; color: #000 !important; text-decoration: none; display: inline-block;">
                        + Nova Mentoria
                    </a>
                <?php else: ?>
                    <a href="<?php echo admin_url('admin.php?page=' . $current_page . '&action=new&type=lms_course'); ?>" class="bg-gradient-to-r from-gold-600 to-gold-400 hover:from-gold-500 hover:to-gold-300 text-black px-6 py-3 rounded-xl text-xs font-bold uppercase tracking-widest transition-all shadow-lg hover:shadow-gold-500/20" style="background: linear-gradient(to right, #D4AF37, #F2D480) !important; color: #000 !important; text-decoration: none; display: inline-block;">
                        + Novo Curso
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=' . $current_page . '&action=new&type=lms_module'); ?>" class="px-6 py-3 border border-gold-500/50 rounded-xl text-xs font-bold uppercase tracking-widest text-gold-500 hover:bg-gold-500/10 transition-all font-serif italic" style="color: #D4AF37 !important; border-color: rgba(212, 175, 55, 0.8) !important;">
                        + Novo Módulo
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=' . $current_page . '&action=new&type=lms_lesson'); ?>" class="px-6 py-3 border border-gold-500/30 rounded-xl text-xs font-bold uppercase tracking-widest text-gold-500 hover:bg-gold-500/10 transition-all" style="color: #D4AF37 !important; border-color: rgba(212, 175, 55, 0.5) !important;">
                        + Nova Aula
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>


    <!-- Content List -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-separate border-spacing-y-3">
            <thead>
                <tr class="text-[11px] text-gold-400 font-bold uppercase tracking-widest border-b border-white/10">
                    <th class="px-6 pb-4">Título do Registro</th>
                    <th class="px-6 pb-4">Data de Criação</th>
                    <th class="px-6 pb-4">Status</th>
                    <?php if (!$is_calendar): ?>
                        <th class="px-6 pb-4">Aulas Vinculadas</th>
                    <?php endif; ?>
                    <th class="px-6 pb-4 text-right">Ações Rápidas</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($posts): foreach ($posts as $post): 
                    $edit_link = get_edit_post_link($post->ID);
                    $view_link = get_permalink($post->ID);
                    $status = get_post_status($post->ID);
                    $status_color = ($status === 'publish') ? 'text-green-500' : 'text-gray-500';
                ?>
                <tr class="glass hover:bg-white/5 transition-all group">
                    <td class="px-6 py-5 rounded-l-2xl border-l border-t border-b border-white/5">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-12 bg-onyx border border-white/10 rounded-lg overflow-hidden flex items-center justify-center text-gold-500">
                                <?php if (has_post_thumbnail($post->ID)): ?>
                                    <?php echo get_the_post_thumbnail($post->ID, array(40, 50), array('class' => 'object-cover w-full h-full')); ?>
                                <?php else: ?>
                                    <span class="dashicons <?php echo $icon; ?>"></span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-white group-hover:text-gold-400 transition-colors"><?php echo esc_html($post->post_title); ?></h4>
                                <p class="text-[10px] text-gray-500 mt-0.5">ID: #<?php echo $post->ID; ?> | Slug: <?php echo $post->post_name; ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-5 border-t border-b border-white/5">
                        <span class="text-xs text-gray-400"><?php echo get_the_date('d/m/Y', $post->ID); ?></span>
                    </td>
                    <td class="px-6 py-5 border-t border-b border-white/5">
                        <span class="text-[10px] font-bold uppercase tracking-widest <?php echo $status_color; ?>">
                            <?php echo $status === 'publish' ? 'Ativo' : 'Rascunho'; ?>
                        </span>
                    </td>
                    <?php if (!$is_calendar): ?>
                    <td class="px-6 py-5 border-t border-b border-white/5">
                        <?php 
                        $lessons = get_posts(array(
                            'post_type' => 'lms_lesson',
                            'meta_key' => '_lms_course_id',
                            'meta_value' => $post->ID,
                            'posts_per_page' => -1
                        ));
                        echo '<span class="px-2 py-1 bg-white/5 rounded-md text-[10px] font-bold text-gold-400">' . count($lessons) . ' Aulas</span>';
                        ?>
                    </td>
                    <?php endif; ?>
                    <td class="px-6 py-5 rounded-r-2xl border-r border-t border-b border-white/5 text-right">
                        <div class="flex items-center justify-end gap-3">
                            <?php if (!$is_calendar): ?>
                                <a href="<?php echo admin_url('admin.php?page=' . $current_page . '&action=curriculum&id=' . $post->ID); ?>" class="p-2 bg-blue-500/5 hover:bg-blue-500/20 text-blue-400 rounded-lg transition-all" title="Gerenciar Matriz e Ordem das Aulas">
                                    <span class="dashicons dashicons-list-view"></span>
                                </a>
                                <a href="<?php echo admin_url('admin.php?page=' . $current_page . '&action=new&type=lms_lesson&course_id=' . $post->ID); ?>" class="p-2 bg-gold-500/5 hover:bg-gold-500/20 text-gold-500 rounded-lg transition-all" title="Adicionar Aula a este Curso">
                                    <span class="dashicons dashicons-plus"></span>
                                </a>
                            <?php endif; ?>
                            <a href="<?php echo $view_link; ?>" target="_blank" class="p-2 bg-white/5 hover:bg-gold-500/10 text-gray-400 hover:text-gold-500 rounded-lg transition-all" title="Ver no Site">
                                <span class="dashicons dashicons-visibility"></span>
                            </a>
                            <a href="<?php echo admin_url('admin.php?page=' . $current_page . '&action=edit&id=' . $post->ID); ?>" class="p-2 bg-white/5 hover:bg-gold-500/10 text-gray-400 hover:text-gold-500 rounded-lg transition-all" title="Editar">
                                <span class="dashicons dashicons-edit"></span>
                            </a>
                            <a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=lms_delete_elite_content&post_id=' . $post->ID . '&redirect=' . $current_page ), 'lms_delete_content_nonce' ); ?>" 
                               onclick="return confirm('ATENÇÃO: Deseja realmente excluir este conteúdo de elite permanentemente?');"
                               class="p-2 bg-red-500/5 hover:bg-red-500/20 text-red-500/40 hover:text-red-500 rounded-lg transition-all" title="Excluir Permanentemente">
                                <span class="dashicons dashicons-trash"></span>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr>
                    <td colspan="5" class="px-6 py-20 text-center glass rounded-2xl border border-white/5 text-gray-500 italic">
                        Nenhum registro encontrado nesta categoria. Comece criando o seu primeiro conteúdo de elite.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<style>
    #wpcontent { background: #000 !important; padding-left: 0 !important; }
    #wpbody-content { padding-bottom: 0 !important; }
    .elite-admin-wrap { font-family: 'Outfit', sans-serif !important; }
    .elite-admin-wrap h1, .elite-admin-wrap h4 { font-family: 'Playfair Display', serif !important; }
    .glass { background: rgba(255, 255, 255, 0.02); }
    
    /* Force Override WP Admin Generic Styles */
    .elite-admin-wrap input, .elite-admin-wrap select {
        color: white !important;
        background-color: rgba(0,0,0,0.6) !important;
        border: 1px solid rgba(255,255,255,0.1) !important;
    }
    .elite-admin-wrap select option {
        background: #111 !important;
        color: white !important;
    }
</style>
