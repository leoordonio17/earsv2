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
            <small>Check the API configuration in backend/config/main.php and test at: <a href="<?= Url::to(['/debug/test-api']) ?>" target="_blank">/debug/test-api</a></small>
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
                <option value="revoked">Access Revoked</option>
                <option value="pending">No Access</option>
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
                    <th width="150" class="text-center">Access Status</th>
                    <th width="120" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody id="personnelTableBody">
                <tr>
                    <td colspan="8" class="text-center loading-row">
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
        tbody.innerHTML = '<tr><td colspan="8" class="text-center">No personnel found</td></tr>';
        return;
    }

    tbody.innerHTML = data.map(person => {
        const hasAccess = person.has_access;
        const statusClass = hasAccess ? 'status-granted' : 'status-pending';
        const statusText = hasAccess ? 'Granted' : 'No Access';
        const actionBtn = hasAccess 
            ? `<button class="btn-action btn-revoke" onclick="revokeAccess(${person.id})">Revoke</button>`
            : `<button class="btn-action btn-grant" onclick="grantAccess(${person.id})">Grant Access</button>`;

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
                    <span class="status-badge ${statusClass}">${statusText}</span>
                </td>
                <td class="text-center">${actionBtn}</td>
            </tr>
        `;
    }).join('');
}

// Grant access
function grantAccess(pidsId) {
    if (!confirm('Grant EARS access to this user?')) return;

    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = 'Processing...';

    fetch('<?= Url::to(['user-management/grant-access']) ?>', {
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
            btn.innerHTML = 'Grant Access';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Failed to grant access');
        btn.disabled = false;
        btn.innerHTML = 'Grant Access';
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
    document.getElementById('personnelTableBody').innerHTML = '<tr><td colspan="7" class="text-center loading-row"><div class="loading-spinner"></div><p>Refreshing...</p></td></tr>';
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
    renderPersonnel(sortPersonnelAlphabetically(filtered));
});

// Filter functionality
document.getElementById('accessFilter').addEventListener('change', function(e) {
    const filter = e.target.value;
    let filtered = personnelData;
    
    if (filter === 'granted') {
        filtered = personnelData.filter(p => p.has_access === true);
    } else if (filter === 'revoked' || filter === 'pending') {
        filtered = personnelData.filter(p => p.has_access === false);
    }
    
    renderPersonnel(sortPersonnelAlphabetically(filtered));
});

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
