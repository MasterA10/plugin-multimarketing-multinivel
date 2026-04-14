<?php
/**
 * Template Name: Admin Commission Dashboard
 * 
 * Visualization of platform sales and commissions.
 */
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Acesso negado.' );
}

global $wpdb;
$table_referrals = $wpdb->prefix . 'lms_referrals';

// Fetch aggregate data
$summary = $wpdb->get_row( "SELECT COUNT(*) as count, SUM(order_total) as total_sales, SUM(commission_amount) as total_commissions FROM $table_referrals" );
$total_sales = $summary->total_sales ?: 0;
$total_commissions = $summary->total_commissions ?: 0;
$total_referrals = $summary->count ?: 0;

// Fetch last 50 referrals
$limit = 50;
$referrals = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_referrals ORDER BY created_at DESC LIMIT %d", $limit ) );

// Fetch top educators
$ranking = Expressive_Referral::get_annual_ranking(5);

$commission_percentage = get_option( 'lms_commission_percentage', 10 );
?>

<div class="elite-commissions-wrap bg-[#111] text-white min-h-screen p-8 rounded-xl shadow-2xl mr-4 mt-4 font-sans">
    
    <!-- Header -->
    <header class="flex justify-between items-center mb-10 border-b border-white/5 pb-8">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-gold-500/10 rounded-xl flex items-center justify-center text-gold-500 border border-gold-500/20">
                <span class="dashicons dashicons-chart-area" style="font-size: 24px; width: 24px; height: 24px;"></span>
            </div>
            <div>
                <h1 class="font-serif italic text-3xl mb-1 leading-tight" style="color: #D4AF37 !important;">Painel de Comissões Elite</h1>
                <p class="text-[10px] uppercase tracking-[0.25em] font-medium" style="color: rgba(242, 212, 128, 0.6) !important;">Visão Geral de Performance Financeira</p>
            </div>
        </div>
        
        <div class="flex items-center gap-2 bg-gold-500/10 px-4 py-2 rounded-lg border border-gold-500/20">
            <span class="text-[10px] uppercase font-bold text-gold-500 mt-0.5">Taxa Atual:</span>
            <span class="text-xl font-black text-gold-500"><?php echo $commission_percentage; ?>%</span>
        </div>
    </header>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="glass p-6 rounded-2xl border border-white/5 relative overflow-hidden group">
            <div class="absolute -right-10 -bottom-10 w-32 h-32 bg-gold-500/5 rounded-full blur-2xl group-hover:bg-gold-500/10 transition-all"></div>
            <p class="text-[10px] uppercase tracking-widest text-zinc-500 mb-2 font-bold">Volume Total em Vendas</p>
            <div class="text-3xl font-serif italic text-white mb-2">
                <span class="text-xs text-gold-500 not-italic mr-1">R$</span>
                <?php echo number_format($total_sales, 2, ',', '.'); ?>
            </div>
            <p class="text-[10px] text-zinc-600 italic">Total acumulado processado pelo sistema.</p>
        </div>

        <div class="bg-gold-500/5 p-6 rounded-2xl border border-gold-500/20 shadow-lg shadow-gold-500/5 relative overflow-hidden group">
            <div class="absolute -right-10 -bottom-10 w-32 h-32 bg-gold-500/10 rounded-full blur-2xl group-hover:bg-gold-500/20 transition-all"></div>
            <p class="text-[10px] uppercase tracking-widest text-gold-500 mb-2 font-bold">Comissões Geradas</p>
            <div class="text-3xl font-serif italic text-gold-500 mb-2">
                <span class="text-xs not-italic mr-1">R$</span>
                <?php echo number_format($total_commissions, 2, ',', '.'); ?>
            </div>
            <p class="text-[10px] text-gold-500/40 italic">Valores brutos aplicados pela taxa de <?php echo $commission_percentage; ?>%.</p>
        </div>

        <div class="background: rgba(255,255,255,0.02) p-6 rounded-2xl border border-white/5 relative overflow-hidden group">
            <p class="text-[10px] uppercase tracking-widest text-zinc-500 mb-2 font-bold">Total de Indicações</p>
            <div class="text-3xl font-serif italic text-white mb-2">
                <?php echo $total_referrals; ?>
                <span class="text-[10px] text-zinc-500 not-italic uppercase tracking-widest ml-2">Sucessos</span>
            </div>
            <p class="text-[10px] text-zinc-600 italic">Conversões registradas entre membros.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Table -->
        <div class="lg:col-span-2 space-y-6">
            <div class="glass p-8 rounded-[30px] border border-white/5 relative overflow-hidden">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold font-serif italic text-gold-500">Últimas Atividades Financeiras</h3>
                    <div class="text-[9px] uppercase tracking-widest text-zinc-600">Mostrando as últimas <?php echo count($referrals); ?></div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-white/5 text-[9px] uppercase tracking-widest text-zinc-500">
                                <th class="py-4 px-2">Educador</th>
                                <th class="py-4 px-2">Autoridade</th>
                                <th class="py-4 px-2 text-right">Venda (R$)</th>
                                <th class="py-4 px-2 text-right">Comissão (R$)</th>
                                <th class="py-4 px-2 text-right">Data</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <?php if($referrals): foreach($referrals as $ref): 
                                $educator = get_userdata($ref->educator_id);
                                $authority = get_userdata($ref->authority_id);
                                if(!$educator || !$authority) continue;
                            ?>
                            <tr class="group hover:bg-white/[0.02] transition-all">
                                <td class="py-4 px-2">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full border border-gold-500/20 overflow-hidden bg-black/40">
                                            <?php echo Expressive_Core::get_elite_avatar($ref->educator_id, 32, 'rounded-full'); ?>
                                        </div>
                                        <div>
                                            <div class="text-sm font-semibold text-white"><?php echo esc_html($educator->display_name); ?></div>
                                            <div class="text-[8px] text-zinc-600 uppercase">Educador</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-2">
                                    <div class="text-sm font-medium text-zinc-300"><?php echo esc_html($authority->display_name); ?></div>
                                    <div class="text-[8px] text-zinc-600 uppercase">Novo Membro</div>
                                </td>
                                <td class="py-4 px-2 text-right text-xs font-bold text-white">
                                    <?php echo number_format($ref->order_total, 2, ',', '.'); ?>
                                </td>
                                <td class="py-4 px-2 text-right text-xs font-black text-gold-500">
                                    <?php echo number_format($ref->commission_amount, 2, ',', '.'); ?>
                                </td>
                                <td class="py-4 px-2 text-right text-[10px] text-zinc-500 font-mono">
                                    <?php echo date('d/m/y H:i', strtotime($ref->created_at)); ?>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="5" class="py-10 text-center text-xs text-zinc-600 italic">Nenhuma comissão registrada.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar / Ranking -->
        <div class="space-y-6">
            <div class="glass p-8 rounded-[30px] border border-white/5">
                <h3 class="text-xl font-bold font-serif italic text-gold-500 mb-6 flex items-center gap-3">
                    <span class="w-1 h-6 bg-gold-500 rounded-full"></span>
                    Top Performances
                </h3>

                <div class="space-y-6">
                    <?php if($ranking): foreach($ranking as $index => $row): 
                        $user = get_userdata($row->educator_id);
                        if(!$user) continue;
                    ?>
                    <div class="flex items-center justify-between group">
                        <div class="flex items-center gap-3">
                            <span class="text-xl font-black italic <?php echo $index < 3 ? 'text-gold-500' : 'text-zinc-700'; ?> opacity-50"><?php echo $index + 1; ?></span>
                            <div class="w-10 h-10 rounded-full border border-white/5 p-0.5">
                                <?php echo Expressive_Core::get_elite_avatar($row->educator_id, 40, 'rounded-full'); ?>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-white"><?php echo esc_html($user->display_name); ?></div>
                                <div class="text-[9px] text-zinc-600 uppercase tracking-widest"><?php echo $row->ref_count; ?> Indicações</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs font-bold text-gold-500">R$ <?php echo number_format($row->total_commissions, 0, ',', '.'); ?></div>
                            <div class="text-[8px] text-zinc-600 uppercase tracking-widest">Ganhos</div>
                        </div>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
                
                <div class="mt-8 pt-8 border-t border-white/5">
                    <p class="text-[10px] text-zinc-500 italic text-center leading-relaxed">
                        Estes valores são calculados em tempo real com base nas transações registradas no ecossistema Elite.
                    </p>
                </div>
            </div>

            <!-- Help/Notice Card -->
            <div class="bg-red-900/5 p-8 rounded-[30px] border border-red-900/10">
                <h4 class="text-[10px] font-bold uppercase tracking-[0.2em] text-red-500 mb-4">Nota de Gestão</h4>
                <p class="text-[11px] text-zinc-400 leading-relaxed font-serif italic">
                    As comissões exibidas acima referem-se estritamente aos cálculos do motor multi-nível Elite. O pagamento deve ser processado manualmente ou através de integrações externas vinculadas ao seu faturamento principal.
                </p>
            </div>
        </div>
    </div>

</div>

<style>
    #wpcontent { background: #000 !important; padding-left: 0 !important; }
    #wpbody-content { padding-bottom: 0 !important; }
    .elite-commissions-wrap { font-family: 'Outfit', sans-serif !important; }
    .elite-commissions-wrap h1, .elite-commissions-wrap h3 { font-family: 'Playfair Display', serif !important; }
    .glass { background: rgba(255, 255, 255, 0.015); backdrop-filter: blur(10px); }
</style>
