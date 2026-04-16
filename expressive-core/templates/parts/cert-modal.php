<?php
/**
 * Elite Certificate Modal - Name Confirmation
 * Shows a name input for the student to confirm before generating the certificate.
 */
$user_id = get_current_user_id();
$user_data = get_userdata($user_id);
if (!$user_data) return;
?>

<div id="elite-cert-modal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.92); backdrop-filter:blur(24px); align-items:center; justify-content:center; padding:24px; text-align:center; transition:opacity 0.3s ease; opacity:0;">
    <div style="max-width:600px; width:100%; background:rgba(255,255,255,0.03); border:1px solid rgba(212,175,55,0.3); border-radius:40px; padding:60px 40px; position:relative; overflow:hidden; box-shadow:0 25px 60px rgba(0,0,0,0.6);">
        <!-- Background Glow -->
        <div style="position:absolute; top:-60px; right:-60px; width:200px; height:200px; background:rgba(212,175,55,0.08); border-radius:50%; filter:blur(80px); pointer-events:none;"></div>

        <!-- Achievement Icon -->
        <div style="width:100px; height:100px; margin:0 auto 30px; background:rgba(212,175,55,0.1); border:1px solid rgba(212,175,55,0.3); border-radius:50%; display:flex; align-items:center; justify-content:center;">
            <svg style="width:50px; height:50px; color:#D4AF37;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
            </svg>
        </div>

        <!-- Text -->
        <div style="margin-bottom:30px;">
            <p style="font-size:10px; color:#D4AF37; text-transform:uppercase; letter-spacing:0.5em; font-weight:700; margin-bottom:16px; font-family:'Outfit',sans-serif;">Maestria Alcançada</p>
            <h2 style="font-size:2.2rem; font-family:'Playfair Display',serif; font-style:italic; color:#fff; margin:0 0 12px; line-height:1.2;">Parabéns, <span style="color:#D4AF37;"><?php echo esc_html($user_data->first_name ?: $user_data->display_name); ?>!</span></h2>
            <p style="font-size:13px; color:#888; max-width:360px; margin:0 auto; line-height:1.6; font-family:'Outfit',sans-serif;">
                Confirme seu nome completo abaixo.<br>Este será o nome impresso no certificado.
            </p>
        </div>

        <!-- Name Input -->
        <div style="max-width:400px; margin:0 auto 30px; position:relative;">
            <input type="text" id="elite-cert-student-name" 
                   value="<?php echo esc_attr($user_data->display_name); ?>"
                   style="width:100%; box-sizing:border-box; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:16px; padding:16px 24px; color:#fff; text-align:center; font-family:'Playfair Display',serif; font-style:italic; font-size:18px; outline:none; transition:border-color 0.3s;"
                   onfocus="this.style.borderColor='rgba(212,175,55,0.5)'"
                   onblur="this.style.borderColor='rgba(255,255,255,0.1)'"
                   placeholder="Seu Nome Completo">
        </div>

        <!-- Buttons -->
        <div style="display:flex; flex-direction:column; gap:12px; align-items:center;">
            <button id="elite-download-cert-btn" style="width:100%; max-width:400px; padding:18px; background:#D4AF37; color:#000; border:none; border-radius:16px; font-size:11px; font-weight:900; text-transform:uppercase; letter-spacing:0.2em; cursor:pointer; box-shadow:0 10px 30px rgba(212,175,55,0.2); transition:all 0.3s; font-family:'Outfit',sans-serif;">
                Emitir Certificado Agora
            </button>
            <button onclick="closeCertModal()" style="background:none; border:none; color:#666; font-size:10px; text-transform:uppercase; letter-spacing:0.15em; cursor:pointer; padding:8px; transition:color 0.3s; font-family:'Outfit',sans-serif;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#666'">
                Continuar Estudando
            </button>
        </div>
    </div>
</div>

<script>
function closeCertModal() {
    var modal = document.getElementById('elite-cert-modal');
    if (!modal) return;
    modal.style.opacity = '0';
    setTimeout(function() {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }, 300);
}

/**
 * Global function to show certificate confirmation modal.
 * @param {number} id - Course ID (0 for global)
 * @param {string} nonce - WP nonce
 * @param {number} type - 0 = course cert, 1 = global cert
 */
function showEliteCertModal(id, nonce, type) {
    var modal = document.getElementById('elite-cert-modal');
    var downloadBtn = document.getElementById('elite-download-cert-btn');
    var nameInput = document.getElementById('elite-cert-student-name');

    if (!modal || !downloadBtn || !nameInput) {
        console.error('Elite Cert Modal: Elements not found');
        return;
    }

    type = type || 0;

    // Show the modal
    modal.style.display = 'flex';
    // Force reflow before transition
    modal.offsetHeight;
    modal.style.opacity = '1';
    document.body.style.overflow = 'hidden';

    // Bind download action
    downloadBtn.onclick = function() {
        var studentName = encodeURIComponent(nameInput.value.trim());
        if (!studentName) {
            nameInput.style.borderColor = 'red';
            nameInput.focus();
            return;
        }
        var baseUrl = '<?php echo esc_url(site_url()); ?>';
        var url = '';

        if (type === 0) {
            url = baseUrl + '/?lms_action=view_certificate&course_id=' + id + '&nonce=' + nonce + '&student_name=' + studentName;
        } else {
            url = baseUrl + '/certificado-elite/?nonce=' + nonce + '&student_name=' + studentName;
        }

        window.open(url, '_blank');
        closeCertModal();
    };
}

// Close modal on backdrop click
document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('elite-cert-modal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeCertModal();
            }
        });
    }
});
</script>
