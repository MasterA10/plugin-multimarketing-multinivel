<?php
/**
 * Admin Template: Edit Link Bio Page - PREMIUM REDESIGN
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$post_id = isset( $_GET['post_id'] ) ? intval( $_GET['post_id'] ) : 0;
$post_title = '';
$bio_title = '';
$bio_subtitle = '';
$bio_photo = 0;
$show_crown = false;
$links = array();

if ( $post_id > 0 ) {
    $post = get_post( $post_id );
    $post_title = $post->post_title;
    $bio_title = get_post_meta($post_id, '_lms_bio_title', true);
    $bio_subtitle = get_post_meta($post_id, '_lms_bio_subtitle', true);
    $bio_photo = get_post_meta($post_id, '_lms_bio_photo', true);
    $show_crown = get_post_meta($post_id, '_lms_bio_show_crown', true);
    $links = get_post_meta($post_id, '_lms_bio_links', true) ?: array();
}
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Playfair+Display:ital,wght@1,400;1,700&display=swap');

    .elite-admin-wrap {
        font-family: 'Outfit', sans-serif;
        background: #0a0a0a;
        color: #fff;
        min-height: calc(100vh - 32px);
        margin: -20px -20px 0 -20px;
        padding: 40px;
        background-image: 
            radial-gradient(circle at 0% 0%, rgba(212, 175, 55, 0.05) 0%, transparent 50%);
    }

    .elite-editor-grid {
        display: grid;
        grid-template-columns: 350px 1fr;
        gap: 40px;
        max-width: 1200px;
        margin: 0 auto;
    }

    .elite-panel {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(212, 175, 55, 0.1);
        border-radius: 28px;
        padding: 30px;
        backdrop-filter: blur(10px);
    }

    .elite-panel-title {
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 10px;
        color: #D4AF37;
    }

    .elite-field {
        margin-bottom: 25px;
    }

    .elite-label {
        display: block;
        font-size: 0.85rem;
        font-weight: 600;
        color: rgba(255,255,255,0.4);
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .elite-input, .elite-textarea, .elite-select {
        width: 100%;
        background: rgba(0,0,0,0.3);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 12px;
        padding: 12px 18px;
        color: #fff;
        font-family: 'Outfit', sans-serif;
        transition: all 0.3s;
    }

    .elite-input:focus, .elite-textarea:focus {
        border-color: #D4AF37;
        outline: none;
        box-shadow: 0 0 15px rgba(212, 175, 55, 0.1);
    }

    .elite-warning-box {
        background: rgba(190, 18, 60, 0.1);
        border-left: 3px solid #e11d48;
        padding: 15px;
        border-radius: 0 12px 12px 0;
        margin-top: 10px;
    }

    .elite-warning-box p {
        margin: 0;
        font-size: 0.8rem;
        color: #fb7185;
        line-height: 1.4;
    }

    .profile-uploader {
        text-align: center;
        margin-bottom: 30px;
    }

    .photo-preview {
        width: 140px;
        height: 140px;
        border-radius: 50%;
        margin: 0 auto 20px;
        border: 3px solid #D4AF37;
        padding: 5px;
        position: relative;
        cursor: pointer;
        transition: transform 0.3s;
    }

    .photo-preview:hover { transform: scale(1.05); }

    .photo-preview img {
        width: 100%; height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }

    /* Repeater */
    .link-row {
        background: rgba(255,255,255,0.02);
        border-radius: 20px;
        padding: 20px;
        margin-bottom: 15px;
        border: 1px solid rgba(255,255,255,0.03);
        position: relative;
    }

    .link-row:hover {
        background: rgba(255,255,255,0.04);
        border-color: rgba(212, 175, 55, 0.2);
    }

    .btn-remove {
        position: absolute;
        top: 15px;
        right: 15px;
        background: rgba(244, 63, 94, 0.1);
        color: #fb7185;
        border: none;
        width: 24px;
        height: 24px;
        border-radius: 6px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
    }

    .btn-remove:hover { background: #e11d48; color: #fff; }

    .btn-elite-add {
        width: 100%;
        background: transparent;
        border: 2px dashed rgba(212, 175, 55, 0.2);
        color: #D4AF37;
        padding: 15px;
        border-radius: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .btn-elite-add:hover {
        background: rgba(212, 175, 55, 0.05);
        border-color: #D4AF37;
    }

    .elite-footer-actions {
        display: flex;
        justify-content: flex-end;
        gap: 20px;
        margin-top: 40px;
    }

    .btn-save {
        background: linear-gradient(135deg, #D4AF37 0%, #F2D480 50%, #D4AF37 100%);
        color: #000;
        padding: 15px 40px;
        border-radius: 18px;
        border: none;
        font-weight: 700;
        font-size: 1.1rem;
        cursor: pointer;
        box-shadow: 0 10px 30px rgba(212, 175, 55, 0.2);
        transition: transform 0.3s;
    }

    .btn-save:hover { transform: translateY(-3px); }

    .btn-cancel {
        color: rgba(255,255,255,0.4);
        text-decoration: none;
        align-self: center;
        font-weight: 600;
    }

    .btn-cancel:hover { color: #fff; }

    /* Switch Style */
    .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 26px;
    }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #333;
        transition: .4s;
        border-radius: 34px;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 18px; width: 18px;
        left: 4px; bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    input:checked + .slider { background-color: #D4AF37; }
    input:checked + .slider:before { transform: translateX(24px); }

</style>

<div class="elite-admin-wrap">
    <div style="margin-bottom: 40px;">
        <h1 style="font-size: 2.2rem; font-weight: 700; color: #fff; margin: 0;"><?php echo $post_id ? 'Refinar Bio Hub' : 'Nova Bio de Elite'; ?></h1>
        <p style="color: rgba(255,255,255,0.4); margin-top: 5px;">Ajuste o design e os destinos da sua página de links.</p>
    </div>

    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
        <?php wp_nonce_field( 'lms_save_elite_content_nonce', 'lms_nonce' ); ?>
        <input type="hidden" name="action" value="lms_save_bio">
        <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">

        <div class="elite-editor-grid">
            <!-- Left Panel: Profile Configuration -->
            <div class="elite-panel">
                <div class="elite-panel-title">
                    <span class="dashicons dashicons-admin-users"></span>Perfil
                </div>

                <div class="profile-uploader">
                    <div id="select-bio-photo" class="photo-preview">
                        <div id="bio-photo-preview">
                            <?php if ($bio_photo) : ?>
                                <img src="<?php echo wp_get_attachment_image_url($bio_photo, 'medium'); ?>">
                            <?php else : ?>
                                <div style="display: flex; align-items:center; justify-content:center; height: 100%; color: rgba(255,255,255,0.2);">
                                    <span class="dashicons dashicons-camera" style="font-size: 40px; width:40px; height:40px;"></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div style="position: absolute; bottom: 0; right: 0; background: #D4AF37; color: #000; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                            <span class="dashicons dashicons-edit" style="font-size: 16px;"></span>
                        </div>
                    </div>
                    <input type="hidden" name="bio_photo" id="bio_photo_id" value="<?php echo $bio_photo; ?>">
                </div>

                <div class="elite-field">
                    <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(255,255,255,0.02); padding: 15px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                        <span class="elite-label" style="margin: 0;">Usar Coroa Majestosa</span>
                        <label class="switch">
                            <input type="checkbox" name="show_crown" value="1" <?php checked($show_crown, true); ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>

                <div class="elite-field">
                    <label class="elite-label">Identificador (Slug)</label>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="color: rgba(255,255,255,0.2); font-family: monospace;">/links/</span>
                        <input type="text" name="post_title" value="<?php echo esc_attr($post_title); ?>" required
                               class="elite-input" placeholder="ex: seu-nome">
                    </div>
                    <div class="elite-warning-box">
                        <p><strong>Cuidado:</strong> O identificador deve ser único. Nomes duplicados causarão conflitos de redirecionamento ou abrirão a página errada.</p>
                    </div>
                </div>
            </div>

            <!-- Right Panel: Bio Content & Links -->
            <div class="elite-panel">
                <div class="elite-panel-title">
                    <span class="dashicons dashicons-welcome-edit-page"></span>Conteúdo & Botões
                </div>

                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px; margin-bottom: 40px;">
                    <div>
                        <label class="elite-label">Nome de Exibição</label>
                        <input type="text" name="bio_title" value="<?php echo esc_attr($bio_title); ?>" class="elite-input" placeholder="Seu Nome">
                    </div>
                    <div>
                        <label class="elite-label">Subtítulo / Aroma</label>
                        <input type="text" name="bio_subtitle" value="<?php echo esc_attr($bio_subtitle); ?>" class="elite-input" placeholder="O que você faz de melhor?">
                    </div>
                </div>

                <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                    <span class="elite-label">Links Ativos</span>
                    <button type="button" id="add-link-row" class="btn-elite-add" style="width: auto; padding: 8px 20px;">
                        <span class="dashicons dashicons-plus"></span> Adicionar Link
                    </button>
                </div>

                <div id="links-container">
                    <?php if (empty($links)) : ?>
                        <div id="links-empty-state" style="text-align: center; padding: 40px; border: 2px dashed rgba(255,255,255,0.05); border-radius: 20px; color: rgba(255,255,255,0.2);">
                            Nenhum link configurado. Clique em Adicionar acima.
                        </div>
                    <?php else : ?>
                        <?php foreach ($links as $index => $link) : 
                            $current_icon = isset($link['icon']) ? $link['icon'] : 'link';
                        ?>
                            <div class="link-row">
                                <button type="button" class="btn-remove remove-link-row"><span class="dashicons dashicons-no-alt"></span></button>
                                <div style="display: grid; grid-template-columns: 150px 1fr 1fr; gap: 15px;">
                                    <div>
                                        <label class="elite-label">Ícone</label>
                                        <select name="links[<?php echo $index; ?>][icon]" class="elite-select">
                                            <option value="link" <?php selected($current_icon, 'link'); ?>>Link Geral</option>
                                            <option value="instagram" <?php selected($current_icon, 'instagram'); ?>>Instagram</option>
                                            <option value="whatsapp" <?php selected($current_icon, 'whatsapp'); ?>>WhatsApp</option>
                                            <option value="facebook" <?php selected($current_icon, 'facebook'); ?>>Facebook</option>
                                            <option value="shopping" <?php selected($current_icon, 'shopping'); ?>>Compras</option>
                                            <option value="lms" <?php selected($current_icon, 'lms'); ?>>Membros</option>
                                            <option value="website" <?php selected($current_icon, 'website'); ?>>Site</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="elite-label">Texto</label>
                                        <input type="text" name="links[<?php echo $index; ?>][label]" value="<?php echo esc_attr($link['label']); ?>" class="elite-input" placeholder="Título do Botão">
                                    </div>
                                    <div>
                                        <label class="elite-label">URL</label>
                                        <input type="url" name="links[<?php echo $index; ?>][url]" value="<?php echo esc_attr($link['url']); ?>" class="elite-input" placeholder="https://...">
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="elite-footer-actions">
                    <a href="<?php echo admin_url('admin.php?page=elite-links'); ?>" class="btn-cancel">Cancelar Alterações</a>
                    <button type="submit" class="btn-save shadow-gold-500/20">
                        Publicar Bio de Elite <span class="dashicons dashicons-cloud-upload" style="margin-left: 10px;"></span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Media Library Integration
    $('#select-bio-photo').click(function(e) {
        e.preventDefault();
        var frame = wp.media({ title: 'Design de Bio: Perfil', button: { text: 'Aplicar Foto' }, multiple: false });
        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            $('#bio_photo_id').val(attachment.id);
            $('#bio-photo-preview').html('<img src="'+attachment.url+'">');
        });
        frame.open();
    });

    // Repeater Functionality
    let nextIndex = <?php echo count($links); ?>;
    $('#add-link-row').click(function() {
        $('#links-empty-state').hide();
        const html = `
            <div class="link-row" style="opacity: 0; transform: translateY(10px); transition: all 0.3s;">
                <button type="button" class="btn-remove remove-link-row"><span class="dashicons dashicons-no-alt"></span></button>
                <div style="display: grid; grid-template-columns: 150px 1fr 1fr; gap: 15px;">
                    <div>
                        <label class="elite-label">Ícone</label>
                        <select name="links[${nextIndex}][icon]" class="elite-select">
                            <option value="link">Link Geral</option>
                            <option value="instagram">Instagram</option>
                            <option value="whatsapp">WhatsApp</option>
                            <option value="facebook">Facebook</option>
                            <option value="shopping">Compras</option>
                            <option value="lms">Membros</option>
                            <option value="website">Site</option>
                        </select>
                    </div>
                    <div>
                        <label class="elite-label">Texto</label>
                        <input type="text" name="links[${nextIndex}][label]" class="elite-input" placeholder="Novo Link">
                    </div>
                    <div>
                        <label class="elite-label">URL</label>
                        <input type="url" name="links[${nextIndex}][url]" class="elite-input" placeholder="https://...">
                    </div>
                </div>
            </div>
        `;
        const $row = $(html);
        $('#links-container').append($row);
        setTimeout(() => $row.css({ opacity: 1, transform: 'translateY(0)' }), 10);
        nextIndex++;
    });

    $(document).on('click', '.remove-link-row', function() {
        $(this).closest('.link-row').fadeOut(300, function() { 
            $(this).remove(); 
            if ($('#links-container .link-row').length === 0) $('#links-empty-state').show();
        });
    });
});
</script>
