<?php
/**
 * Admin Template: Manage Link Hub (Listing) - PREMIUM REDESIGN
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$links_query = new WP_Query( array(
    'post_type'      => 'elite_links',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
) );

$status = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap');

    .elite-admin-wrap {
        font-family: 'Outfit', sans-serif;
        background: #0a0a0a;
        color: #fff;
        min-height: calc(100vh - 32px);
        margin: -20px -20px 0 -20px;
        padding: 40px;
        background-image: 
            radial-gradient(circle at 0% 0%, rgba(212, 175, 55, 0.05) 0%, transparent 50%),
            radial-gradient(circle at 100% 100%, rgba(212, 175, 55, 0.05) 0%, transparent 50%);
    }

    .elite-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 50px;
    }

    .elite-title h1 {
        font-size: 2.5rem;
        font-weight: 700;
        color: #fff;
        margin: 0;
        letter-spacing: -1px;
    }

    .elite-title p {
        color: rgba(255,255,255,0.5);
        margin: 5px 0 0 0;
    }

    .btn-elite-primary {
        background: linear-gradient(135deg, #D4AF37 0%, #F2D480 50%, #D4AF37 100%);
        color: #000;
        padding: 12px 28px;
        border-radius: 14px;
        text-decoration: none;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 10px 20px rgba(212, 175, 55, 0.2);
    }

    .btn-elite-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 30px rgba(212, 175, 55, 0.3);
        color: #000;
    }

    .elite-card {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(212, 175, 55, 0.1);
        border-radius: 24px;
        padding: 30px;
        transition: all 0.4s ease;
        position: relative;
        overflow: hidden;
        backdrop-filter: blur(10px);
    }

    .elite-card:hover {
        border-color: rgba(212, 175, 55, 0.4);
        background: rgba(255, 255, 255, 0.05);
        transform: translateY(-5px);
    }

    .elite-card-photo {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        border: 2px solid rgba(212, 175, 55, 0.3);
        padding: 3px;
        margin-bottom: 20px;
    }

    .elite-card-photo img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }

    .elite-card-title {
        font-size: 1.4rem;
        font-weight: 700;
        margin-bottom: 10px;
        color: #fff;
    }

    .elite-card-stats {
        display: flex;
        gap: 15px;
        color: rgba(255,255,255,0.4);
        font-size: 0.85rem;
        margin-bottom: 25px;
    }

    .elite-url-box {
        background: rgba(0,0,0,0.3);
        border-radius: 12px;
        padding: 10px 15px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        border: 1px solid rgba(255,255,255,0.05);
    }

    .elite-url-box code {
        font-family: monospace;
        color: #D4AF37;
        font-size: 0.8rem;
    }

    .btn-copy {
        background: transparent;
        border: none;
        color: rgba(255,255,255,0.3);
        cursor: pointer;
        transition: color 0.3s;
    }

    .btn-copy:hover { color: #fff; }

    .elite-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .btn-elite-secondary {
        background: rgba(255,255,255,0.05);
        color: #fff;
        padding: 10px;
        text-align: center;
        border-radius: 12px;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 600;
        transition: all 0.3s;
        border: 1px solid rgba(255,255,255,0.05);
    }

    .btn-elite-secondary:hover {
        background: #fff;
        color: #000;
    }

    .btn-elite-outline {
        border: 1px solid rgba(255,255,255,0.1);
        color: rgba(255,255,255,0.6);
        padding: 10px;
        border-radius: 12px;
        text-decoration: none;
        text-align: center;
        font-size: 0.9rem;
        transition: all 0.3s;
    }

    .btn-elite-outline:hover {
        border-color: #rose-500;
        color: #f43f5e;
        background: rgba(244, 63, 94, 0.05);
    }

    .elite-status {
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 100;
    }

    .elite-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 30px;
    }

    /* Scrollbar */
    ::-webkit-scrollbar { width: 8px; }
    ::-webkit-scrollbar-track { background: #0a0a0a; }
    ::-webkit-scrollbar-thumb { background: #222; border-radius: 10px; }
    ::-webkit-scrollbar-thumb:hover { background: #D4AF37; }
</style>

<div class="elite-admin-wrap">
    <div class="elite-header">
        <div class="elite-title">
            <h1>Elite Link Hub</h1>
            <p>Sua central de bios de luxo e presença digital poderosa.</p>
        </div>
        
        <a href="<?php echo admin_url('admin.php?page=elite-links&action=new'); ?>" class="btn-elite-primary">
            <span class="dashicons dashicons-plus-alt"></span>
            Criar Nova Bio de Elite
        </a>
    </div>

    <?php if ( $status === 'saved' || $status === 'deleted' ) : ?>
        <div class="elite-status">
            <div style="background: #111; border: 1px solid #D4AF37; padding: 15px 25px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
                <span class="dashicons dashicons-yes-alt" style="color: #D4AF37; margin-right: 10px;"></span>
                <?php echo $status === 'saved' ? 'Sua Bio foi salva com sucesso!' : 'Página removida permanentemente.'; ?>
            </div>
        </div>
        <script>
            setTimeout(() => { jQuery('.elite-status').fadeOut(); }, 4000);
            
            function copyEliteUrl(btn, text) {
                var $btn = jQuery(btn);
                var originalHtml = $btn.html();
                
                // Try modern API
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(text).then(function() {
                        showSuccess($btn, originalHtml);
                    });
                } else {
                    // Fallback for non-secure contexts (Local Sites)
                    var textArea = document.createElement("textarea");
                    textArea.value = text;
                    textArea.style.position = "fixed";
                    textArea.style.left = "-999999px";
                    textArea.style.top = "-999999px";
                    document.body.appendChild(textArea);
                    textArea.focus();
                    textArea.select();
                    try {
                        document.execCommand('copy');
                        showSuccess($btn, originalHtml);
                    } catch (err) {
                        console.error('Falha ao copiar', err);
                    }
                    document.body.removeChild(textArea);
                }
            }

            function showSuccess($btn, originalHtml) {
                $btn.html('<span class="dashicons dashicons-yes" style="color: #D4AF37;"></span>');
                setTimeout(function() {
                    $btn.html(originalHtml);
                }, 2000);
            }
        </script>
<?php endif; ?>

    <div class="elite-grid">
        <?php if ( $links_query->have_posts() ) : ?>
            <?php while ( $links_query->have_posts() ) : $links_query->the_post(); 
                $slug = get_post_field('post_name', get_the_ID());
                $full_url = home_url('/links/' . $slug);
                $photo_id = get_post_meta(get_the_ID(), '_lms_bio_photo', true);
                $photo_url = $photo_id ? wp_get_attachment_image_url($photo_id, 'thumbnail') : 'https://ui-avatars.com/api/?name=' . urlencode(get_the_title()) . '&background=111&color=D4AF37';
                $links_count = count((array)get_post_meta(get_the_ID(), '_lms_bio_links', true));
            ?>
                <div class="elite-card">
                    <div class="elite-card-photo">
                        <img src="<?php echo esc_url($photo_url); ?>" alt="">
                    </div>

                    <h3 class="elite-card-title"><?php the_title(); ?></h3>
                    
                    <div class="elite-card-stats">
                        <span><span class="dashicons dashicons-admin-links" style="font-size: 16px; margin-right: 5px;"></span> <?php echo $links_count; ?> links</span>
                        <span><span class="dashicons dashicons-visibility" style="font-size: 16px; margin-right: 5px;"></span> /links/<?php echo $slug; ?></span>
                    </div>

                    <div class="elite-url-box">
                        <code><?php echo esc_url($full_url); ?></code>
                        <button class="btn-copy" onclick="copyEliteUrl(this, '<?php echo esc_url($full_url); ?>')" title="Copiar URL">
                            <span class="dashicons dashicons-admin-page"></span>
                        </button>
                    </div>

                    <div class="elite-actions">
                        <a href="<?php echo admin_url('admin.php?page=elite-links&action=edit&post_id=' . get_the_ID()); ?>" class="btn-elite-secondary">
                            Editar Design
                        </a>
                        <a href="<?php echo wp_nonce_url( admin_url('admin-post.php?action=lms_delete_bio&post_id=' . get_the_ID()), 'lms_delete_content_nonce' ); ?>" 
                           onclick="return confirm('Excluir esta página permanentemente?');" class="btn-elite-outline">
                            Excluir
                        </a>
                    </div>
                </div>
            <?php endwhile; wp_reset_postdata(); ?>
        <?php else : ?>
            <div class="elite-card" style="grid-column: 1/-1; text-align: center; padding: 100px;">
                <span class="dashicons dashicons-share-alt2" style="font-size: 80px; width: 80px; height: 80px; color: rgba(212,175,55,0.1); margin-bottom: 20px;"></span>
                <h3 style="font-size: 1.5rem; color: #fff; margin-bottom: 10px;">Nenhuma Bio criada ainda</h3>
                <p style="color: rgba(255,255,255,0.4);">Comece agora e crie uma experiência de luxo para o seu Instagram.</p>
                <div style="margin-top: 30px;">
                    <a href="<?php echo admin_url('admin.php?page=elite-links&action=new'); ?>" class="btn-elite-primary">Criar Primeira Bio</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
