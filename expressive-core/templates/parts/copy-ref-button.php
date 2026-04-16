<?php
/**
 * Elite Referral Copy Button - REFINED LUXURY VERSION
 * A reusable, premium floating or inline component for referral links.
 */
$user_id = get_current_user_id();
$user_data = get_userdata($user_id);
if (!$user_data) return;

$ref_link = site_url('/?ref=' . $user_data->user_login);
?>

<div class="elite-copy-ref-container" style="margin-top: 20px;">
    <button onclick="copyEliteReferral(this)" 
            data-link="<?php echo esc_url($ref_link); ?>"
            class="group relative w-full py-5 bg-zinc-900 overflow-hidden border border-white/5 rounded-2xl transition-all duration-500 hover:border-gold-500/40 hover:bg-gold-500/5 shadow-xl hover:shadow-gold-500/10">
        
        <!-- Hover Glow -->
        <div class="absolute inset-0 bg-gradient-to-r from-gold-500/0 via-gold-500/5 to-gold-500/0 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-1000"></div>
        
        <div class="relative z-10 flex items-center justify-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-gold-500/10 flex items-center justify-center text-gold-500 border border-gold-500/20 group-hover:bg-gold-500 group-hover:text-black transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                </svg>
            </div>
            <div class="text-left">
                <p class="text-[9px] text-zinc-600 uppercase tracking-[0.3em] group-hover:text-gold-500/60 font-black transition-colors">Seu Link de Expansão</p>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-white uppercase tracking-widest copy-text">Copiar Link de Autoridade</span>
                    <span class="text-[10px] text-gold-500 opacity-0 group-hover:opacity-100 transition-opacity">→</span>
                </div>
            </div>
        </div>
    </button>
</div>

<script>
function copyEliteReferral(btn) {
    const link = btn.getAttribute('data-link');
    const textEl = btn.querySelector('.copy-text');
    const originalText = textEl.innerText;
    
    const handleSuccess = () => {
        textEl.innerText = "Link Copiado com Sucesso!";
        btn.classList.add('border-gold-500', 'bg-gold-500/10');
        
        // Visual feedback on the icon box
        const iconBox = btn.querySelector('.w-10');
        iconBox.classList.add('bg-gold-500', 'text-black');
        
        setTimeout(() => {
            textEl.innerText = originalText;
            btn.classList.remove('border-gold-500', 'bg-gold-500/10');
            iconBox.classList.remove('bg-gold-500', 'text-black');
        }, 3000);
    };

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(link).then(handleSuccess).catch(err => console.error('Elite Copy Error: ', err));
    } else {
        const textArea = document.createElement("textarea");
        textArea.value = link;
        textArea.style.position = "absolute";
        textArea.style.opacity = "0";
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
            handleSuccess();
        } catch (err) {
            console.error('Elite Fallback Copy Error: ', err);
        }
        document.body.removeChild(textArea);
    }
}
</script>
