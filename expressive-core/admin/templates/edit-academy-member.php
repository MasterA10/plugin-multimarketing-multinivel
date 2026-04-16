<?php
/**
 * Template Name: Academy Editor (Zero Gutenberg)
 * 
 * Custom management interface for creating and editing Academy Members.
 */
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Acesso negado.' );
}

$action = isset( $_GET['action'] ) ? $_GET['action'] : 'new';
$post_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
$current_page = 'elite-academy';

// Load Data
$post_obj = ($post_id > 0) ? get_post($post_id) : null;
$title = $post_obj ? $post_obj->post_title : '';
$content = $post_obj ? $post_obj->post_content : '';
$role = $post_obj ? get_post_meta($post_id, '_academy_member_role', true) : '';
$background = $post_obj ? get_post_meta($post_id, '_academy_member_background', true) : '';
$tier = $post_obj ? get_post_meta($post_id, '_academy_member_tier', true) : 'grand_master';
$instagram = $post_obj ? get_post_meta($post_id, '_academy_member_instagram', true) : '';

$editor_title = ($post_id > 0) ? 'Editar Profissional' : 'Novo Membro da Equipe';
?>

<div class="elite-admin-wrap bg-[#111] text-white min-h-screen p-8 rounded-xl shadow-2xl mr-4 mt-4 font-sans max-w-5xl animate-fade-in">
    
    <!-- Editor Header -->
    <header class="flex justify-between items-center mb-10 border-b border-white/5 pb-8">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-gold-500/10 rounded-xl flex items-center justify-center text-gold-500 border border-gold-500/20">
                <span class="dashicons dashicons-admin-users" style="font-size: 24px; width: 24px; height: 24px;"></span>
            </div>
            <div>
                <h1 class="font-serif italic text-3xl mb-1 leading-tight" style="color: #D4AF37 !important;"><?php echo $editor_title; ?></h1>
                <p class="text-[10px] uppercase tracking-[0.25em] font-medium" style="color: rgba(242, 212, 128, 0.6) !important;">Interface de Gestão PMU Build (Zero Gutenberg)</p>
            </div>
        </div>
        <div class="flex gap-4">
            <a href="<?php echo admin_url('admin.php?page=' . $current_page); ?>" class="px-6 py-3 border border-white/10 rounded-xl text-xs font-bold uppercase tracking-widest text-gray-400 hover:text-white transition-all" style="text-decoration: none;">
                Cancelar
            </a>
            <button type="button" onclick="document.getElementById('academy-editor-form').submit();" class="bg-gradient-to-r from-gold-600 to-gold-400 hover:from-gold-500 hover:to-gold-300 text-black px-8 py-3 rounded-xl text-xs font-bold uppercase tracking-widest transition-all shadow-lg hover:shadow-gold-500/20 active:scale-95" style="background: linear-gradient(to right, #D4AF37, #F2D480) !important; color: #000 !important; font-weight: 800 !important; cursor: pointer;">
                <?php echo ($post_id > 0) ? 'Salvar Alterações' : 'Cadastrar Profissional'; ?>
            </button>
        </div>
    </header>

    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" id="academy-editor-form">
        <input type="hidden" name="action" value="lms_save_academy_member">
        <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
        <?php wp_nonce_field('lms_save_elite_content_nonce', 'lms_nonce'); ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            
            <!-- Main Content Area -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Identification Section -->
                <div class="glass p-8 rounded-3xl border border-white/5 space-y-6">
                    <h3 class="text-sm font-bold uppercase tracking-widest flex items-center gap-2" style="color: #D4AF37 !important;">
                        <span class="w-1.5 h-4 bg-gold-500 rounded-full"></span>
                        Informações Básicas
                    </h3>
                    
                    <div class="space-y-2">
                        <label class="text-[10px] text-gray-500 uppercase tracking-widest">Nome Completo do Profissional</label>
                        <input type="text" name="post_title" value="<?php echo esc_attr($title); ?>" required class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-4 text-white focus:border-gold-500/50 transition-all outline-none font-serif text-lg italic" placeholder="Ex: Dra. Maria Silva">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] text-gray-500 uppercase tracking-widest">Cargo / Função Principal</label>
                            <input type="text" name="academy_member_role" value="<?php echo esc_attr($role); ?>" class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-4 text-white focus:border-gold-500/50 outline-none text-sm" placeholder="Ex: CEO & Fundadora">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] text-gray-500 uppercase tracking-widest">Instagram (@)</label>
                            <input type="text" name="academy_member_instagram" value="<?php echo esc_attr($instagram); ?>" class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-4 text-gold-400 focus:border-gold-500/50 outline-none text-sm font-mono" placeholder="Ex: maria.pmu">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] text-gray-500 uppercase tracking-widest">Formação / Mini Currículo</label>
                        <textarea name="academy_member_background" class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-4 text-white focus:border-gold-500/50 outline-none min-h-[120px] text-sm" placeholder="Liste as principais formações acadêmicas e especializações..."><?php echo esc_textarea($background); ?></textarea>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] text-gray-500 uppercase tracking-widest">Apresentação / Sobre</label>
                        <textarea name="post_content" class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-4 text-white focus:border-gold-500/50 outline-none min-h-[200px] leading-relaxed text-sm" placeholder="Uma breve descrição sobre a trajetória do profissional..."><?php echo esc_textarea($content); ?></textarea>
                    </div>
                </div>

            </div>

                <!-- Side Configuration Area -->
                <div class="space-y-8">
                    
                    <!-- Thumbnail Section -->
                    <div id="section-thumbnail" class="bg-white/5 p-8 rounded-3xl border border-white/10 space-y-6">
                        <h3 class="text-sm font-bold uppercase tracking-widest flex items-center gap-2" style="color: #D4AF37 !important;">
                            <span class="dashicons dashicons-format-image"></span>
                            Foto de Perfil
                        </h3>
                        <div id="academy-media-trigger" class="aspect-square w-full bg-black/40 rounded-full border border-white/10 border-dashed flex flex-col items-center justify-center group cursor-pointer hover:border-gold-500/50 transition-all overflow-hidden relative">
                                <?php if (has_post_thumbnail($post_id)): ?>
                                <?php echo get_the_post_thumbnail($post_id, 'medium', array('class' => 'w-full h-full object-cover', 'id' => 'academy-preview-img')); ?>
                                <div class="absolute inset-0 bg-black/60 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all">
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-white">Trocar Foto</span>
                                </div>
                                <?php else: ?>
                                <img id="academy-preview-img" src="" class="hidden w-full h-full object-cover">
                                <div id="academy-placeholder-icon" class="flex flex-col items-center">
                                    <span class="dashicons dashicons-camera text-gold-500/30 mb-2" style="font-size: 32px; width: 32px; height: 32px;"></span>
                                    <span class="text-[10px] text-gold-400 font-bold uppercase tracking-widest text-center px-4">Selecionar Foto Premium</span>
                                </div>
                                <?php endif; ?>
                                <input type="hidden" name="_thumbnail_id" id="_thumbnail_id_field" value="<?php echo get_post_thumbnail_id($post_id); ?>">
                        </div>
                        <p class="text-[9px] text-gray-600 text-center italic">Recomendado: Foto quadrada (1:1) de alta qualidade.</p>
                    </div>

                    <!-- Category -->
                    <div class="bg-white/5 p-8 rounded-3xl border border-white/10 group hover:border-gold-500/30 transition-all shadow-[0_0_15px_rgba(212,175,55,0.05)] hover:shadow-[0_0_20px_rgba(212,175,55,0.15)]">
                        <label class="text-[10px] text-gold-500 uppercase font-bold tracking-widest block mb-4 flex items-center gap-2">
                            <span class="dashicons dashicons-category"></span>
                            Nível de Hierarquia
                        </label>
                        <div class="space-y-3">
                            <label class="flex items-center gap-3 cursor-pointer group/opt">
                                <input type="radio" name="academy_member_tier" value="lideranca" <?php checked($tier, 'lideranca'); ?> class="accent-gold-500">
                                <span class="text-xs text-gray-400 group-hover/opt:text-white transition-colors">Direção e Liderança</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group/opt">
                                <input type="radio" name="academy_member_tier" value="grand_master" <?php checked($tier, 'grand_master'); ?> class="accent-gold-500">
                                <span class="text-xs text-gray-400 group-hover/opt:text-white transition-colors">Grand Master</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group/opt">
                                <input type="radio" name="academy_member_tier" value="convidado" <?php checked($tier, 'convidado'); ?> class="accent-gold-500">
                                <span class="text-xs text-gray-400 group-hover/opt:text-white transition-colors">Convidado</span>
                            </label>
                        </div>
                    </div>

                    <!-- Visibility -->
                    <div class="bg-white/5 p-8 rounded-3xl border border-white/10">
                        <label class="text-[10px] text-gray-500 uppercase tracking-widest block mb-3">Status de Exibição</label>
                        <select name="post_status" class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-xs text-white focus:border-gold-500/50 outline-none">
                            <option value="publish" <?php selected(get_post_status($post_id), 'publish'); ?>>Ativo (Visível no site)</option>
                            <option value="draft" <?php selected(get_post_status($post_id), 'draft'); ?>>Oculto (Rascunho)</option>
                        </select>
                    </div>

                </div>
        </div>
    </form>

</div>

<script>
    jQuery(document).ready(function($) {
        // Media Library Integration
        $('#academy-media-trigger').on('click', function(e) {
            e.preventDefault();
            var frame = wp.media({
                title: 'Selecionar Foto de Perfil Premium',
                button: { text: 'Usar esta Foto' },
                multiple: false
            });
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                var imgUrl = (attachment.sizes && attachment.sizes.medium) ? attachment.sizes.medium.url : attachment.url;
                
                $('#_thumbnail_id_field').val(attachment.id);
                const $preview = $('#academy-preview-img');
                $preview.attr('src', imgUrl).removeClass('hidden').show();
                $('#academy-placeholder-icon').hide();
                $('#academy-media-trigger').addClass('border-gold-500 bg-gold-500/10');
            });
            frame.open();
        });
    });
</script>

<style>
    #wpcontent { background: #000 !important; padding-left: 0 !important; }
    #wpbody-content { padding-bottom: 0 !important; }
    .elite-admin-wrap { font-family: 'Outfit', sans-serif !important; }
    .elite-admin-wrap h1, .elite-admin-wrap textarea, .elite-admin-wrap input[type="text"] { 
        font-family: 'Playfair Display', serif !important; 
    }
    .glass { background: rgba(255, 255, 255, 0.02); }
    .animate-fade-in { animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    .elite-admin-wrap input[type="text"], 
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
</style>
