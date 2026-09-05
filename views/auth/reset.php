<div class="auth-shell">


    <div class="auth-side">
        <div class="auth-side-brand">
            <i class="bi bi-recycle"></i> EcoBin
        </div>

        <div class="auth-side-quote">
            Almost there — set a strong new password to secure your account.
        </div>

        <div class="auth-side-stat">
            <div>
                <div class="auth-side-stat-num">✅</div>
                <div class="auth-side-stat-label">Verified reset link · one-time use</div>
            </div>
        </div>
    </div>


    <div class="auth-form-panel">
        <div class="auth-form-box">

            <div style="width:56px;height:56px;background:linear-gradient(135deg,#1b7f4f,#27ae60);border-radius:14px;display:flex;align-items:center;justify-content:center;margin-bottom:20px;">
                <i class="bi bi-shield-lock-fill text-white" style="font-size:26px;"></i>
            </div>

            <h2 style="margin-bottom:6px;">Set a new password</h2>
            <p class="eco-subheading" style="margin-bottom:28px;">
                Choose a strong password for your EcoBin account. Minimum 8 characters.
            </p>

            <?php
            $flashType = $_SESSION['flash_type'] ?? null;
            $flashMsg  = $_SESSION['flash_msg'] ?? null;
            unset($_SESSION['flash_type'], $_SESSION['flash_msg']);
            ?>

            <?php if ($flashMsg): ?>
                <div class="alert alert-<?= $flashType === 'success' ? 'success' : 'danger' ?> d-flex align-items-center gap-2 mb-4" role="alert" style="border-radius:10px;font-size:0.9rem;">
                    <i class="bi bi-<?= $flashType === 'success' ? 'check-circle' : 'exclamation-circle' ?>-fill"></i>
                    <?= \EcoBin\Services\Security::e($flashMsg) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="index.php?page=reset" novalidate id="reset-form">
                <input type="hidden" name="csrf_token" value="<?= \EcoBin\Services\Security::csrfToken() ?>">
                <input type="hidden" name="token"      value="<?= \EcoBin\Services\Security::e($token ?? '') ?>">

                <div class="auth-field">
                    <label for="reset-password">New password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0" style="border-radius:10px 0 0 10px;">
                            <i class="bi bi-lock text-muted"></i>
                        </span>
                        <input
                            id="reset-password"
                            type="password"
                            name="password"
                            class="form-control border-start-0 border-end-0"
                            style="border-radius:0;"
                            placeholder="Minimum 8 characters"
                            minlength="8"
                            autocomplete="new-password"
                            required
                        >
                        <button type="button" class="input-group-text bg-white border-start-0" style="border-radius:0 10px 10px 0;cursor:pointer;" onclick="togglePassword('reset-password', this)">
                            <i class="bi bi-eye text-muted" id="eye-reset-password"></i>
                        </button>
                    </div>
                </div>

                <div class="auth-field">
                    <label for="reset-confirm">Confirm new password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0" style="border-radius:10px 0 0 10px;">
                            <i class="bi bi-lock-fill text-muted"></i>
                        </span>
                        <input
                            id="reset-confirm"
                            type="password"
                            name="password_confirm"
                            class="form-control border-start-0 border-end-0"
                            style="border-radius:0;"
                            placeholder="Repeat new password"
                            minlength="8"
                            autocomplete="new-password"
                            required
                        >
                        <button type="button" class="input-group-text bg-white border-start-0" style="border-radius:0 10px 10px 0;cursor:pointer;" onclick="togglePassword('reset-confirm', this)">
                            <i class="bi bi-eye text-muted" id="eye-reset-confirm"></i>
                        </button>
                    </div>
                </div>


                <div class="mb-3">
                    <div style="height:5px;border-radius:4px;background:#e5ebe7;overflow:hidden;">
                        <div id="strength-bar" style="height:100%;width:0%;border-radius:4px;transition:width .3s,background .3s;"></div>
                    </div>
                    <div id="strength-label" class="small text-muted mt-1"></div>
                </div>

                <!-- Match indicator -->
                <div id="match-msg" class="small mb-3"></div>

                <button id="reset-submit-btn" type="submit" class="btn-auth w-100 mt-1">
                    <span id="reset-btn-text">Reset Password <i class="bi bi-check2-circle ms-1"></i></span>
                    <span id="reset-btn-loading" class="d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>Saving…
                    </span>
                </button>

            </form>

            <div class="auth-links mt-3">
                <a href="index.php?page=forgot">Request a new link</a>
                &nbsp;·&nbsp;
                <a href="index.php?page=login">Back to login</a>
            </div>

        </div>
    </div>

</div>

<script>
function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}

// Password strength meter
const pwdInput = document.getElementById('reset-password');
const bar      = document.getElementById('strength-bar');
const label    = document.getElementById('strength-label');

pwdInput.addEventListener('input', function () {
    const val  = this.value;
    let score  = 0;
    if (val.length >= 8)                        score++;
    if (val.length >= 12)                       score++;
    if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
    if (/[0-9]/.test(val))                      score++;
    if (/[^A-Za-z0-9]/.test(val))               score++;

    const levels = [
        { pct: 0,   color: '#e5ebe7', text: '' },
        { pct: 20,  color: '#e74c3c', text: 'Very weak' },
        { pct: 40,  color: '#e67e22', text: 'Weak' },
        { pct: 60,  color: '#f1c40f', text: 'Fair' },
        { pct: 80,  color: '#27ae60', text: 'Strong' },
        { pct: 100, color: '#1b7f4f', text: 'Very strong' },
    ];
    const lvl = levels[score] || levels[0];
    bar.style.width      = lvl.pct + '%';
    bar.style.background = lvl.color;
    label.textContent    = lvl.text;
    label.style.color    = lvl.color;
    checkMatch();
});

// Password match checker
const confirmInput = document.getElementById('reset-confirm');
const matchMsg     = document.getElementById('match-msg');

function checkMatch() {
    if (!confirmInput.value) { matchMsg.textContent = ''; return; }
    if (pwdInput.value === confirmInput.value) {
        matchMsg.innerHTML = '<i class="bi bi-check-circle-fill text-success me-1"></i><span style="color:#1b7f4f;">Passwords match</span>';
    } else {
        matchMsg.innerHTML = '<i class="bi bi-x-circle-fill text-danger me-1"></i><span style="color:#e74c3c;">Passwords do not match</span>';
    }
}
confirmInput.addEventListener('input', checkMatch);

// Client-side validation before submit
document.getElementById('reset-form').addEventListener('submit', function (e) {
    if (pwdInput.value !== confirmInput.value) {
        e.preventDefault();
        matchMsg.innerHTML = '<i class="bi bi-x-circle-fill text-danger me-1"></i><span style="color:#e74c3c;">Passwords do not match</span>';
        confirmInput.focus();
        return;
    }
    document.getElementById('reset-btn-text').classList.add('d-none');
    document.getElementById('reset-btn-loading').classList.remove('d-none');
    document.getElementById('reset-submit-btn').disabled = true;
});
</script>