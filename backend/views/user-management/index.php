<?php

/** @var yii\web\View $this */
/** @var array $allPersonnel */
/** @var array $accessMap */
/** @var string $apiError */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'User Management';
$this->registerCssFile('@web/css/user-management.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
?>

<div class="user-management-page">
    <?php if ($apiError): ?>
        <div class="alert alert-warning">
            <strong>API Connection Issue:</strong> <?= Html::encode($apiError) ?>
            <br>
            <small>
                Troubleshoot connection issues: 
                <?= Html::a('Run Diagnostics Tool', ['user-management/test-connection'], ['target' => '_blank', 'style' => 'font-weight: 600; color: #967259;']) ?>
            </small>
        </div>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="page-header-section">
        <div class="header-content">
            <h1 class="page-heading">
                <span class="heading-icon">👥</span>
                User Access Management
            </h1>
            <p class="page-description">Manage EARS system access for PIDS personnel</p>
        </div>
        <div class="header-stats">
            <div class="stat-box">
                <div class="stat-value" id="totalUsers">-</div>
                <div class="stat-label">Total Personnel</div>
            </div>
            <div class="stat-box stat-success">
                <div class="stat-value" id="activeUsers">-</div>
                <div class="stat-label">Active Access</div>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="filter-section">
        <div class="search-box">
            <input type="text" id="searchInput" class="search-input" placeholder="Search by name, email, or department...">
            <span class="search-icon">🔍</span>
        </div>
        <div class="filter-controls">
            <select id="accessFilter" class="filter-select">
                <option value="all">All Personnel</option>
                <option value="granted">Access Granted</option>
                <option value="pending">No Access</option>
            </select>
            <select id="roleFilter" class="filter-select">
                <option value="all">All Roles</option>
                <option value="administrator">Administrator</option>
                <option value="personnel">Personnel</option>
            </select>
            <button class="btn-refresh" onclick="refreshPersonnel()">
                <span class="refresh-icon">🔄</span> Refresh
            </button>
        </div>
    </div>

    <!-- Personnel Table -->
    <div class="personnel-table-container">
        <table class="personnel-table" id="personnelTable">
            <thead>
                <tr>
                    <th width="60">Photo</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Division</th>
                    <th>Position</th>
                    <th width="120" class="text-center">Role</th>
                    <th width="150" class="text-center">Access Status</th>
                    <th width="120" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody id="personnelTableBody">
                <tr>
                    <td colspan="9" class="text-center loading-row">
                        <div class="loading-spinner"></div>
                        <p>Loading personnel from PIDS API...</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
let personnelData = [];

// Load personnel on page load
document.addEventListener('DOMContentLoaded', function() {
    loadPersonnel();
});

// Load personnel from API
function loadPersonnel() {
    console.log('Loading personnel from API...');
    fetch('<?= Url::to(['user-management/get-personnel']) ?>')
        .then(response => response.json())
        .then(result => {
            console.log('API Response:', result);
            if (result.success) {
                personnelData = result.data.sort((a, b) => {
                    const nameA = (a.full_name || '').toLowerCase();
                    const nameB = (b.full_name || '').toLowerCase();
                    return nameA.localeCompare(nameB);
                });
                console.log('Loaded ' + personnelData.length + ' personnel records');
                renderPersonnel(personnelData);
                updateStats();
            } else {
                console.error('API Error:', result.message);
                showError('Failed to load personnel data: ' + (result.message || 'Unknown error'));
                if (result.debug) {
                    console.log('Debug info:', result.debug);
                }
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            showError('Error connecting to API: ' + error.message);
        });
}

// Render personnel table
function renderPersonnel(data) {
    const tbody = document.getElementById('personnelTableBody');
    
    if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center">No personnel found</td></tr>';
        return;
    }

    tbody.innerHTML = data.map(person => {
        const hasAccess = person.has_access;
        const statusClass = hasAccess ? 'status-granted' : 'status-pending';
        const statusText = hasAccess ? 'Granted' : 'No Access';
        const role = person.role || 'personnel';
        const roleText = role === 'administrator' ? 'Administrator' : 'Personnel';
        const roleBadgeClass = role === 'administrator' ? 'role-admin' : 'role-personnel';
        
        // Disable revoke button for current user
        let actionBtn;
        if (hasAccess) {
            if (person.is_current_user) {
                actionBtn = `<button class="btn-action btn-revoke" disabled title="Cannot revoke your own account">Revoke</button>`;
            } else {
                actionBtn = `<button class="btn-action btn-revoke" onclick="revokeAccess(${person.id})">Revoke</button>`;
            }
        } else {
            actionBtn = `<button class="btn-action btn-grant" onclick="grantAccess(${person.id})">Grant Access</button>`;
        }

        const initial = person.full_name.charAt(0).toUpperCase();
        const profilePic = person.profile_picture 
            ? `<img src="${person.profile_picture}" alt="${person.full_name}" class="profile-pic" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"><div class="profile-initial" style="display:none;">${initial}</div>`
            : `<div class="profile-initial">${initial}</div>`;

        return `
            <tr data-pids-id="${person.id}" data-access="${hasAccess ? 'granted' : 'pending'}">
                <td>${profilePic}</td>
                <td><strong>${person.full_name}</strong></td>
                <td>${person.email}</td>
                <td>${person.department || '-'}</td>
                <td>${person.division || '-'}</td>
                <td>${person.position || '-'}</td>
                <td class="text-center">
                    ${hasAccess ? `<span class="role-badge ${roleBadgeClass}">${roleText}</span>` : '-'}
                </td>
                <td class="text-center">
                    <span class="status-badge ${statusClass}">${statusText}</span>
                </td>
                <td class="text-center">${actionBtn}</td>
            </tr>
        `;
    }).join('');
}

// Grant access with role selection
function grantAccess(pidsId) {
    // Create modal for role selection
    const modalHtml = `
        <div class="modal-overlay" id="roleModal" onclick="closeRoleModal(event)">
            <div class="modal-content" onclick="event.stopPropagation()">
                <h3>Grant Access - Select Role</h3>
                <p>Choose the role for this user:</p>
                <div class="role-options">
                    <label class="role-option">
                        <input type="radio" name="role" value="personnel" checked>
                        <span class="role-label">
                            <strong>Personnel</strong>
                            <small>Regular user access</small>
                        </span>
                    </label>
                    <label class="role-option">
                        <input type="radio" name="role" value="administrator">
                        <span class="role-label">
                            <strong>Administrator</strong>
                            <small>Full access including Settings</small>
                        </span>
                    </label>
                </div>
                <div class="modal-actions">
                    <button class="btn-cancel" onclick="closeRoleModal()">Cancel</button>
                    <button class="btn-confirm" onclick="confirmGrantAccess(${pidsId})">Grant Access</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

function closeRoleModal(event) {
    const modal = document.getElementById('roleModal');
    if (modal) {
        modal.remove();
    }
}

function confirmGrantAccess(pidsId) {
    const selectedRole = document.querySelector('input[name="role"]:checked').value;
    closeRoleModal();
    
    fetch('<?= Url::to(['user-management/grant-access']) ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': '<?= Yii::$app->request->csrfToken ?>'
        },
        body: 'pids_id=' + pidsId + '&role=' + selectedRole
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showSuccess(result.message);
            refreshPersonnel();
        } else {
            showError(result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Failed to grant access');
    });
}

// Revoke access
function revokeAccess(pidsId) {
    if (!confirm('Revoke EARS access from this user?')) return;

    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = 'Processing...';

    fetch('<?= Url::to(['user-management/revoke-access']) ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': '<?= Yii::$app->request->csrfToken ?>'
        },
        body: 'pids_id=' + pidsId
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showSuccess(result.message);
            refreshPersonnel();
        } else {
            showError(result.message);
            btn.disabled = false;
            btn.innerHTML = 'Revoke';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Failed to revoke access');
        btn.disabled = false;
        btn.innerHTML = 'Revoke';
    });
}

// Refresh personnel list
function refreshPersonnel() {
    document.getElementById('personnelTableBody').innerHTML = '<tr><td colspan="9" class="text-center loading-row"><div class="loading-spinner"></div><p>Refreshing...</p></td></tr>';
    loadPersonnel();
}

// Update statistics
function updateStats() {
    const total = personnelData.length;
    const active = personnelData.filter(p => p.has_access).length;
    
    document.getElementById('totalUsers').textContent = total;
    document.getElementById('activeUsers').textContent = active;
}

// Sort personnel alphabetically by full name
function sortPersonnelAlphabetically(data) {
    return data.sort((a, b) => {
        const nameA = (a.full_name || '').toLowerCase();
        const nameB = (b.full_name || '').toLowerCase();
        return nameA.localeCompare(nameB);
    });
}

// Search functionality
document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const filtered = personnelData.filter(person => 
        person.full_name.toLowerCase().includes(searchTerm) ||
        person.email.toLowerCase().includes(searchTerm) ||
        (person.department && person.department.toLowerCase().includes(searchTerm))
    );
    applyFilters(filtered);
});

// Access filter functionality
document.getElementById('accessFilter').addEventListener('change', function(e) {
    applyFilters();
});

// Role filter functionality
document.getElementById('roleFilter').addEventListener('change', function(e) {
    applyFilters();
});

// Apply all filters
function applyFilters(data = null) {
    let filtered = data || personnelData;
    
    // Apply access filter
    const accessFilter = document.getElementById('accessFilter').value;
    if (accessFilter === 'granted') {
        filtered = filtered.filter(p => p.has_access === true);
    } else if (accessFilter === 'pending') {
        filtered = filtered.filter(p => p.has_access === false);
    }
    
    // Apply role filter
    const roleFilter = document.getElementById('roleFilter').value;
    if (roleFilter !== 'all') {
        filtered = filtered.filter(p => p.role === roleFilter);
    }
    
    renderPersonnel(sortPersonnelAlphabetically(filtered));
}

// Show success message
function showSuccess(message) {
    // You can integrate with your existing alert system
    alert(message);
}

// Show error message
function showError(message) {
    // You can integrate with your existing alert system
    alert(message);
}
</script>
