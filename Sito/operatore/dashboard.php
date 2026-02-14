<?php
$current_page = 'dashboard';
require_once 'includes/session_check.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>
<div class="col-md-9 col-lg-10 main-content">
    <div class="row mb-4">
        <div class="col">
            <h1>Benvenuto <?php echo htmlspecialchars($user_role); ?></h1>
            <p class="lead">Seleziona una sezione dal menu laterale per iniziare.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card stat-card">
                <i class="bi bi-people-fill" style="font-size: 2rem;"></i>
                <h5>Gestione Studenti</h5>
                <a href="studenti.php" class="btn btn-light btn-sm mt-2">Vai alla sezione</a>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card stat-card success">
                <i class="bi bi-newspaper" style="font-size: 2rem;"></i>
                <h5>Gestione Notizie</h5>
                <a href="notizie.php" class="btn btn-light btn-sm mt-2">Vai alla sezione</a>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card stat-card info">
                <i class="bi bi-card-list" style="font-size: 2rem;"></i>
                <h5>Gestione Menu</h5>
                <a href="menu.php" class="btn btn-light btn-sm mt-2">Vai alla sezione</a>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card stat-card warning">
                <i class="bi bi-egg-fried" style="font-size: 2rem;"></i>
                <h5>Gestione Piatti</h5>
                <a href="piatti.php" class="btn btn-light btn-sm mt-2">Vai alla sezione</a>
            </div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
