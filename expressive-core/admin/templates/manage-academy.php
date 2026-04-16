<?php
/**
 * Template Name: Academy Member Manager
 * 
 * Custom management interface for Academy Members.
 */
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Acesso negado.' );
}

$current_page = 'elite-academy';
$post_type = 'academy_member';

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
                <span class="dashicons dashicons-groups" style="font-size: 24px; width: 24px; height: 24px;"></span>
            </div>
            <div>
                <h1 class="font-serif italic text-3xl mb-1 leading-tight" style="color: #D4AF37 !important;">Equipe da Academia</h1>
                <p class="text-[10px] uppercase tracking-[0.25em] font-medium" style="color: rgba(242, 212, 128, 0.6) !important;">Gestão de Profissionais PMU Build</p>
            </div>
        </div>
        <div>
            <a href="<?php echo admin_url('admin.php?page=' . $current_page . '&action=new&type=' . $post_type); ?>" class="bg-gradient-to-r from-gold-600 to-gold-400 hover:from-gold-500 hover:to-gold-300 text-black px-8 py-3 rounded-xl text-xs font-bold uppercase tracking-widest transition-all shadow-lg hover:shadow-gold-500/20" style="background: linear-gradient(to right, #D4AF37, #F2D480) !important; color: #000 !important; text-decoration: none; display: inline-block;">
                + Novo Membro
            </a>
        </div>
    </header>

    <?php if ( isset( $_GET['status'] ) ) : ?>
        <div class="mb-6 p-4 bg-gold-500/10 border border-gold-500/20 rounded-xl text-gold-500 text-xs font-bold uppercase tracking-widest">
            <?php echo $_GET['status'] === 'saved' ? '✨ Alterações salvas com sucesso!' : '🗑️ Membro removido definitivamente.'; ?>
        </div>
    <?php endif; ?>

    <!-- Content List -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-separate border-spacing-y-3">
            <thead>
                <tr class="text-[11px] text-gold-400 font-bold uppercase tracking-widest border-b border-white/10">
                    <th class="px-6 pb-4">Profissional</th>
                    <th class="px-6 pb-4 text-center">Nível / Categoria</th>
                    <th class="px-6 pb-4 text-center">Instagram</th>
                    <th class="px-6 pb-4 text-right">Ações Rápidas</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($posts): foreach ($posts as $post): 
                    $edit_link = admin_url('admin.php?page=' . $current_page . '&action=edit&id=' . $post->ID);
                    $view_link = get_permalink($post->ID);
                    $role = get_post_meta($post->ID, '_academy_member_role', true);
                    $tier = get_post_meta($post->ID, '_academy_member_tier', true);
                    $insta = get_post_meta($post->ID, '_academy_member_instagram', true);
                    
                    $tier_labels = array(
                        'lideranca' => 'Direção e Liderança',
                        'grand_master' => 'Grand Master',
                        'convidado' => 'Convidado'
                    );
                    $tier_label = isset($tier_labels[$tier]) ? $tier_labels[$tier] : 'Não definido';
                ?>
                <tr class="glass hover:bg-white/5 transition-all group">
                    <td class="px-6 py-5 rounded-l-2xl border-l border-t border-b border-white/5">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-onyx border border-white/10 rounded-full overflow-hidden flex items-center justify-center text-gold-500">
                                <?php if (has_post_thumbnail($post->ID)): ?>
                                    <?php echo get_the_post_thumbnail($post->ID, array(48, 48), array('class' => 'object-cover w-full h-full')); ?>
                                <?php else: ?>
                                    <span class="dashicons dashicons-admin-users"></span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-white group-hover:text-gold-400 transition-colors"><?php echo esc_html($post->post_title); ?></h4>
                                <p class="text-[10px] text-gray-500 mt-0.5"><?php echo esc_html($role); ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-5 border-t border-b border-white/5 text-center">
                        <span class="px-3 py-1 bg-white/5 border border-white/10 rounded-full text-[9px] font-bold uppercase tracking-widest text-zinc-400">
                            <?php echo esc_html($tier_label); ?>
                        </span>
                    </td>
                    <td class="px-6 py-5 border-t border-b border-white/5 text-center">
                        <?php if ($insta): ?>
                            <span class="text-xs text-gold-500 font-mono">@<?php echo esc_html($insta); ?></span>
                        <?php else: ?>
                            <span class="text-[9px] text-gray-700 italic">não informado</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-5 rounded-r-2xl border-r border-t border-b border-white/5 text-right">
                        <div class="flex items-center justify-end gap-3">
                            <a href="<?php echo home_url('/equipe-academia'); ?>" target="_blank" class="p-2 bg-white/5 hover:bg-gold-500/10 text-gray-400 hover:text-gold-500 rounded-lg transition-all" title="Ver no Site">
                                <span class="dashicons dashicons-visibility"></span>
                            </a>
                            <a href="<?php echo $edit_link; ?>" class="p-2 bg-white/5 hover:bg-gold-500/10 text-gray-400 hover:text-gold-500 rounded-lg transition-all" title="Editar">
                                <span class="dashicons dashicons-edit"></span>
                            </a>
                            <a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=lms_delete_academy_member&post_id=' . $post->ID ), 'lms_delete_content_nonce' ); ?>" 
                               onclick="return confirm('Deseja realmente remover este profissional da equipe?');"
                               class="p-2 bg-red-500/5 hover:bg-red-500/20 text-red-500/40 hover:text-red-500 rounded-lg transition-all" title="Excluir Permanentemente">
                                <span class="dashicons dashicons-trash"></span>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr>
                    <td colspan="4" class="px-6 py-20 text-center glass rounded-2xl border border-white/5 text-gray-500 italic">
                        Nenhum membro da equipe cadastrado. Comece agora!
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
</style>
