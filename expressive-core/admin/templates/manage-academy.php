<?php
/**
 * Template Name: Academy Member Manager
 * 
 * Custom management interface for Academy Members with Drag & Drop sorting.
 */
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Acesso negado.' );
}

$current_page = 'elite-academy';
$post_type = 'academy_member';

// Query posts ordered by menu_order
$all_posts = get_posts(array(
    'post_type'      => $post_type,
    'posts_per_page' => -1,
    'post_status'    => 'any',
    'orderby'        => 'menu_order',
    'order'          => 'ASC'
));

// Group by tier
$grouped_members = array(
    'lideranca'   => array(),
    'grandmaster' => array(),
    'convidado'   => array()
);

foreach ( $all_posts as $post ) {
    $tier = get_post_meta( $post->ID, '_academy_member_tier', true ) ?: 'convidado';
    if ( $tier === 'grand_master' ) {
        $tier = 'grandmaster';
    }

    if ( isset( $grouped_members[$tier] ) ) {
        $grouped_members[$tier][] = $post;
    } else {
        $grouped_members['convidado'][] = $post;
    }
}

$tier_labels = array(
    'lideranca'   => 'Direção e Liderança',
    'grandmaster' => 'Educadores Grand Master Diamantes',
    'convidado'   => 'Educadores Convidados'
);
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
        <div class="flex items-center gap-4">
            <div id="save-status" class="hidden text-[10px] font-bold uppercase tracking-widest text-gold-500 bg-gold-500/10 px-4 py-2 rounded-lg border border-gold-500/20 animate-pulse">
                Salvando ordem...
            </div>
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

    <div class="space-y-12">
        <?php foreach ( $grouped_members as $tier_key => $members ) : ?>
            <section class="academy-category">
                <div class="flex items-center gap-3 mb-6">
                    <div class="h-px flex-1 bg-white/5"></div>
                    <h2 class="text-[11px] font-black uppercase tracking-[0.3em] text-gold-500/60"><?php echo esc_html( $tier_labels[$tier_key] ); ?></h2>
                    <div class="h-px flex-1 bg-white/5"></div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="text-[10px] text-zinc-500 font-bold uppercase tracking-widest">
                                <th class="px-6 pb-2 w-10 text-center">Ordem</th>
                                <th class="px-6 pb-2">Profissional</th>
                                <th class="px-6 pb-2 text-center">Instagram</th>
                                <th class="px-6 pb-2 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="academy-sortable-list" data-tier="<?php echo esc_attr( $tier_key ); ?>">
                            <?php if ( ! empty( $members ) ) : foreach ( $members as $post ) : 
                                $edit_link = admin_url('admin.php?page=' . $current_page . '&action=edit&id=' . $post->ID);
                                $role = get_post_meta($post->ID, '_academy_member_role', true);
                                $insta = get_post_meta($post->ID, '_academy_member_instagram', true);
                            ?>
                            <tr class="glass hover:bg-white/5 transition-all group cursor-move" data-id="<?php echo $post->ID; ?>">
                                <td class="px-6 py-4 rounded-l-2xl border-l border-t border-b border-white/5 text-center">
                                    <span class="dashicons dashicons-move text-zinc-700 group-hover:text-gold-500/40 transition-colors"></span>
                                </td>
                                <td class="px-6 py-4 border-t border-b border-white/5">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 bg-black border border-white/10 rounded-full overflow-hidden flex-shrink-0">
                                            <?php if (has_post_thumbnail($post->ID)): ?>
                                                <?php echo get_the_post_thumbnail($post->ID, array(40, 40), array('class' => 'object-cover w-full h-full')); ?>
                                            <?php else: ?>
                                                <div class="w-full h-full flex items-center justify-center text-zinc-800">
                                                    <span class="dashicons dashicons-admin-users"></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-semibold text-white group-hover:text-gold-400 transition-colors"><?php echo esc_html($post->post_title); ?></h4>
                                            <p class="text-[10px] text-zinc-500 mt-0.5"><?php echo esc_html($role); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 border-t border-b border-white/5 text-center">
                                    <?php if ($insta): ?>
                                        <span class="text-[10px] text-gold-500/80 font-mono">@<?php echo esc_html($insta); ?></span>
                                    <?php else: ?>
                                        <span class="text-[9px] text-zinc-800 italic">não informado</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 rounded-r-2xl border-r border-t border-b border-white/5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="<?php echo $edit_link; ?>" class="w-8 h-8 flex items-center justify-center bg-white/5 hover:bg-gold-500/10 text-zinc-500 hover:text-gold-500 rounded-lg transition-all">
                                            <span class="dashicons dashicons-edit" style="font-size: 16px;"></span>
                                        </a>
                                        <a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=lms_delete_academy_member&post_id=' . $post->ID ), 'lms_delete_content_nonce' ); ?>" 
                                           onclick="return confirm('Deseja realmente remover este profissional da equipe?');"
                                           class="w-8 h-8 flex items-center justify-center bg-red-500/5 hover:bg-red-500/20 text-red-500/30 hover:text-red-500 rounded-lg transition-all">
                                            <span class="dashicons dashicons-trash" style="font-size: 16px;"></span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; else : ?>
                            <tr class="no-drag">
                                <td colspan="4" class="px-6 py-12 text-center glass rounded-2xl border border-white/5 text-zinc-600 text-[10px] uppercase tracking-widest italic">
                                    Nenhum membro nesta categoria.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endforeach; ?>
    </div>

</div>

<script>
jQuery(document).ready(function($) {
    $(".academy-sortable-list").sortable({
        items: "tr:not(.no-drag)",
        cursor: "move",
        opacity: 0.8,
        placeholder: "bg-white/5 h-20 rounded-2xl",
        update: function(event, ui) {
            saveAcademyOrder();
        }
    });

    function saveAcademyOrder() {
        let order = [];
        // We gather order from all lists to maintain a global menu_order sequence if needed, 
        // but sorting is usually handled within the list.
        // Let's gather ALL IDs in the current visual order across ALL categories.
        $(".academy-sortable-list tr[data-id]").each(function() {
            order.push($(this).data("id"));
        });

        $("#save-status").removeClass("hidden");

        $.post(ajaxurl, {
            action: 'lms_update_academy_order',
            nonce: '<?php echo wp_create_nonce("lms_save_elite_content_nonce"); ?>',
            order: order
        }, function(response) {
            $("#save-status").addClass("hidden");
            if (!response.success) {
                alert("Erro ao salvar ordem: " + response.data);
            }
        });
    }
});
</script>

<style>
    #wpcontent { background: #000 !important; padding-left: 0 !important; }
    #wpbody-content { padding-bottom: 0 !important; }
    .elite-admin-wrap { font-family: 'Outfit', sans-serif !important; }
    .elite-admin-wrap h1, .elite-admin-wrap h4 { font-family: 'Playfair Display', serif !important; }
    .glass { background: rgba(255, 255, 255, 0.02); }
    .ui-sortable-helper { display: table !important; background: rgba(212, 175, 55, 0.1) !important; border: 1px solid rgba(212, 175, 55, 0.3) !important; border-radius: 16px !important; }
</style>
