<?php
/**
 * Floating Copy-Indication Button for Educators
 */
$current_user = wp_get_current_user();
if ( ! $current_user->exists() ) return;
$ref_id = $current_user->user_login;
?>
<div id="educator-copy-link-btn" class="elite-ref-btn-container" style="font-family: 'Outfit', sans-serif;">
    <button onclick="copyCurrentPageWithRef('<?php echo esc_js($ref_id); ?>')" class="elite-ref-btn-trigger">
        <svg class="elite-ref-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 002-2h-8a2 2 0 00-2 2v8a2 2 0 00-2 2z"></path>
        </svg>
    </button>
    
    <!-- Tooltip -->
    <div class="elite-ref-tooltip">
        Copiar Link Indicação
        <div class="elite-ref-tooltip-arrow"></div>
    </div>

    <!-- Success Feedback Overlay -->
    <div id="copy-success-toast" class="elite-ref-toast">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:20px; height:20px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
        Link Copiado! 🔗
    </div>
</div>

<style>
.elite-ref-btn-container {
    position: fixed;
    bottom: 32px;
    right: 32px;
    z-index: 999999; /* Ultra high to be above everything */
}

.elite-ref-btn-trigger {
    background: #D4AF37;
    color: #000;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    border: 2px solid rgba(0,0,0,0.1);
    box-shadow: 0 10px 30px rgba(212, 175, 55, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
    outline: none;
    padding: 0;
}

.elite-ref-btn-trigger:hover {
    background: #F2D480;
    transform: scale(1.1);
    box-shadow: 0 15px 40px rgba(212, 175, 55, 0.6);
}

.elite-ref-btn-trigger:active {
    transform: scale(0.95);
}

.elite-ref-icon {
    width: 28px;
    height: 28px;
}

.elite-ref-tooltip {
    position: absolute;
    bottom: 100%;
    right: 0;
    margin-bottom: 20px;
    padding: 12px 24px;
    background: #0a0a0a;
    border: 1px solid rgba(212, 175, 55, 0.3);
    color: #D4AF37;
    border-radius: 16px;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 2px;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    transform: translateY(10px);
    transition: all 0.4s ease;
    box-shadow: 0 20px 40px rgba(0,0,0,0.4);
    backdrop-filter: blur(10px);
    pointer-events: none;
}

.elite-ref-btn-container:hover .elite-ref-tooltip {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.elite-ref-tooltip-arrow {
    position: absolute;
    top: 100%;
    right: 20px;
    width: 10px;
    height: 10px;
    background: #0a0a0a;
    border-right: 1px solid rgba(212, 175, 55, 0.3);
    border-bottom: 1px solid rgba(212, 175, 55, 0.3);
    transform: rotate(45deg) translateY(-5px);
}

.elite-ref-toast {
    position: fixed;
    bottom: 40px;
    right: 40px;
    background: #D4AF37;
    color: #000;
    padding: 16px 32px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 2px;
    box-shadow: 0 20px 50px rgba(212, 175, 55, 0.5);
    display: flex;
    align-items: center;
    gap: 12px;
    opacity: 0;
    visibility: hidden;
    transform: translateX(50px);
    transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    z-index: 1000000;
}

.elite-ref-toast.visible {
    opacity: 1;
    visibility: visible;
    transform: translateX(0);
}
</style>

<script>
function copyCurrentPageWithRef(refId) {
    try {
        const url = new URL(window.location.href);
        url.hash = '';
        url.searchParams.set('ref', refId);
        
        const fullUrl = url.toString();
        
        const handleSuccess = () => {
            const toast = document.getElementById('copy-success-toast');
            if (toast) {
                toast.classList.add('visible');
                setTimeout(() => {
                    toast.classList.remove('visible');
                }, 3500);
            }
        };

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(fullUrl).then(handleSuccess);
        } else {
            const textArea = document.createElement("textarea");
            textArea.value = fullUrl;
            textArea.style.position = "fixed";
            textArea.style.left = "-9999px";
            textArea.style.top = "0";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            handleSuccess();
        }
    } catch (e) {
        console.error('Copy Error:', e);
    }
}
</script>
