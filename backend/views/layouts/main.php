<?php

/** @var \yii\web\View $this */
/** @var string $content */

use backend\assets\AppAsset;
use common\widgets\Alert;
use yii\bootstrap5\Html;

AppAsset::register($this);

// Register dashboard CSS for authenticated users
if (!Yii::$app->user->isGuest) {
    $this->registerCssFile('@web/css/dashboard.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
}
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?> - EARS</title>
    <link rel="icon" type="image/x-icon" href="<?= Yii::$app->request->baseUrl ?>/favicon.ico">
    <?php $this->head() ?>
</head>
<body class="<?= Yii::$app->user->isGuest ? 'guest-body' : 'dashboard-body' ?>">
<?php $this->beginBody() ?>

<?php if (!Yii::$app->user->isGuest): ?>
    <!-- Dashboard Layout for Authenticated Users -->
    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo-section">
                    <div class="logo-wrapper">
                        <?= Html::img('@web/images/PIDS_logo.png', ['alt' => 'PIDS Logo', 'class' => 'sidebar-logo']) ?>
                    </div>
                    <div class="sidebar-title-wrapper">
                        <h3 class="sidebar-title">Electronic Accomplishment</h3>
                        <h3 class="sidebar-title">Reporting System</h3>
                        <p class="sidebar-subtitle">(EARS)</p>
                    </div>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <ul class="nav-list">
                    <li class="nav-item <?= Yii::$app->controller->id === 'site' && Yii::$app->controller->action->id === 'index' ? 'active' : '' ?>">
                        <?= Html::a('<span class="nav-icon">🏠</span><span class="nav-text">Dashboard</span>', ['/site/index'], ['class' => 'nav-link']) ?>
                    </li>

                    <!-- Tasks Menu -->
                    <li class="nav-item nav-item-expandable <?= in_array(Yii::$app->controller->id, ['workplan', 'accomplishment']) ? 'expanded' : '' ?>">
                        <a href="#" class="nav-link" onclick="toggleTasksMenu(event)">
                            <span class="nav-icon">📋</span>
                            <span class="nav-text">Tasks</span>
                            <span class="nav-arrow">›</span>
                        </a>
                        <ul class="submenu" id="tasksSubmenu">
                            <li class="submenu-item <?= Yii::$app->controller->id === 'workplan' ? 'active' : '' ?>">
                                <?= Html::a('<span class="submenu-icon">📝</span><span class="submenu-text">Workplan</span>', ['/workplan/index'], ['class' => 'submenu-link']) ?>
                            </li>
                            <li class="submenu-item <?= Yii::$app->controller->id === 'accomplishment' ? 'active' : '' ?>">
                                <?= Html::a('<span class="submenu-icon">✅</span><span class="submenu-text">Accomplishment</span>', ['/accomplishment/index'], ['class' => 'submenu-link']) ?>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item <?= Yii::$app->controller->id === 'progress-report' ? 'active' : '' ?>">
                        <?= Html::a('<span class="nav-icon">📈</span><span class="nav-text">Progress Report</span>', ['/progress-report/index'], ['class' => 'nav-link']) ?>
                    </li>
                    <li class="nav-item <?= Yii::$app->controller->id === 'analytics' ? 'active' : '' ?>">
                        <?= Html::a('<span class="nav-icon">📊</span><span class="nav-text">Analytics</span>', ['/analytics/index'], ['class' => 'nav-link']) ?>
                    </li>
                    <li class="nav-item">
                        <?= Html::a('<span class="nav-icon">📝</span><span class="nav-text">Reports</span>', '#', ['class' => 'nav-link']) ?>
                    </li>
                    
                    <!-- Settings Submenu (Administrator Only) -->
                    <?php if (Yii::$app->user->identity->role === \common\models\User::ROLE_ADMINISTRATOR): ?>
                    <li class="nav-item nav-item-expandable">
                        <a href="#" class="nav-link" onclick="toggleSettingsMenu(event)">
                            <span class="nav-icon">⚙️</span>
                            <span class="nav-text">Settings</span>
                            <span class="nav-arrow">›</span>
                        </a>
                        <ul class="submenu" id="settingsSubmenu">
                            <li class="submenu-item <?= Yii::$app->controller->id === 'user-management' ? 'active' : '' ?>">
                                <?= Html::a('<span class="submenu-icon">👥</span><span class="submenu-text">User Management</span>', ['/user-management/index'], ['class' => 'submenu-link']) ?>
                            </li>
                            <li class="submenu-item <?= Yii::$app->controller->id === 'status' ? 'active' : '' ?>">
                                <?= Html::a('<span class="submenu-icon">📋</span><span class="submenu-text">Status</span>', ['/status/index'], ['class' => 'submenu-link']) ?>
                            </li>
                            <li class="submenu-item <?= Yii::$app->controller->id === 'task-type' ? 'active' : '' ?>">
                                <?= Html::a('<span class="submenu-icon">📝</span><span class="submenu-text">Task Type</span>', ['/task-type/index'], ['class' => 'submenu-link']) ?>
                            </li>
                            <li class="submenu-item <?= Yii::$app->controller->id === 'task-category' ? 'active' : '' ?>">
                                <?= Html::a('<span class="submenu-icon">🗂️</span><span class="submenu-text">Task Category</span>', ['/task-category/index'], ['class' => 'submenu-link']) ?>
                            </li>
                            <li class="submenu-item <?= Yii::$app->controller->id === 'project-stage' ? 'active' : '' ?>">
                                <?= Html::a('<span class="submenu-icon">🎯</span><span class="submenu-text">Project Stage</span>', ['/project-stage/index'], ['class' => 'submenu-link']) ?>
                            </li>
                            <li class="submenu-item <?= Yii::$app->controller->id === 'milestone' ? 'active' : '' ?>">
                                <?= Html::a('<span class="submenu-icon">🏆</span><span class="submenu-text">Milestone</span>', ['/milestone/index'], ['class' => 'submenu-link']) ?>
                            </li>
                            <li class="submenu-item <?= Yii::$app->controller->id === 'project-assignment' ? 'active' : '' ?>">
                                <?= Html::a('<span class="submenu-icon">📌</span><span class="submenu-text">Project Assignment</span>', ['/project-assignment/index'], ['class' => 'submenu-link']) ?>
                            </li>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <div class="main-content" id="mainContent">
            <!-- Top Navigation Bar -->
            <header class="top-navbar">
                <div class="navbar-left">
                    <button class="burger-menu" id="burgerMenu" onclick="toggleSidebar()">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                    <h2 class="page-title"><?= Html::encode($this->title) ?></h2>
                </div>
                
                <div class="navbar-right">
                    <!-- User Profile Dropdown -->
                    <div class="user-profile-dropdown">
                        <button class="profile-btn" id="profileBtn" onclick="toggleProfileDropdown()">
                            <?php 
                            $user = Yii::$app->user->identity;
                            $profilePic = $user->profile_picture ?? null;
                            $fullName = $user->full_name ?? $user->username;
                            $position = $user->position ?? '';
                            $initial = strtoupper(substr($fullName, 0, 1));
                            ?>
                            <?php if ($profilePic): ?>
                                <img src="<?= Html::encode($profilePic) ?>" alt="Profile" class="profile-avatar-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="profile-avatar" style="display:none;"><?= $initial ?></div>
                            <?php else: ?>
                                <div class="profile-avatar"><?= $initial ?></div>
                            <?php endif; ?>
                            <div class="profile-info">
                                <span class="profile-name"><?= Html::encode($fullName) ?></span>
                                <?php if ($position): ?>
                                    <span class="profile-position"><?= Html::encode($position) ?></span>
                                <?php endif; ?>
                            </div>
                            <span class="dropdown-arrow">▼</span>
                        </button>
                        
                        <div class="profile-dropdown-menu" id="profileDropdown">
                            <div class="dropdown-header">
                                <?php if ($profilePic): ?>
                                    <img src="<?= Html::encode($profilePic) ?>" alt="Profile" class="dropdown-avatar-img">
                                <?php else: ?>
                                    <div class="dropdown-avatar"><?= $initial ?></div>
                                <?php endif; ?>
                                <div class="dropdown-info">
                                    <div class="dropdown-name"><?= Html::encode($fullName) ?></div>
                                    <?php if ($position): ?>
                                        <div class="dropdown-position"><?= Html::encode($position) ?></div>
                                    <?php endif; ?>
                                    <div class="dropdown-email"><?= Html::encode($user->email ?? '') ?></div>
                                </div>
                            </div>
                            <div class="dropdown-divider"></div>
                            <ul class="dropdown-list">
                                <li><?= Html::a('<span class="dropdown-icon">👤</span> My Profile', ['/profile/index'], ['class' => 'dropdown-link']) ?></li>
                                <li><?= Html::a('<span class="dropdown-icon">❓</span> Help', '#', ['class' => 'dropdown-link']) ?></li>
                            </ul>
                            <div class="dropdown-divider"></div>
                            <?= Html::beginForm(['/site/logout'], 'post', ['class' => 'logout-form']) ?>
                                <?= Html::submitButton('<span class="dropdown-icon">🚪</span> Logout', ['class' => 'dropdown-link logout-btn']) ?>
                            <?= Html::endForm() ?>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="page-content">
                <?= Alert::widget() ?>
                <?= $content ?>
            </main>

            <!-- Footer -->
            <footer class="dashboard-footer">
                <div class="footer-content">
                    <p>&copy; <?= date('Y') ?> EARS - Electronic Accomplishment Reporting System. All rights reserved.</p>
                </div>
            </footer>
        </div>
    </div>

    <script>
        // Global functions - must be accessible from onclick handlers
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const body = document.body;
            
            // For desktop - toggle collapsed state
            if (window.innerWidth > 1024) {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
            } else {
                // For mobile - toggle show state
                sidebar.classList.toggle('show');
                body.classList.toggle('sidebar-open');
            }
        }

        function toggleProfileDropdown() {
            const dropdown = document.getElementById('profileDropdown');
            dropdown.classList.toggle('show');
        }
        
        function toggleSettingsMenu(event) {
            event.preventDefault();
            const submenu = document.getElementById('settingsSubmenu');
            const navItem = event.currentTarget.closest('.nav-item-expandable');
            
            navItem.classList.toggle('expanded');
        }

        function toggleTasksMenu(event) {
            event.preventDefault();
            const submenu = document.getElementById('tasksSubmenu');
            const navItem = event.currentTarget.closest('.nav-item-expandable');
            
            navItem.classList.toggle('expanded');
        }



        // Event listeners
        document.addEventListener('click', function(event) {
            const profileBtn = document.getElementById('profileBtn');
            const dropdown = document.getElementById('profileDropdown');
            if (profileBtn && dropdown && !profileBtn.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });
        
        // Close mobile sidebar when clicking outside
        document.addEventListener('click', function(event) {
            if (window.innerWidth <= 1024) {
                const sidebar = document.getElementById('sidebar');
                const burgerMenu = document.getElementById('burgerMenu');
                
                if (sidebar && burgerMenu && 
                    sidebar.classList.contains('show') && 
                    !sidebar.contains(event.target) && 
                    !burgerMenu.contains(event.target)) {
                    sidebar.classList.remove('show');
                    document.body.classList.remove('sidebar-open');
                }
            }
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            
            if (window.innerWidth > 1024) {
                sidebar.classList.remove('show');
                document.body.classList.remove('sidebar-open');
            } else {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('expanded');
            }
        });
    </script>

<?php else: ?>
    <!-- Guest Layout -->
    <?= $content ?>
<?php endif; ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage();

