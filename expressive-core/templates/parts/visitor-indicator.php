<?php
/**
 * Visitor Indicator Component
 * 
 * High-fidelity floating badge for non-logged-in users.
 */
if ( is_user_logged_in() ) return;
?>

<div id="elite-visitor-indicator" class="fixed bottom-8 right-8 z-[9999] animate-fade-in-up">
    <div class="glass-elite p-6 rounded-[32px] border border-gold-500/30 shadow-2xl shadow-gold-500/10 max-w-[320px] backdrop-blur-xl bg-black/60">
        
        <!-- Header -->
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-2xl bg-gold-500/10 border border-gold-500/20 flex items-center justify-center text-gold-500 group relative">
                <div class="absolute inset-0 bg-gold-500/20 blur-xl rounded-full animate-pulse opacity-50"></div>
                <svg class="w-6 h-6 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
            </div>
            <div>
                <p class="text-[10px] text-zinc-500 uppercase tracking-[0.2em] font-bold">Acesso Curiosidade</p>
                <h4 class="text-sm font-bold text-white tracking-tight">Modo Visitante <span class="text-gold-500 italic font-serif">Ativo</span></h4>
            </div>
        </div>

        <!-- Description -->
        <p class="text-xs text-zinc-400 mb-6 leading-relaxed">
            Você está explorando a plataforma em modo visitante. Para desbloquear <span class="text-white font-medium italic">descontos de até 40%</span> e conteúdos exclusivos, torne-se um membro.
        </p>

        <!-- Actions -->
        <div class="flex flex-col gap-3">
            <a href="<?php echo home_url('/elite/ccp-academy/'); ?>" class="w-full bg-gold-500 hover:bg-gold-400 text-black px-6 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all shadow-lg shadow-gold-500/20 text-center flex items-center justify-center gap-2 group">
                <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                Adquirir Acesso Elite
            </a>
            <a href="<?php echo site_url('/login/'); ?>" class="w-full bg-white/5 hover:bg-white/10 text-white border border-white/10 px-6 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all text-center">
                Já sou Membro (Login)
            </a>
        </div>

        <!-- Subtle Close -->
        <button onclick="document.getElementById('elite-visitor-indicator').remove()" class="absolute -top-2 -right-2 w-6 h-6 bg-zinc-800 border border-white/10 rounded-full flex items-center justify-center text-zinc-500 hover:text-white transition-colors">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>
</div>

<style>
    .glass-elite {
        background: rgba(10, 10, 10, 0.85);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
    }
    .animate-fade-in-up {
        animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
