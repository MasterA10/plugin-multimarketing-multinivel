<?php
/**
 * Global Floating Referral Button
 * 
 * Exclusively for Educators (Educadoras/Administrators).
 * Allows quick copying of the referral link for the current page.
 */
if ( ! is_user_logged_in() ) return;

$current_user = wp_get_current_user();
$ref_code = $current_user->user_login;
?>

<div id="elite-referral-fab" class="elite-fab-container">
    <button onclick="copyEliteReferralLink(this)" class="elite-fab-button" title="Copiar Link de Indicação">
        <div class="elite-fab-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path></svg>
        </div>
        <span class="elite-fab-label">Copiar Link de Convite</span>
    </button>
</div>

<script>
function copyEliteReferralLink(btn) {
    // 1. Get current URL and strip existing ref params or query strings
    const url = new URL(window.location.href);
    url.searchParams.delete('ref');
    
    // 2. Append current user's ref code
    url.searchParams.set('ref', '<?php echo esc_js($ref_code); ?>');
    
    const referralUrl = url.toString();
    const label = btn.querySelector('.elite-fab-label');
    const originalText = label.innerText;

    // 3. Copy with modern API and fallback
    const handleSuccess = () => {
        label.innerText = "¡Link Copiado!";
        btn.classList.add('elite-fab-success');
        setTimeout(() => {
            label.innerText = originalText;
            btn.classList.remove('elite-fab-success');
        }, 2500);
    };

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(referralUrl).then(handleSuccess).catch(err => {
            console.error('Erro ao copiar link:', err);
            prompt("Copie seu link de indicação:", referralUrl);
        });
    } else {
        // Fallback for non-HTTPS or older browsers
        const textArea = document.createElement("textarea");
        textArea.value = referralUrl;
        textArea.style.position = "fixed";
        textArea.style.left = "-9999px";
        textArea.style.top = "0";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            document.execCommand('copy');
            handleSuccess();
        } catch (err) {
            prompt("Copie seu link de indicação:", referralUrl);
        }
        document.body.removeChild(textArea);
    }
}
</script>

<style>
.elite-fab-container {
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 99999;
}

.elite-fab-button {
    background: #D4AF37;
    background: linear-gradient(135deg, #D4AF37 0%, #F2D480 100%);
    color: #000;
    border: none;
    height: 56px;
    width: 56px;
    border-radius: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 10px 25px rgba(212, 175, 55, 0.4);
    transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    padding: 0 0 0 0;
    overflow: hidden;
    position: relative;
}

.elite-fab-icon {
    width: 24px;
    height: 24px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.elite-fab-icon svg {
    width: 100%;
    height: 100%;
}

.elite-fab-label {
    white-space: nowrap;
    font-family: 'Outfit', sans-serif;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    opacity: 0;
    width: 0;
    overflow: hidden;
    transition: all 0.4s ease;
}

/* Hover State */
@media (min-width: 1024px) {
    .elite-fab-button:hover {
        width: 220px;
        padding-left: 20px;
        padding-right: 20px;
        gap: 12px;
    }
    .elite-fab-button:hover .elite-fab-label {
        opacity: 1;
        width: auto;
    }
}

.elite-fab-button:active {
    transform: scale(0.95);
}

.elite-fab-success {
    background: #fff !important;
    box-shadow: 0 10px 30px rgba(255, 255, 255, 0.3);
}

.elite-fab-success .elite-fab-label {
    opacity: 1 !important;
    width: auto !important;
    color: #000;
}

/* Entrance Animation */
@keyframes eliteFabPop {
    from { opacity: 0; transform: scale(0.5) translateY(20px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}

.elite-fab-container {
    animation: eliteFabPop 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@media (max-width: 768px) {
    .elite-fab-container {
        bottom: 20px;
        right: 20px;
    }
}
</style>
