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

                    <div class="info-item">
                        <div class="info-label">
                            <span class="info-icon">🔐</span>
                            Role
                        </div>
                        <div class="info-value">
                            <?php 
                            $roleText = $user->role === 'administrator' ? 'Administrator' : 'Personnel';
                            $roleClass = $user->role === 'administrator' ? 'role-badge-admin' : 'role-badge-personnel';
                            ?>
                            <span class="<?= $roleClass ?>"><?= Html::encode($roleText) ?></span>
                        </div>
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

            <!-- Custom Initials Section -->
            <div class="initials-section">
                <h3 class="section-title">🔤 Custom Initials for Reports</h3>
                
                <div class="initials-info">
                    <p class="info-text">
                        Your initials are used in report filenames (Workplan and Accomplishment reports). 
                        Customize your initials if the auto-generated ones don't suit you.
                    </p>
                </div>

                <div class="initials-form">
                    <div class="form-group-inline">
                        <label for="custom-initials" class="initials-label">Your Initials:</label>
                        <input type="text" 
                               id="custom-initials" 
                               class="initials-input" 
                               value="<?= Html::encode($user->custom_initials ?? '') ?>" 
                               placeholder="e.g., JDC"
                               maxlength="10"
                               style="text-transform: uppercase;">
                        <button class="btn-save-initials" onclick="updateInitials()">
                            💾 Save Initials
                        </button>
                    </div>
                    <p class="initials-help-text">
                        Use uppercase letters and numbers only (max 10 characters). 
                        <?php if ($user->full_name): ?>
                            Auto-generated from "<?= Html::encode($user->full_name) ?>".
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <!-- Digital Signature Section -->
            <div class="signature-section">
                <h3 class="section-title">✍️ Digital Signature</h3>
                
                <?php
                // Prepare user positions mapping for JavaScript
                use kartik\select2\Select2;
                use yii\helpers\ArrayHelper;
                use common\models\User;
                
                $users = User::find()
                    ->where(['status' => User::STATUS_ACTIVE])
                    ->andWhere(['<>', 'id', $user->id])
                    ->orderBy(['full_name' => SORT_ASC])
                    ->all();
                
                $userList = ArrayHelper::map($users, 'id', 'full_name');
                $userPositions = ArrayHelper::map($users, 'id', 'position');
                ?>
                
                <script>
                // Make userPositions available globally for Select2 events
                var userPositions = <?= json_encode($userPositions) ?>;
                </script>
                
                <div class="signature-upload-area">
                    <label class="signature-label">Upload Signature (PNG/JPG, max 2MB)</label>
                    <?php if ($user->digital_signature): ?>
                        <div class="current-signature">
                            <img src="<?= Yii::getAlias('@web') . Html::encode($user->digital_signature) ?>" alt="Digital Signature" class="signature-preview">
                            <button class="btn-delete-signature" onclick="deleteSignature()">🗑️ Remove Signature</button>
                        </div>
                    <?php else: ?>
                        <p class="no-signature-text">No signature uploaded yet</p>
                    <?php endif; ?>
                    
                    <div class="upload-controls">
                        <input type="file" id="signatureFile" accept=".png,.jpg,.jpeg" style="display:none;" onchange="uploadSignature()">
                        <button class="btn-upload-signature" onclick="document.getElementById('signatureFile').click()">
                            📤 <?= $user->digital_signature ? 'Change Signature' : 'Upload Signature' ?>
                        </button>
                    </div>
                </div>

                <div class="signature-settings-form">
                    <h4 class="subsection-title">Approval Settings</h4>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Reviewers (Multiple Selection)</label>
                            <?php
                            echo Select2::widget([
                                'name' => 'reviewer_ids',
                                'value' => array_values($user->getReviewerIds()),
                                'data' => $userList,
                                'options' => [
                                    'placeholder' => 'Select reviewers...',
                                    'id' => 'reviewer-ids',
                                    'class' => 'form-control',
                                    'multiple' => true,
                                ],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                                'pluginEvents' => [
                                    'change' => 'function(e) { 
                                        var reviewerIds = $(this).val() || [];
                                        var designations = [];
                                        reviewerIds.forEach(function(id) {
                                            if (userPositions[id]) {
                                                designations.push(userPositions[id]);
                                            }
                                        });
                                        $("#reviewer-designations-display").val(designations.join(", "));
                                    }',
                                    'select2:clear' => 'function(e) { 
                                        $("#reviewer-designations-display").val("");
                                    }',
                                ],
                            ]);
                            ?>
                        </div>
                        
                        <div class="form-group">
                            <label>Reviewer Designations</label>
                            <input type="text" id="reviewer-designations-display" class="form-control" 
                                   value="<?= Html::encode(implode(', ', array_map(function($ur) { return $ur->reviewer_designation; }, $user->userReviewers))) ?>" 
                                   placeholder="Auto-populated from reviewers' positions"
                                   readonly>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Approver</label>
                            <?php
                            echo Select2::widget([
                                'name' => 'approver_id',
                                'value' => $user->approver_id,
                                'data' => $userList,
                                'options' => [
                                    'placeholder' => 'Select approver...',
                                    'id' => 'approver-id',
                                    'class' => 'form-control',
                                ],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                                'pluginEvents' => [
                                    'change' => 'function(e) { 
                                        var approverId = $(this).val();
                                        var designation = approverId && userPositions[approverId] ? userPositions[approverId] : "";
                                        $("#approver-designation").val(designation);
                                    }',
                                    'select2:clear' => 'function(e) { 
                                        $("#approver-designation").val("");
                                    }',
                                ],
                            ]);
                            ?>
                        </div>
                        
                        <div class="form-group">
                            <label>Approver Designation</label>
                            <input type="text" id="approver-designation" class="form-control" 
                                   value="<?= Html::encode($user->approver_designation ?? '') ?>" 
                                   placeholder="Auto-populated from approver's position"
                                   readonly>
                        </div>
                    </div>

                    <button class="btn-save-settings" onclick="saveSignatureSettings()">
                        💾 Save Approval Settings
                    </button>
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

.role-badge-admin {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: linear-gradient(135deg, #967259, #B8926A);
    color: white;
}

.role-badge-personnel {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: #E8D4BC;
    color: #3E2F1F;
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

/* Custom Initials Section */
.initials-section {
    margin: 20px 30px 30px;
    padding: 25px;
    background: white;
    border-radius: 10px;
    border: 2px solid #E8D4BC;
}

.initials-info {
    margin-bottom: 20px;
}

.info-text {
    color: #666;
    font-size: 14px;
    line-height: 1.6;
    margin: 0;
}

.initials-form {
    margin-top: 15px;
}

.form-group-inline {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 10px;
    flex-wrap: wrap;
}

.initials-label {
    font-size: 15px;
    font-weight: 600;
    color: #2D1F13;
    min-width: 120px;
}

.initials-input {
    padding: 10px 15px;
    border: 2px solid #D4A574;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    width: 150px;
    text-align: center;
    font-family: 'Courier New', monospace;
    letter-spacing: 2px;
}

.initials-input:focus {
    outline: none;
    border-color: #967259;
    box-shadow: 0 0 0 3px rgba(150, 114, 89, 0.1);
}

.btn-save-initials {
    padding: 10px 20px;
    background: linear-gradient(135deg, #967259, #B8926A);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.btn-save-initials:hover:not(:disabled) {
    background: linear-gradient(135deg, #B8926A, #D4A574);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(150, 114, 89, 0.3);
}

.btn-save-initials:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.initials-help-text {
    color: #6c757d;
    font-size: 13px;
    margin: 5px 0 0 0;
    font-style: italic;
}

.external-icon {
    font-size: 14px;
}

/* Signature Section */
.signature-section {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    margin-top: 20px;
}

.signature-upload-area {
    padding: 25px;
    border-bottom: 1px solid #e9ecef;
}

.signature-label {
    display: block;
    font-weight: 600;
    color: #2D1F13;
    margin-bottom: 15px;
    font-size: 14px;
}

.current-signature {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    text-align: center;
    margin-bottom: 15px;
}

.signature-preview {
    max-width: 300px;
    max-height: 150px;
    border: 2px solid #dee2e6;
    border-radius: 4px;
    background: white;
    padding: 10px;
}

.btn-delete-signature {
    margin-top: 15px;
    padding: 8px 16px;
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-delete-signature:hover {
    background: #c82333;
    transform: translateY(-1px);
}

.no-signature-text {
    text-align: center;
    color: #6c757d;
    padding: 20px;
    font-style: italic;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 15px;
}

.upload-controls {
    text-align: center;
}

.btn-upload-signature {
    padding: 12px 24px;
    background: linear-gradient(135deg, #967259 0%, #B8926A 100%);
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-upload-signature:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(150, 114, 89, 0.3);
}

.signature-settings-form {
    padding: 25px;
}

.subsection-title {
    font-size: 16px;
    font-weight: 600;
    color: #2D1F13;
    margin-bottom: 20px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 8px;
    font-size: 13px;
}

.form-control {
    padding: 10px 14px;
    border: 1px solid #ced4da;
    border-radius: 6px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: #967259;
    box-shadow: 0 0 0 3px rgba(150, 114, 89, 0.1);
}

.form-control:read-only {
    background-color: #e9ecef;
    cursor: not-allowed;
    opacity: 0.8;
}

.btn-save-settings {
    padding: 12px 30px;
    background: linear-gradient(135deg, #28a745 0%, #34ce57 100%);
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s ease;
    margin-top: 10px;
}

.btn-save-settings:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
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

    .form-row {
        grid-template-columns: 1fr;
    }

    .signature-preview {
        max-width: 100%;
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

function uploadSignature() {
    const fileInput = document.getElementById('signatureFile');
    const file = fileInput.files[0];
    
    if (!file) return;
    
    const formData = new FormData();
    formData.append('signature', file);
    
    // Show loading state
    const uploadBtn = document.querySelector('.btn-upload-signature');
    const originalText = uploadBtn.innerHTML;
    uploadBtn.disabled = true;
    uploadBtn.innerHTML = '⏳ Uploading...';
    
    fetch('<?= \yii\helpers\Url::to(['profile/upload-signature']) ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-Token': '<?= Yii::$app->request->csrfToken ?>'
        }
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showMessage('success', result.message);
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showMessage('danger', result.message);
            uploadBtn.disabled = false;
            uploadBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        showMessage('danger', 'An error occurred while uploading signature');
        uploadBtn.disabled = false;
        uploadBtn.innerHTML = originalText;
    });
}

function deleteSignature() {
    if (!confirm('Are you sure you want to delete your signature?')) {
        return;
    }
    
    fetch('<?= \yii\helpers\Url::to(['profile/delete-signature']) ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': '<?= Yii::$app->request->csrfToken ?>'
        }
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showMessage('success', result.message);
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showMessage('danger', result.message);
        }
    })
    .catch(error => {
        showMessage('danger', 'An error occurred while deleting signature');
    });
}

function updateInitials() {
    const initialsInput = document.getElementById('custom-initials');
    const customInitials = initialsInput.value.trim().toUpperCase();
    
    if (!customInitials) {
        showMessage('danger', 'Please enter your custom initials');
        return;
    }
    
    // Validate format
    if (!/^[A-Z0-9]+$/.test(customInitials)) {
        showMessage('danger', 'Custom initials must contain only uppercase letters and numbers');
        return;
    }
    
    if (customInitials.length > 10) {
        showMessage('danger', 'Custom initials must not exceed 10 characters');
        return;
    }
    
    const saveBtn = document.querySelector('.btn-save-initials');
    const originalText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '⏳ Saving...';
    
    const formData = new FormData();
    formData.append('custom_initials', customInitials);
    
    fetch('<?= \yii\helpers\Url::to(['profile/update-initials']) ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-Token': '<?= Yii::$app->request->csrfToken ?>'
        }
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showMessage('success', result.message);
            initialsInput.value = result.initials;
        } else {
            showMessage('danger', result.message);
        }
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalText;
    })
    .catch(error => {
        showMessage('danger', 'An error occurred while updating initials');
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalText;
    });
}

// Auto-convert initials to uppercase as user types
document.addEventListener('DOMContentLoaded', function() {
    const initialsInput = document.getElementById('custom-initials');
    if (initialsInput) {
        initialsInput.addEventListener('input', function(e) {
            this.value = this.value.toUpperCase();
        });
    }
});


// Populate designations on page load
$(document).ready(function() {
    const reviewerId = $('#reviewer-id').val();
    if (reviewerId && userPositions[reviewerId]) {
        $('#reviewer-designation').val(userPositions[reviewerId]);
    }
    
    const approverId = $('#approver-id').val();
    if (approverId && userPositions[approverId]) {
        $('#approver-designation').val(userPositions[approverId]);
    }
});

function saveSignatureSettings() {
    const reviewerIds = $('#reviewer-ids').val() || [];
    const approverId = document.getElementById('approver-id').value;
    const approverDesignation = document.getElementById('approver-designation').value;
    
    const formData = new FormData();
    
    // Append each reviewer ID as an array element
    reviewerIds.forEach(function(id) {
        formData.append('reviewer_ids[]', id);
    });
    
    formData.append('approver_id', approverId);
    formData.append('approver_designation', approverDesignation);
    
    const saveBtn = document.querySelector('.btn-save-settings');
    const originalText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '⏳ Saving...';
    
    fetch('<?= \yii\helpers\Url::to(['profile/update-signature-settings']) ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-Token': '<?= Yii::$app->request->csrfToken ?>'
        }
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showMessage('success', result.message);
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showMessage('danger', result.message || 'Failed to save settings');
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        showMessage('danger', 'An error occurred while saving settings');
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalText;
    });
}
</script>
