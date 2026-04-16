<?php
/**
 * Elite Certificate Modal - REFINED LUXURY VERSION
 * Shows a congratulations message and download link when the user hits a milestone.
 */
$user_id = get_current_user_id();
$user_data = get_userdata($user_id);
?>

<div id="elite-cert-modal" class="fixed inset-0 z-[100] bg-black/90 backdrop-blur-2xl hidden flex items-center justify-center p-6 text-center animate-fade-in">
    <div class="max-w-2xl w-full glass p-10 md:p-16 rounded-[60px] border border-gold-500/30 shadow-2xl relative overflow-hidden group">
        <!-- Background Effects -->
        <div class="absolute -top-24 -right-24 w-64 h-64 bg-gold-500/10 rounded-full blur-[100px] group-hover:bg-gold-500/20 transition-all duration-1000"></div>
        <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-gold-500/5 rounded-full blur-[100px]"></div>

        <div class="relative z-10 space-y-10">
            <!-- Achievement Icon -->
            <div class="relative w-32 h-32 mx-auto">
                <div class="absolute inset-0 bg-gold-500/20 rounded-full animate-ping opacity-20"></div>
                <div class="w-32 h-32 bg-gold-500/10 rounded-full flex items-center justify-center border border-gold-500/30 shadow-2xl shadow-gold-500/20 relative">
                    <svg class="w-16 h-16 text-gold-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                    </svg>
                </div>
            </div>

            <div class="space-y-4">
                <span class="text-[10px] text-gold-500 uppercase tracking-[0.5em] font-bold">Maestria Alcançada</span>
                <h2 class="text-4xl md:text-5xl font-serif italic text-white leading-tight">Parabéns, <br> <span class="text-gold-500"><?php echo esc_html($user_data->first_name ?: $user_data->display_name); ?>!</span></h2>
                <p class="text-zinc-500 text-sm leading-relaxed max-w-sm mx-auto font-light">
                    Sua dedicação o levou a completar mais de <span class="text-white font-medium">75% da jornada</span>. Seu certificado de especialização Gran Master já está disponível para outorga.
                </p>
            </div>

            <!-- Certificate Preview Card (Minimalist) -->
            <div class="bg-black/40 border border-white/5 p-6 rounded-3xl group/card relative overflow-hidden transition-all hover:border-gold-500/20">
                <div class="flex items-center gap-6">
                    <div class="w-20 aspect-[4/3] bg-zinc-900 rounded-lg border border-white/10 flex items-center justify-center">
                        <svg class="w-8 h-8 text-zinc-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <div class="text-left">
                        <h4 class="text-xs font-bold text-white uppercase tracking-widest">Credencial Gran Master</h4>
                        <p class="text-[9px] text-zinc-600 uppercase tracking-widest mt-1">Válido Internacionalmente</p>
                    </div>
                </div>
            </div>

            <div class="pt-6 space-y-4">
                <button id="elite-download-cert-btn" class="block w-full py-5 bg-gold-500 text-black font-black uppercase tracking-[0.2em] text-xs rounded-2xl hover:bg-white hover:scale-[1.02] transition-all shadow-xl shadow-gold-500/20 cursor-pointer">
                    Baixar Meu Certificado
                </button>
                <button onclick="closeCertModal()" class="text-[10px] text-zinc-600 hover:text-white uppercase tracking-widest block mx-auto transition-colors">
                    Continuar Estudando
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function closeCertModal() {
    const modal = document.getElementById('elite-cert-modal');
    modal.classList.remove('opacity-100');
    modal.classList.add('opacity-0');
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }, 300);
}

function showCertModal(courseId, nonce) {
    const modal = document.getElementById('elite-cert-modal');
    const downloadBtn = document.getElementById('elite-download-cert-btn');
    
    // Update download link
    downloadBtn.onclick = function() {
        const url = `<?php echo site_url(); ?>/?lms_action=view_certificate&course_id=${courseId}&nonce=${nonce}`;
        window.open(url, '_blank');
        closeCertModal();
    };

    modal.classList.remove('hidden', 'opacity-0');
    modal.classList.add('opacity-100');
    document.body.style.overflow = 'hidden';
}
</script>
