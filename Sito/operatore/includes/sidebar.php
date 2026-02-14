<!-- Bottone toggle per mobile (visibile solo su schermi piccoli) -->
<div class="d-md-none p-3 bg-light border-bottom">
    <button class="btn btn-primary" 
            type="button" 
            data-bs-toggle="offcanvas" 
            data-bs-target="#sidebarOffcanvas" 
            aria-controls="sidebarOffcanvas">
        <i class="bi bi-list"></i> Menu
    </button>
</div>

<!-- Sidebar Offcanvas per Mobile -->
<div class="offcanvas offcanvas-start d-md-none" 
     tabindex="-1" 
     id="sidebarOffcanvas" 
     aria-labelledby="sidebarOffcanvasLabel">
    <div class="offcanvas-header border-bottom">
        <div>
            <h5 class="offcanvas-title" id="sidebarOffcanvasLabel">Mensa</h5>
            <p class="small mb-0">che vorrei</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <nav class="mt-3">
            <a href="studenti.php" class="<?php echo $current_page === 'studenti' ? 'active' : ''; ?>">
                <i class="bi bi-people-fill me-2"></i>Studenti
            </a>
            <a href="notizie.php" class="<?php echo $current_page === 'notizie' ? 'active' : ''; ?>">
                <i class="bi bi-newspaper me-2"></i>Notizie
            </a>
            <a href="menu.php" class="<?php echo $current_page === 'menu' ? 'active' : ''; ?>">
                <i class="bi bi-card-list me-2"></i>Menu
            </a>
            <a href="piatti.php" class="<?php echo $current_page === 'piatti' ? 'active' : ''; ?>">
                <i class="bi bi-egg-fried me-2"></i>Piatti
            </a>
            <?php if ($user_role === 'admin'): ?>
                <a href="operatori.php" class="<?php echo $current_page === 'operatori' ? 'active' : ''; ?>">
                    <i class="bi bi-shield-lock me-2"></i>Operatori
                </a>
            <?php endif; ?>
            <hr class="my-3">
            <a href="../index.php">
                <i class="bi bi-house-fill me-2"></i>Home
            </a>
            <a href="logout.php">
                <i class="bi bi-box-arrow-right me-2"></i>Logout
            </a>
        </nav>
    </div>
</div>

<!-- Sidebar normale per Desktop (nascosta su mobile) -->
<div class="col-md-3 col-lg-2 sidebar d-none d-md-block">
    <div class="p-3 text-center border-bottom">
        <h5>Mensa</h5>
        <p class="small mb-0">che vorrei</p>
    </div>
    <nav class="mt-3">
        <a href="studenti.php" class="<?php echo $current_page === 'studenti' ? 'active' : ''; ?>">
            <i class="bi bi-people-fill me-2"></i>Studenti
        </a>
        <a href="notizie.php" class="<?php echo $current_page === 'notizie' ? 'active' : ''; ?>">
            <i class="bi bi-newspaper me-2"></i>Notizie
        </a>
        <a href="menu.php" class="<?php echo $current_page === 'menu' ? 'active' : ''; ?>">
            <i class="bi bi-card-list me-2"></i>Menu
        </a>
        <a href="piatti.php" class="<?php echo $current_page === 'piatti' ? 'active' : ''; ?>">
            <i class="bi bi-egg-fried me-2"></i>Piatti
        </a>
        <?php if ($user_role === 'admin'): ?>
            <a href="operatori.php" class="<?php echo $current_page === 'operatori' ? 'active' : ''; ?>">
                <i class="bi bi-shield-lock me-2"></i>Operatori
            </a>
        <?php endif; ?>
        <hr class="my-3">
        <a href="../index.php">
            <i class="bi bi-house-fill me-2"></i>Home
        </a>
        <a href="logout.php">
            <i class="bi bi-box-arrow-right me-2"></i>Logout
        </a>
    </nav>
</div>