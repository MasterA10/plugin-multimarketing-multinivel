<?php
/**
 * Template Name: Purchase Access Page
 * 
 * Flow for acquiring elite member area access.
 */
get_header(); ?>

<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&family=Playfair+Display:ital,wght@0,700;1,700&display=swap" rel="stylesheet">
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    gold: {
                        400: '#F2D480',
                        500: '#D4AF37',
                        600: '#B8962E',
                    },
                    onyx: '#121212',
                },
                fontFamily: {
                    sans: ['Outfit', 'sans-serif'],
                    serif: ['Playfair Display', 'serif'],
                }
            }
        }
    }
</script>

<div class="min-h-screen bg-black text-white font-sans flex items-center justify-center py-20 px-4">
    <div class="max-w-4xl w-full">
        <!-- Brand -->
        <div class="text-center mb-16">
            <h1 class="font-serif text-4xl text-gold-500 italic mb-2">Elite Membership</h1>
            <p class="text-[10px] text-zinc-500 uppercase tracking-[0.4em] font-bold">O Próximo Nível da Sua Jornada</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            <!-- Left: Value Proposition -->
            <div class="space-y-8">
                <h2 class="text-3xl font-serif italic text-white leading-tight">Desbloqueie o Poder da <span class="text-gold-500">Exclusividade Elite.</span></h2>
                <p class="text-zinc-400 text-sm leading-relaxed">Sua conta foi criada com sucesso, mas o acesso à Área de Membros requer uma assinatura ativa. Ao se tornar Elite, você garante:</p>
                
                <ul class="space-y-4">
                    <li class="flex items-start gap-3">
                        <div class="w-5 h-5 rounded-full bg-gold-500/10 flex items-center justify-center text-gold-500 mt-0.5 border border-gold-500/20">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <span class="text-xs text-zinc-300">Treinamentos Técnicos de Alta Performance</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <div class="w-5 h-5 rounded-full bg-gold-500/10 flex items-center justify-center text-gold-500 mt-0.5 border border-gold-500/20">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <span class="text-xs text-zinc-300">Acesso a Lives e Mentorias Exclusivas</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <div class="w-5 h-5 rounded-full bg-gold-500/10 flex items-center justify-center text-gold-500 mt-0.5 border border-gold-500/20">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <span class="text-xs text-zinc-300">Até 40% de Desconto em todo o Ecossistema</span>
                    </li>
                </ul>
            </div>

            <!-- Right: Action Card -->
            <div class="bg-white/[0.03] backdrop-blur-xl border border-white/10 rounded-[40px] p-10 shadow-2xl relative overflow-hidden group">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-gold-500/10 rounded-full blur-3xl group-hover:bg-gold-500/20 transition-all"></div>
                
                <div class="relative z-10">
                    <div class="mb-8">
                        <span class="text-[9px] font-bold uppercase tracking-widest text-gold-500 bg-gold-500/10 px-3 py-1 rounded-full border border-gold-500/20">Plano Elite</span>
                        <div class="mt-4 flex items-baseline gap-2">
                            <span class="text-4xl font-black text-white">R$ 149</span>
                            <span class="text-zinc-500 text-sm">/mês</span>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <p class="text-[10px] text-zinc-500 italic">O fluxo de pagamento será integrado aqui no próximo passo.</p>
                        <a href="#" class="inline-block w-full py-4 bg-gradient-to-r from-gold-600 to-gold-400 text-black font-bold uppercase tracking-widest text-[10px] rounded-2xl hover:scale-[1.02] transition-all shadow-lg shadow-gold-500/20 text-center">Assinar Agora</a>
                        
                        <div class="pt-6 border-t border-white/5 text-center">
                            <a href="<?php echo home_url('/area-de-membros/'); ?>" class="text-[9px] uppercase tracking-widest text-zinc-600 hover:text-white transition-colors">Voltar para o Início</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
