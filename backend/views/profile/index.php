<?php

use yii\helpers\Html;

$this->title = 'My Profile';
$user = Yii::$app->user->identity;

// Get user data
$profilePic = $user->profile_picture ?? null;
$fullName = $user->full_name ?? $user->username;
$username = $user->username ?? '';
$email = $user->email ?? '';
$position = $user->position ?? '';
$department = $user->department ?? '';
$division = $user->division ?? '';
$initial = strtoupper(substr($fullName, 0, 1));
?>

<div class="profile-page">
    <div class="profile-header">
        <h1><span class="icon">👤</span> My Profile</h1>
        <p class="subtitle">View your account information</p>
        <button class="btn-sync-profile" id="btnSyncProfile" onclick="syncProfile()">
            <span class="sync-icon">🔄</span> Sync from DTS
        </button>
    </div>

    <div class="profile-container">
        <!-- Profile Card -->
        <div class="profile-card">
            <div class="profile-avatar-section">
                <?php if ($profilePic): ?>
                    <img src="<?= Html::encode($profilePic) ?>" alt="Profile Picture" class="profile-avatar-large" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="profile-avatar-large profile-avatar-placeholder" style="display:none;">
                        <?= $initial ?>
                    </div>
                <?php else: ?>
                    <div class="profile-avatar-large profile-avatar-placeholder">
                        <?= $initial ?>
                    </div>
                <?php endif; ?>
                <h2 class="profile-fullname"><?= Html::encode($fullName) ?></h2>
                <?php if ($position): ?>
                    <p class="profile-position-badge"><?= Html::encode($position) ?></p>
                <?php endif; ?>
            </div>

            <div class="profile-info-section">
                <h3 class="section-title">Personal Information</h3>
                
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">
                            <span class="info-icon">📧</span>
                            Email Address
                        </div>
                        <div class="info-value"><?= Html::encode($email) ?></div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <span class="info-icon">👤</span>
                            Username
                        </div>
                        <div class="info-value"><?= Html::encode($username) ?></div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <span class="info-icon">💼</span>
                            Position
                        </div>
                        <div class="info-value"><?= Html::encode($position ?: 'Not set') ?></div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <span class="info-icon">🏢</span>
                            Department
                        </div>
                        <div class="info-value"><?= Html::encode($department ?: 'Not set') ?></div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <span class="info-icon">🏛️</span>
                            Division
                        </div>
                        <div class="info-value"><?= Html::encode($division ?: 'Not set') ?></div>
                    </div>
                </div>
            </div>

            <!-- Important Notice -->
            <div class="profile-notice">
                <div class="notice-icon">ℹ️</div>
                <div class="notice-content">
                    <h4>Account Management Notice</h4>
                    <p>To update your credentials, email address, or password, please visit the <strong>PIDS Document Tracking System (DTS)</strong>.</p>
                    <p>All changes to your account information must be made through DTS and will automatically sync with EARS.</p>
                    <a href="https://dts.pids.gov.ph" target="_blank" class="dts-link">
                        Open PIDS DTS <span class="external-icon">↗</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.profile-page {
    padding: 30px;
    max-width: 1000px;
    margin: 0 auto;
}

.profile-header {
    margin-bottom: 30px;
}

.profile-header h1 {
    font-size: 32px;
    color: #2D1F13;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.profile-header .icon {
    font-size: 36px;
}

.profile-header .subtitle {
    color: #6c757d;
    font-size: 16px;
    margin: 0;
}

.btn-sync-profile {
    margin-top: 15px;
    padding: 10px 20px;
    background: linear-gradient(135deg, #967259, #B8926A);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(150, 114, 89, 0.3);
}

.btn-sync-profile:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(150, 114, 89, 0.4);
}

.btn-sync-profile:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.btn-sync-profile .sync-icon {
    display: inline-block;
    transition: transform 0.6s ease;
}

.btn-sync-profile.syncing .sync-icon {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.profile-container {
    display: flex;
    gap: 30px;
}

.profile-card {
    flex: 1;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow: hidden;
}

.profile-avatar-section {
    background: linear-gradient(135deg, #967259 0%, #3E2F1F 100%);
    padding: 40px 30px;
    text-align: center;
    color: white;
}

.profile-avatar-large {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid white;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    margin-bottom: 20px;
}

.profile-avatar-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.2);
    font-size: 48px;
    font-weight: bold;
    margin: 0 auto 20px;
}

.profile-fullname {
    font-size: 28px;
    font-weight: 600;
    margin: 0 0 10px 0;
    color: white;
}

.profile-position-badge {
    display: inline-block;
    background: rgba(255,255,255,0.2);
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 500;
    margin: 0;
}

.profile-info-section {
    padding: 30px;
}

.section-title {
    font-size: 20px;
    color: #2D1F13;
    margin-bottom: 20px;
    font-weight: 600;
    padding-bottom: 10px;
    border-bottom: 2px solid #E8D4BC;
}

.info-grid {
    display: grid;
    gap: 20px;
}

.info-item {
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #967259;
}

.info-label {
    font-size: 13px;
    color: #6c757d;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.info-icon {
    font-size: 16px;
}

.info-value {
    font-size: 16px;
    color: #2D1F13;
    font-weight: 500;
}

.profile-notice {
    margin: 20px 30px 30px;
    padding: 20px;
    background: #FFF9E6;
    border: 2px solid #FFE8A3;
    border-radius: 10px;
    display: flex;
    gap: 15px;
}

.notice-icon {
    font-size: 32px;
    flex-shrink: 0;
}

.notice-content h4 {
    margin: 0 0 10px 0;
    color: #856404;
    font-size: 16px;
    font-weight: 600;
}

.notice-content p {
    margin: 0 0 10px 0;
    color: #856404;
    font-size: 14px;
    line-height: 1.6;
}

.dts-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #967259;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    margin-top: 10px;
    padding: 8px 16px;
    background: white;
    border-radius: 6px;
    border: 2px solid #967259;
    transition: all 0.3s ease;
}

.dts-link:hover {
    background: #967259;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(150, 114, 89, 0.3);
}

.external-icon {
    font-size: 12px;
}

@media (max-width: 768px) {
    .profile-page {
        padding: 20px 15px;
    }

    .profile-header h1 {
        font-size: 24px;
    }

    .profile-avatar-large {
        width: 100px;
        height: 100px;
    }

    .profile-fullname {
        font-size: 22px;
    }

    .profile-info-section {
        padding: 20px;
    }

    .profile-notice {
        margin: 15px 20px 20px;
        flex-direction: column;
        text-align: center;
    }
}
</style>

<script>
function syncProfile() {
    const btn = document.getElementById('btnSyncProfile');
    const originalText = btn.innerHTML;
    
    // Disable button and show loading state
    btn.disabled = true;
    btn.classList.add('syncing');
    btn.innerHTML = '<span class="sync-icon">🔄</span> Syncing...';

    fetch('<?= \yii\helpers\Url::to(['profile/sync']) ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': '<?= Yii::$app->request->csrfToken ?>'
        }
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            // Show success message
            showMessage('success', result.message);
            
            // Update displayed data
            if (result.data) {
                // Update profile picture
                const profileImg = document.querySelector('.profile-avatar-large');
                if (result.data.profile_picture && profileImg && profileImg.tagName === 'IMG') {
                    profileImg.src = result.data.profile_picture;
                }
                
                // Update text fields
                document.querySelectorAll('.info-value').forEach((el, index) => {
                    const fields = ['email', 'username', 'position', 'department', 'division'];
                    if (fields[index] && result.data[fields[index]]) {
                        el.textContent = result.data[fields[index]];
                    }
                });
                
                // Update full name
                const fullNameEl = document.querySelector('.profile-fullname');
                if (fullNameEl && result.data.full_name) {
                    fullNameEl.textContent = result.data.full_name;
                }
                
                // Update position badge
                const positionBadge = document.querySelector('.profile-position-badge');
                if (positionBadge && result.data.position) {
                    positionBadge.textContent = result.data.position;
                }
                
                // Reload page after 2 seconds to refresh navbar
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            }
        } else {
            showMessage('error', result.message || 'Failed to sync profile');
        }
    })
    .catch(error => {
        console.error('Sync error:', error);
        showMessage('error', 'An error occurred while syncing profile');
    })
    .finally(() => {
        // Re-enable button
        btn.disabled = false;
        btn.classList.remove('syncing');
        btn.innerHTML = originalText;
    });
}

function showMessage(type, message) {
    // Create message element
    const msgDiv = document.createElement('div');
    msgDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
    msgDiv.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 12px rgba(0,0,0,0.2);';
    msgDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    document.body.appendChild(msgDiv);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        msgDiv.remove();
    }, 5000);
}
</script>
