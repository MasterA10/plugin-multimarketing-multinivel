<?php
/**
 * Global Certificate Name Validation Modal 
 */
if ( ! is_user_logged_in() ) {
    return;
}

$current_user = wp_get_current_user();
?>
<!-- Certificate Name Modal Overlay (Global) -->
<div id="certificate-modal" class="fixed inset-0 bg-black/80 backdrop-blur-xl z-[60] hidden flex items-center justify-center p-6 transition-all duration-500 opacity-0 overlay-hidden" style="font-family: 'Outfit', sans-serif;">
    <div class="w-full max-w-md bg-[#0a0a0a] rounded-[40px] overflow-hidden border border-[#D4AF37]/20 shadow-2xl relative">
        <div class="p-10 text-center">
            <div class="w-20 h-20 bg-[#D4AF37]/10 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-[#D4AF37]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            
            <h3 class="text-2xl font-serif italic text-[#D4AF37] font-bold mb-2">Validar seu Nome</h3>
            <p class="text-xs text-zinc-500 uppercase tracking-widest mb-8">Confirme como seu nome deve ser impresso no certificado oficial.</p>
            
            <div class="space-y-6">
                <div class="relative">
                    <input type="text" id="cert-user-name" 
                           class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white text-sm focus:outline-none focus:border-[#D4AF37]/50 transition-all font-serif italic text-lg text-center"
                           placeholder="Seu Nome Completo"
                           value="<?php echo esc_attr( $current_user->display_name ); ?>">
                </div>
                
                <div class="flex flex-col gap-3">
                    <button id="generate-cert-final-btn" class="w-full py-4 bg-[#D4AF37] hover:bg-[#F2D480] text-black font-bold text-[10px] uppercase tracking-[0.2em] rounded-2xl transition-all shadow-xl shadow-[#D4AF37]/10 cursor-pointer">
                        Confirmar e Gerar Certificado
                    </button>
                    <button onclick="toggleCertificateModal()" class="w-full py-4 text-[9px] text-zinc-500 uppercase tracking-widest hover:text-white transition-all cursor-pointer">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // --- GLOBAL CERTIFICATE LOGIC ---
    let currentCertData = { courseId: 0, nonce: '', isGlobal: false };

    function toggleCertificateModal() {
        const modal = document.getElementById('certificate-modal');
        if (!modal) return;
        
        if (modal.classList.contains('hidden')) {
            modal.classList.remove('hidden', 'opacity-0');
            modal.classList.add('opacity-100');
            document.body.style.overflow = 'hidden';
        } else {
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }, 300);
        }
    }

    // Attach globally
    window.prepareCertificate = function(courseId, nonce, isGlobal = false) {
        currentCertData = { courseId, nonce, isGlobal };
        toggleCertificateModal();
    };

    document.addEventListener('DOMContentLoaded', function() {
        const genBtn = document.getElementById('generate-cert-final-btn');
        if (genBtn) {
            genBtn.addEventListener('click', function() {
                const nameInput = document.getElementById('cert-user-name');
                const name = nameInput ? nameInput.value.trim() : '';
                if (!name) {
                    alert('Por favor, informe seu nome para o certificado.');
                    return;
                }

                let url = '';
                if (currentCertData.isGlobal) {
                    url = '<?php echo home_url("/certificado-elite"); ?>';
                } else {
                    url = '<?php echo site_url(); ?>/?lms_action=view_certificate&course_id=' + currentCertData.courseId + '&nonce=' + currentCertData.nonce;
                }

                url += (url.includes('?') ? '&' : '?') + 'cert_name=' + encodeURIComponent(name);
                
                toggleCertificateModal();
                window.open(url, '_blank');
            });
        }
    });
</script>
