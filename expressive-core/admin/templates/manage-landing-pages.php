<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$status = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';
$lp_query = new WP_Query( array(
    'post_type'      => 'elite_lp',
    'posts_per_page' => -1,
    'post_status'    => 'publish'
) );
?>

<div class="wrap elite-lms-admin">
    <div class="flex flex-col gap-8 p-8 bg-[#0a0a0a] min-h-screen text-white font-['Outfit']">
        
        <!-- Header -->
        <div class="flex justify-between items-center bg-white/5 p-8 rounded-3xl border border-white/10 backdrop-blur-xl">
            <div>
                <h1 class="text-4xl font-bold bg-gradient-to-r from-gold-400 via-gold-500 to-gold-600 bg-clip-text text-transparent">Elite Landing Pages</h1>
                <p class="text-white/40 mt-1 uppercase tracking-[0.2em] text-xs">Páginas de Alta Conversão & Design Premium</p>
            </div>
        </div>

        <?php if ( $status === 'saved' ): ?>
            <div class="bg-green-500/20 border border-green-500/50 text-green-200 p-4 rounded-2xl animate-pulse">
                Página salva com sucesso!
            </div>
        <?php endif; ?>

        <!-- Grid de Páginas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if ( $lp_query->have_posts() ) : while ( $lp_query->have_posts() ) : $lp_query->the_post(); 
                $template_name = get_post_meta( get_the_ID(), '_elite_lp_template', true ) ?: 'gran_master';
                $url = get_permalink();
                ?>
                <div class="group bg-white/5 border border-white/10 rounded-[2.5rem] overflow-hidden hover:border-gold-500/50 transition-all duration-500 hover:shadow-[0_20px_40px_rgba(0,0,0,0.4)]">
                    <div class="aspect-video bg-gradient-to-br from-[#1a1a1a] to-black relative flex items-center justify-center p-8 overflow-hidden">
                        <!-- Preview Mockup -->
                        <div class="w-full h-full border border-white/5 rounded-xl bg-black/40 backdrop-blur-sm p-4 flex flex-col gap-2 transform group-hover:scale-110 transition-transform duration-700">
                           <div class="w-1/2 h-2 bg-gold-500/20 rounded"></div>
                           <div class="w-3/4 h-1 bg-white/10 rounded"></div>
                           <div class="mt-auto w-full h-4 bg-gold-500/40 rounded-lg"></div>
                        </div>
                        <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-4">
                            <a href="<?php echo $url; ?>" target="_blank" class="p-4 bg-white/10 hover:bg-white/20 rounded-full border border-white/20 transition-all">
                                <span class="dashicons dashicons-visibility text-white"></span>
                            </a>
                        </div>
                    </div>
                    
                    <div class="p-8 space-y-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-xl font-bold text-white"><?php the_title(); ?></h3>
                                <p class="text-gold-500/60 text-[10px] uppercase tracking-widest mt-1">Template: <?php echo esc_html(str_replace('_', ' ', $template_name)); ?></p>
                            </div>
                            <span class="px-3 py-1 bg-white/5 rounded-full text-[9px] border border-white/10 text-white/40 font-mono">
                                #<?php the_ID(); ?>
                            </span>
                        </div>

                        <p class="text-white/40 text-xs font-mono truncate bg-black/40 p-3 rounded-xl border border-white/5">
                            <?php echo $url; ?>
                        </p>

                        <div class="flex gap-4 pt-4">
                            <a href="<?php echo admin_url('admin.php?page=elite-pages&action=edit&post_id=' . get_the_ID()); ?>" 
                               class="flex-1 bg-gradient-to-r from-gold-500/20 to-gold-600/20 hover:from-gold-500/30 hover:to-gold-600/30 border border-gold-500/30 text-white text-center py-4 rounded-xl text-xs font-bold transition-all uppercase tracking-widest">
                                <span class="dashicons dashicons-admin-tools mr-2" style="font-size: 16px;"></span>
                                Configurar Design
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; wp_reset_postdata(); else : ?>
                <div class="col-span-full py-20 bg-white/5 border border-dashed border-white/10 rounded-[3rem] text-center">
                    <div class="text-white/20 mb-4 flex justify-center">
                        <span class="dashicons dashicons-layout" style="font-size: 64px; width: 64px; height: 64px;"></span>
                    </div>
                    <p class="text-white/40 uppercase tracking-widest text-sm">Nenhuma página criada ainda</p>
                    <a href="<?php echo admin_url('admin.php?page=elite-pages&action=new'); ?>" class="text-gold-500 hover:underline mt-4 inline-block font-bold">Criar minha primeira Landing Page</a>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<style>
    #wpcontent { padding-left: 0; }
    .elite-lms-admin { font-family: 'Outfit', sans-serif; }
    .dashicons { font-family: dashicons !important; }
</style>
