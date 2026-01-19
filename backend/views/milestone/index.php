<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\LinkPager;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Milestone Management';
?>
<style>
    .milestone-container {
        padding: 30px;
        background: linear-gradient(135deg, #f8f5f1 0%, #fff 100%);
        min-height: 100vh;
    }

    .milestone-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding: 25px;
        background: linear-gradient(135deg, #2D1F13 0%, #3E2F1F 100%);
        border-radius: 15px;
        box-shadow: 0 8px 20px rgba(45, 31, 19, 0.3);
    }

    .milestone-header h1 {
        color: #E8D4BC;
        font-size: 28px;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .btn-create {
        padding: 12px 28px;
        background: linear-gradient(135deg, #967259 0%, #B8926A 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(150, 114, 89, 0.3);
    }

    .btn-create:hover {
        background: linear-gradient(135deg, #B8926A 0%, #D4A574 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(150, 114, 89, 0.4);
        color: white;
        text-decoration: none;
    }

    .milestone-table-container {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .milestone-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .milestone-table thead th {
        background: linear-gradient(135deg, #3E2F1F 0%, #2D1F13 100%);
        color: #E8D4BC;
        padding: 16px;
        text-align: left;
        font-weight: 600;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: none;
    }

    .milestone-table thead th:first-child {
        border-radius: 10px 0 0 0;
    }

    .milestone-table thead th:last-child {
        border-radius: 0 10px 0 0;
    }

    .milestone-table tbody tr {
        transition: all 0.3s ease;
    }

    .milestone-table tbody tr:hover {
        background: #f8f5f1;
        transform: scale(1.01);
    }

    .milestone-table tbody td {
        padding: 16px;
        border-bottom: 1px solid #E8D4BC;
        color: #2D1F13;
        font-size: 14px;
    }

    .milestone-table tbody tr:last-child td {
        border-bottom: none;
    }

    .milestone-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 500;
        color: white;
    }

    .color-preview {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 2px solid white;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    }

    .active-badge {
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }

    .active-badge.active {
        background: #d4edda;
        color: #155724;
    }

    .active-badge.inactive {
        background: #f8d7da;
        color: #721c24;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
    }

    .btn-action {
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-view {
        background: #3498db;
        color: white;
    }

    .btn-view:hover {
        background: #2980b9;
        transform: translateY(-2px);
        color: white;
    }

    .btn-edit {
        background: #967259;
        color: white;
    }

    .btn-edit:hover {
        background: #B8926A;
        transform: translateY(-2px);
        color: white;
    }

    .btn-delete {
        background: #e74c3c;
        color: white;
    }

    .btn-delete:hover {
        background: #c0392b;
        transform: translateY(-2px);
        color: white;
    }

    /* Toggle Switch Styles */
    .toggle-switch {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
    }

    .toggle-switch input[type="checkbox"] {
        display: none;
    }

    .toggle-slider {
        width: 50px;
        height: 24px;
        background: #ccc;
        border-radius: 24px;
        position: relative;
        transition: background 0.3s ease;
    }

    .toggle-slider::before {
        content: '';
        position: absolute;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: white;
        top: 2px;
        left: 2px;
        transition: transform 0.3s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .toggle-switch input:checked + .toggle-slider {
        background: #27ae60;
    }

    .toggle-switch input:checked + .toggle-slider::before {
        transform: translateX(26px);
    }

    .toggle-label {
        font-size: 13px;
        font-weight: 500;
        color: #2D1F13;
        min-width: 60px;
    }

    .toggle-switch:hover .toggle-slider {
        opacity: 0.9;
    }

    .pagination-container {
        margin-top: 25px;
        display: flex;
        justify-content: center;
    }

    .pagination {
        display: flex;
        gap: 8px;
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .pagination li {
        margin: 0;
    }

    .pagination a,
    .pagination .active a {
        padding: 10px 16px;
        background: white;
        color: #2D1F13;
        border: 2px solid #D4A574;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
        display: inline-block;
    }

    .pagination a:hover {
        background: #967259;
        color: white;
        border-color: #967259;
        transform: translateY(-2px);
    }

    .pagination .active a {
        background: linear-gradient(135deg, #2D1F13 0%, #3E2F1F 100%);
        color: #E8D4BC;
        border-color: #2D1F13;
    }

    .pagination .disabled a {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .pagination .disabled a:hover {
        background: white;
        color: #2D1F13;
        transform: none;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #967259;
    }

    .empty-state-icon {
        font-size: 64px;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    .empty-state-text {
        font-size: 18px;
        font-weight: 500;
        margin-bottom: 10px;
    }

    .empty-state-subtext {
        font-size: 14px;
        color: #B8926A;
    }

    .filter-section {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .search-box {
        position: relative;
    }

    .search-input:focus {
        outline: none;
        border-color: #967259;
        box-shadow: 0 0 0 3px rgba(150, 114, 89, 0.1);
    }

    .filter-select:focus {
        outline: none;
        border-color: #967259;
        box-shadow: 0 0 0 3px rgba(150, 114, 89, 0.1);
    }

    .btn-refresh:hover {
        background: #967259;
        color: white;
        border-color: #967259;
    }

    .hidden-row {
        display: none !important;
    }
</style>

<script>
let allRows = [];

document.addEventListener('DOMContentLoaded', function() {
    // Store all table rows
    const tbody = document.querySelector('.milestone-table tbody');
    if (tbody) {
        allRows = Array.from(tbody.querySelectorAll('tr'));
    }

    // Search functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }

    // Milestone filter functionality
    const milestoneFilter = document.getElementById('milestoneFilter');
    if (milestoneFilter) {
        milestoneFilter.addEventListener('change', applyFilters);
    }
});

function applyFilters() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const milestoneFilter = document.getElementById('milestoneFilter').value;
    const container = document.querySelector('.milestone-table-container');
    
    // If both filters are empty/default, show all rows
    if (searchTerm === '' && milestoneFilter === 'all') {
        allRows.forEach(row => row.classList.remove('hidden-row'));
        restoreTableIfNeeded();
        return;
    }

    allRows.forEach(row => {
        const milestoneName = row.cells[1]?.textContent.toLowerCase() || '';
        const description = row.cells[2]?.textContent.toLowerCase() || '';
        const milestoneCell = row.cells[5];
        const milestoneValue = milestoneCell?.getAttribute('data-milestone') || '';

        // Check search term (if empty, matches all)
        const matchesSearch = searchTerm === '' || milestoneName.includes(searchTerm) || description.includes(searchTerm);

        // Check milestone filter
        let matchesMilestone = true;
        if (milestoneFilter === 'active') {
            matchesMilestone = milestoneValue === 'active';
        } else if (milestoneFilter === 'inactive') {
            matchesMilestone = milestoneValue === 'inactive';
        }

        // Show/hide row
        if (matchesSearch && matchesMilestone) {
            row.classList.remove('hidden-row');
        } else {
            row.classList.add('hidden-row');
        }
    });

    // Check if we need to show empty state or restore table
    checkEmptyState();
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('milestoneFilter').value = 'all';
    applyFilters();
}

function restoreTableIfNeeded() {
    const container = document.querySelector('.milestone-table-container');
    const existingTable = container.querySelector('.milestone-table');
    
    if (!existingTable && allRows.length > 0) {
        // Restore the table from saved data
        location.reload();
    }
}

function toggleMilestone(id, isActive) {
    if (!confirm('Are you sure you want to ' + (isActive ? 'activate' : 'deactivate') + ' this milestone?')) {
        // Reset checkbox if user cancels
        event.target.checked = !isActive;
        return;
    }
    
    // Create form and submit
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= \yii\helpers\Url::to(["toggle"]) ?>?id=' + id;
    
    // Add CSRF token
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '<?= Yii::$app->request->csrfParam ?>';
    csrfToken.value = '<?= Yii::$app->request->csrfToken ?>';
    form.appendChild(csrfToken);
    
    document.body.appendChild(form);
    form.submit();
}

function checkEmptyState() {
    const visibleRows = allRows.filter(row => !row.classList.contains('hidden-row'));
    const container = document.querySelector('.milestone-table-container');
    const tableContainer = container.querySelector('.milestone-table');
    
    if (visibleRows.length === 0 && tableContainer) {
        const tbody = tableContainer.querySelector('tbody');
        const originalHTML = tbody.innerHTML;
        
        // Store original table HTML before replacing
        if (!container.dataset.originalTable) {
            container.dataset.originalTable = container.innerHTML;
        }
        
        container.innerHTML = '<div class="empty-state"><div class="empty-state-icon">🔍</div><div class="empty-state-text">No matching results found</div><div class="empty-state-subtext">Try adjusting your search or filters</div></div>';
    } else if (visibleRows.length > 0 && !tableContainer) {
        // Restore original table
        if (container.dataset.originalTable) {
            container.innerHTML = container.dataset.originalTable;
            // Reinitialize allRows after restoration
            const tbody = container.querySelector('.milestone-table tbody');
            if (tbody) {
                allRows = Array.from(tbody.querySelectorAll('tr'));
            }
        }
    }
}
</script>

<div class="milestone-container">
    <div class="milestone-header">
        <h1>
            <span>📚</span>
            Milestone Library
        </h1>
        <?= Html::a('<span>➕</span> Create Milestone', ['create'], ['class' => 'btn-create']) ?>
    </div>

    <!-- Search and Filter Section -->
    <div class="filter-section" style="margin-bottom: 20px;">
        <div class="search-box" style="max-width: 500px;">
            <input type="text" id="searchInput" class="search-input" placeholder="Search by milestone name or description..." style="width: 100%; padding: 12px 16px; border: 2px solid #D4A574; border-radius: 8px; font-size: 14px;">
            <span class="search-icon" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); color: #967259;">🔍</span>
        </div>
        <div class="filter-controls" style="display: flex; gap: 12px; margin-top: 12px;">
            <select id="milestoneFilter" class="filter-select" style="padding: 10px 16px; border: 2px solid #D4A574; border-radius: 8px; font-size: 14px; color: #2D1F13; background: white; cursor: pointer;">
                <option value="all">All Milestones</option>
                <option value="active">Active Only</option>
                <option value="inactive">Inactive Only</option>
            </select>
            <button class="btn-refresh" onclick="resetFilters()" style="padding: 10px 20px; background: white; border: 2px solid #967259; border-radius: 8px; color: #967259; cursor: pointer; font-weight: 500; transition: all 0.3s ease;">
                <span>🔄</span> Reset
            </button>
        </div>
    </div>

    <div class="milestone-table-container">
        <?php if ($dataProvider->getTotalCount() > 0): ?>
            <table class="milestone-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th>Milestone Name</th>
                        <th>Description</th>
                        <th style="width: 100px;">Color</th>
                        <th style="width: 100px;">Sort Order</th>
                        <th style="width: 140px;">Status</th>
                        <th style="width: 220px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dataProvider->getModels() as $model): ?>
                        <tr>
                            <td><?= Html::encode($model->id) ?></td>
                            <td>
                                <div class="milestone-badge" style="background-color: <?= Html::encode($model->color) ?>;">
                                    <?= Html::encode($model->name) ?>
                                </div>
                            </td>
                            <td><?= Html::encode($model->description ?: 'No description') ?></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div class="color-preview" style="background-color: <?= Html::encode($model->color) ?>;"></div>
                                    <span style="font-family: monospace; font-size: 12px;"><?= Html::encode($model->color) ?></span>
                                </div>
                            </td>
                            <td style="text-align: center;"><?= Html::encode($model->sort_order) ?></td>
                            <td data-milestone="<?= $model->is_active ? 'active' : 'inactive' ?>">
                                <label class="toggle-switch" data-id="<?= $model->id ?>" title="Click to toggle milestone">
                                    <input type="checkbox" <?= $model->is_active ? 'checked' : '' ?> onchange="toggleMilestone(<?= $model->id ?>, this.checked)">
                                    <span class="toggle-slider"></span>
                                    <span class="toggle-label"><?= $model->is_active ? 'Active' : 'Inactive' ?></span>
                                </label>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <?= Html::a('<span>👁</span> View', ['view', 'id' => $model->id], ['class' => 'btn-action btn-view']) ?>
                                    <?= Html::a('<span>✏️</span> Edit', ['update', 'id' => $model->id], ['class' => 'btn-action btn-edit']) ?>
                                    <?= Html::a('<span>🗑</span> Delete', ['delete', 'id' => $model->id], [
                                        'class' => 'btn-action btn-delete',
                                        'data' => [
                                            'confirm' => 'Are you sure you want to delete this milestone?',
                                            'method' => 'post',
                                        ],
                                    ]) ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="pagination-container">
                <?= LinkPager::widget([
                    'pagination' => $dataProvider->pagination,
                    'options' => ['class' => 'pagination'],
                    'linkOptions' => ['class' => 'page-link'],
                    'activePageCssClass' => 'active',
                    'disabledPageCssClass' => 'disabled',
                    'prevPageLabel' => '← Previous',
                    'nextPageLabel' => 'Next →',
                    'maxButtonCount' => 5,
                ]) ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">📋</div>
                <div class="empty-state-text">No milestone records found</div>
                <div class="empty-state-subtext">Click "Create Milestone" to add your first milestone</div>
            </div>
        <?php endif; ?>
    </div>
</div>
