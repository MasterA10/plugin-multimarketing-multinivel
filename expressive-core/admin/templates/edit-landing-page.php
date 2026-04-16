<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$post_id = isset( $_GET['post_id'] ) ? intval( $_GET['post_id'] ) : 0;
$post_title = '';
$buttons = array();
$media = array();

if ( $post_id > 0 ) {
    $post = get_post( $post_id );
    $post_title = $post->post_title;
    $buttons = get_post_meta( $post_id, '_elite_lp_buttons', true ) ?: array();
    $media = get_post_meta( $post_id, '_elite_lp_media', true ) ?: array();
}

$current_template = get_post_meta($post_id, '_elite_lp_template', true) ?: 'gran-master';

// Define sections based on template
if ($current_template === 'ccp-academy') {
    $sections = array(
        'hero' => array('label' => 'Hero (Comunidade)', 'has_button' => true, 'button_default' => 'Quero me tornar Mestre'),
        'intro' => array('label' => 'A Grande Diferença (Intro)', 'has_button' => false),
        'pillars' => array('label' => 'Como Funciona (5 Pilares)', 'has_button' => false),
        'comparison' => array('label' => 'Comparativo (Free vs Premium)', 'has_button' => false),
        'method' => array('label' => 'Método e Regras', 'has_button' => false),
        'differentials' => array('label' => 'Diferenciais e Certificação', 'has_button' => false),
        'audience' => array('label' => 'Para Quem é?', 'has_button' => false),
        'offer' => array('label' => 'Oferta e Assinatura', 'has_button' => true, 'button_default' => 'Quero ser Premium'),
        'cta' => array('label' => 'Chamada Final (CTA)', 'has_button' => true, 'button_default' => 'Mudar de vida agora')
    );
} elseif ($current_template === 'gala') {
    $sections = array(
        'hero' => array('label' => 'Hero (Início/Baile)', 'has_button' => true, 'button_default' => 'Garantir Passaporte'),
        'intro' => array('label' => 'Glamour & Networking', 'has_button' => false),
        'awards_prestige' => array('label' => 'Reconhecimento & Prêmios', 'has_button' => false),
        'celebration' => array('label' => 'Luxo & Celebração', 'has_button' => false),
        'opportunities' => array('label' => 'Oportunidades Estratégicas', 'has_button' => true, 'button_default' => 'Ver Oportunidades'),
        'challenge' => array('label' => 'Desafio Gran Master', 'has_button' => false),
        'levels' => array('label' => 'Níveis (Diamante/Rubi)', 'has_button' => false),
        'prize_iphone' => array('label' => 'Prêmio 1: iPhone 15', 'has_button' => false),
        'prize_travel' => array('label' => 'Prêmio 2: Viagem', 'has_button' => false),
        'prize_bag' => array('label' => 'Prêmio 3: Bolsa Premium', 'has_button' => true, 'button_default' => 'Quero esses Prêmios'),
        'artistic' => array('label' => 'Categorias Artísticas', 'has_button' => false),
        'homenagens' => array('label' => 'Homenagens Especiais', 'has_button' => false),
        'speaker' => array('label' => 'Speaker (Rose Oliveira)', 'has_button' => false),
        'ceo' => array('label' => 'Time CEO & Mentoras', 'has_button' => false),
        'photo_juliana' => array('label' => 'Foto: Juliana Parra', 'has_button' => false),
        'photo_catia' => array('label' => 'Foto: Cátia Araújo', 'has_button' => false),
        'photo_cley' => array('label' => 'Foto: Cley Fernandes', 'has_button' => false),
        'photo_paty' => array('label' => 'Foto: Paty Batista', 'has_button' => false),
        'cta_qr' => array('label' => 'Passaporte Final (QR Code + Botão)', 'has_button' => true, 'button_default' => 'Garanta sua Vaga Agora')
    );
} else {
    $sections = array(
        'hero' => array('label' => 'Hero (Início)', 'has_button' => true, 'button_default' => 'Solicitar Acesso'),
        'validation' => array('label' => 'Validação / Dor', 'has_button' => true, 'button_default' => 'Saiba Mais'),
        'recognition' => array('label' => 'Reconhecimento Institucional', 'has_button' => true, 'button_default' => 'Garantir Patente'),
        'checklist' => array('label' => 'Checklist Elegibilidade', 'has_button' => true, 'button_default' => 'Quero me Candidatar'),
        'leadership' => array('label' => 'Liderança e Estrutura', 'has_button' => true, 'button_default' => 'Conhecer Mentores'),
        'phases' => array('label' => 'Jornada (Fases)', 'has_button' => true, 'button_default' => 'Começar Agora'),
        'reasons' => array('label' => 'Por que entrar?', 'has_button' => true, 'button_default' => 'Mudar de Vida'),
        'benefits' => array('label' => 'Benefícios Exclusivos', 'has_button' => true, 'button_default' => 'Acessar Vantagens'),
        'map' => array('label' => 'Mapa de Ganhos (3 Meses)', 'has_button' => true, 'button_default' => 'Ver Planejamento'),
        'authority' => array('label' => 'Autoridade / Jornada', 'has_button' => true, 'button_default' => 'Ver Detalhes'),
        'schedule' => array('label' => 'Cronograma Visual', 'has_button' => true, 'button_default' => 'Ver Agenda'),
        'prestige' => array('label' => 'Avaliação e Prestige', 'has_button' => true, 'button_default' => 'Ser Avaliada'),
        'pricing' => array('label' => 'Investimento', 'has_button' => true, 'button_default' => 'Garantir minha vaga'),
        'choices' => array('label' => 'Duas Escolhas', 'has_button' => true, 'button_default' => 'Escolher Maestria'),
        'conclusion' => array('label' => 'Conclusão / Chamada Final', 'has_button' => true, 'button_default' => 'Sim, Eu Quero'),
        'rules' => array('label' => 'Regras do Processo', 'has_button' => true, 'button_default' => 'Fazer Pré-Cadastro')
    );
}
?>

<div class="wrap elite-lms-admin">
    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" id="elite-lp-editor">
        <input type="hidden" name="action" value="lms_save_lp">
        <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
        <?php wp_nonce_field( 'lms_save_elite_content_nonce', 'lms_nonce' ); ?>

        <div class="flex flex-col gap-6 p-8 bg-[#0a0a0a] min-h-screen text-white font-['Outfit']">
            
            <!-- Header Toolbar -->
            <div class="flex justify-between items-center bg-white/5 p-6 rounded-3xl border border-white/10 backdrop-blur-xl sticky top-0 z-50">
                <div class="flex items-center gap-4">
                    <a href="<?php echo admin_url('admin.php?page=elite-pages'); ?>" class="p-2 hover:bg-white/10 rounded-full transition-all">
                        <span class="dashicons dashicons-arrow-left-alt2"></span>
                    </a>
                    <div class="flex items-center gap-6">
                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] uppercase font-bold text-white/40 mb-1">Título da Página</label>
                            <input type="text" name="post_title" value="<?php echo esc_attr($post_title); ?>" placeholder="ex: Gran Master 2026" class="bg-black/40 border border-[#c5a059]/30 rounded-lg text-xs px-4 py-2 text-white focus:outline-none focus:border-[#c5a059] w-64">
                        </div>

                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] uppercase font-bold text-white/40 mb-1">Link Permanente (Slug)</label>
                            <?php 
                            $slug = '';
                            if ($post_id > 0) {
                                $post_obj = get_post($post_id);
                                $slug = $post_obj->post_name;
                            }
                            ?>
                            <div class="flex items-center gap-2">
                                 <span class="text-[10px] text-white/20">elite/</span>
                                 <input type="text" name="post_name" value="<?php echo esc_attr($slug); ?>" placeholder="ex: baile-de-gala" class="bg-black/40 border border-[#c5a059]/30 rounded-lg text-xs px-4 py-2 text-white focus:outline-none focus:border-[#c5a059] w-48">
                            </div>
                        </div>

                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] uppercase font-bold text-white/40 mb-1">Modelo de Design</label>
                            <select name="elite_lp_template" onchange="this.form.submit()" class="bg-black/40 border border-[#c5a059]/30 rounded-lg text-xs px-4 py-2 text-white focus:outline-none hover:border-[#c5a059] cursor-pointer">
                                <option value="gran-master" <?php selected($current_template, 'gran-master'); ?>>Elite Gran Master (Vendas)</option>
                                <option value="gala" <?php selected($current_template, 'gala'); ?>>Encontro das Grans (Baile de Gala)</option>
                                <option value="ccp-academy" <?php selected($current_template, 'ccp-academy'); ?>>CCP Academy (Formação)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <button type="submit" class="bg-gold-500 hover:bg-gold-600 text-black px-10 py-3 rounded-xl font-bold transition-all shadow-lg text-sm">
                        Publicar Alterações
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-12 gap-8">
                
                <!-- Main Configuration Panel -->
                <div class="col-span-12 lg:col-span-8 space-y-6">
                    
                    <?php foreach ($sections as $key => $config) : 
                        $current_media = isset($media[$key]) ? $media[$key] : array('mode' => 'single', 'ids' => array());
                        $mode = $current_media['mode'];
                        $ids = implode(',', $current_media['ids']);
                        $btn_config = isset($buttons[$key]) ? $buttons[$key] : array('label' => $config['button_default'] ?? '', 'url' => '#');
                    ?>
                        <div class="bg-white/5 border border-white/10 rounded-[2rem] p-8 space-y-6 lp-section-box" data-section="<?php echo $key; ?>">
                            <div class="flex justify-between items-center">
                                <h3 class="text-xl font-bold bg-gradient-to-r from-white to-white/60 bg-clip-text text-transparent">
                                    <?php echo esc_html($config['label']); ?>
                                </h3>
                                <div class="flex items-center bg-black/40 p-1 rounded-xl border border-white/5">
                                    <button type="button" class="mode-toggle mode-btn-single px-4 py-2 rounded-lg text-[10px] uppercase font-bold tracking-widest transition-all <?php echo $mode === 'single' ? 'bg-gold-500 text-black' : 'text-white/40'; ?>" data-mode="single">Imagem Única</button>
                                    <button type="button" class="mode-toggle mode-btn-carousel px-4 py-2 rounded-lg text-[10px] uppercase font-bold tracking-widest transition-all <?php echo $mode === 'carousel' ? 'bg-gold-500 text-black' : 'text-white/40'; ?>" data-mode="carousel">Carrossel</button>
                                    <input type="hidden" name="media[<?php echo $key; ?>][mode]" class="section-mode" value="<?php echo $mode; ?>">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <!-- Media Upload -->
                                <div class="space-y-4">
                                    <label class="text-[10px] uppercase tracking-widest text-white/40 font-bold">Mídia da Seção</label>
                                    <div class="aspect-video bg-black/40 rounded-2xl border border-dashed border-white/10 flex flex-col items-center justify-center p-4 relative overflow-hidden group mb-2">
                                        <div class="media-preview flex gap-2 overflow-x-auto w-full h-full items-center justify-center pointer-events-none">
                                            <?php 
                                            if (!empty($current_media['ids'])) {
                                                foreach ($current_media['ids'] as $id) {
                                                    echo wp_get_attachment_image($id, 'thumbnail', false, array('class' => 'h-20 w-20 object-cover rounded-lg border border-white/10'));
                                                }
                                            } else { ?>
                                                <span class="dashicons dashicons-admin-media text-white/10" style="font-size: 48px; width: 48px; height: 48px;"></span>
                                            <?php } ?>
                                        </div>
                                        <button type="button" class="select-media-btn absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2 font-bold text-xs z-20">
                                            <span class="dashicons dashicons-cloud-upload"></span> Selecionar Arquivos
                                        </button>
                                        <input type="hidden" name="media[<?php echo $key; ?>][ids]" class="media-ids" value="<?php echo $ids; ?>">
                                    </div>
                                    <p class="text-[9px] text-white/20 italic">Formatos suportados: JPG, PNG, WEBP. Tamanho sugerido: 1920x1080px.</p>
                                </div>

                                <!-- Button Config -->
                                <?php if ($config['has_button']) : ?>
                                    <div class="space-y-6 bg-white/5 p-6 rounded-2xl border border-white/5">
                                        <label class="text-[10px] uppercase tracking-widest text-white/40 font-bold">Configuração do CTA (Botão)</label>
                                        <div class="space-y-4">
                                            <div>
                                                <p class="text-[9px] text-white/30 uppercase mb-2">Texto do Botão</p>
                                                <input type="text" name="buttons[<?php echo $key; ?>][label]" value="<?php echo esc_attr($btn_config['label']); ?>" 
                                                       class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-sm focus:border-gold-500 transition-all outline-none">
                                            </div>
                                            <div>
                                                <p class="text-[9px] text-white/30 uppercase mb-2">URL de Destino</p>
                                                <input type="text" name="buttons[<?php echo $key; ?>][url]" value="<?php echo esc_attr($btn_config['url']); ?>" 
                                                       placeholder="https://pay.exemplo.com/..."
                                                       class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-sm focus:border-gold-500 transition-all outline-none">
                                            </div>
                                        </div>
                                    </div>
                                <?php else : ?>
                                    <div class="flex items-center justify-center bg-white/5 border border-dashed border-white/10 rounded-2xl opacity-40">
                                        <p class="text-[10px] uppercase tracking-widest">Botão não disponível nesta seção</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                </div>

                <!-- Sidebar Sidebar -->
                <div class="col-span-12 lg:col-span-4 space-y-6">
                    <div class="bg-white/5 border border-white/10 rounded-[2rem] p-8 space-y-6 sticky top-32">
                        <h3 class="text-xl font-bold">Informações do Texto</h3>
                        <p class="text-sm text-white/40 leading-relaxed">
                            O conteúdo de texto dos parágrafos é carregado automaticamente do arquivo <code class="text-gold-500">site-html-gran-master.md</code>.
                        </p>
                        <div class="bg-gold-500/5 p-6 rounded-2xl border border-gold-500/20 space-y-4">
                            <h4 class="text-gold-500 text-xs font-bold uppercase tracking-widest">Dicas de Conversão</h4>
                            <ul class="text-[11px] text-white/60 space-y-3">
                                <li class="flex gap-3"><span class="text-gold-500">✓</span> Use carrosséis para mostrar depoimentos ou portfólios.</li>
                                <li class="flex gap-3"><span class="text-gold-500">✓</span> Certifique-se que o contraste do texto sobre as imagens seja alto.</li>
                                <li class="flex gap-3"><span class="text-gold-500">✓</span> Links do WhatsApp funcionam bem para o botão principal.</li>
                            </ul>
                        </div>
                        <hr class="border-white/5">
                        <div class="space-y-2">
                             <p class="text-[9px] uppercase tracking-[0.2em] text-white/30">ID da Página</p>
                             <p class="text-lg font-mono text-white/60">#<?php echo $post_id ?: 'NOVO'; ?></p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Mode Toggle Logic
    $('.mode-toggle').on('click', function() {
        const mode = $(this).data('mode');
        const $btnGroup = $(this).parent();
        
        $btnGroup.find('.mode-toggle').removeClass('bg-gold-500 text-black').addClass('text-white/40');
        $(this).addClass('bg-gold-500 text-black').removeClass('text-white/40');
        $btnGroup.find('.section-mode').val(mode);
    });

    // Media Upload Logic
    $('.select-media-btn').on('click', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const $container = $btn.closest('.lp-section-box');
        const $idsInput = $container.find('.media-ids');
        const $preview = $container.find('.media-preview');
        const mode = $container.find('.section-mode').val();

        const isCarousel = (mode === 'carousel');
        
        const frame = wp.media({
            title: isCarousel ? 'Selecionar Múltiplas Imagens (Carrossel)' : 'Selecionar Imagem Única',
            button: { text: 'Confirmar Seleção' },
            multiple: isCarousel ? 'add' : false // 'add' allows multiple selection in media library
        });

        frame.on('select', function() {
            const selection = frame.state().get('selection');
            let ids = [];
            
            // If not carousel, we only want one
            if (!isCarousel) {
                const attachment = selection.first().toJSON();
                ids.push(attachment.id);
                $preview.html(`<img src="${attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url}" class="h-20 w-20 object-cover rounded-lg border border-white/10">`);
            } else {
                $preview.empty();
                selection.each(function(attachment) {
                    const data = attachment.toJSON();
                    ids.push(data.id);
                    const thumb = data.sizes.thumbnail ? data.sizes.thumbnail.url : data.url;
                    $preview.append(`<img src="${thumb}" class="h-20 w-20 object-cover rounded-lg border border-white/10">`);
                });
            }

            $idsInput.val(ids.join(','));
        });

        frame.open();
    });
});
</script>

<style>
    #wpcontent { padding-left: 0; }
    #elite-lp-editor input:focus { ring: 0; outline: none; border-color: #D4AF37; }
</style>
