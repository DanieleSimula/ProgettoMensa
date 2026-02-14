<?php
require_once 'config/connect2DB.php';

if (!isset($_SESSION['nomeUtente'])) {
    $logged_in = false;
} else {
    if (isset($_SESSION['agent']) && $_SESSION['agent'] === sha1($_SERVER['HTTP_USER_AGENT'])) {
        $logged_in = true;
    } else {
        session_destroy();
        $logged_in = false;
        exit();
    }
}

?>
<nav class="navbar  navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">Mensa che vorrei</a>

        <!-- Contenitore per toggler per mobile -->
        <div class="d-flex align-items-center d-lg-none">
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Linea divisoria verticale -->
            <div class="vr bg-light mx-2" style="height: 40px; opacity: 0.5;"></div>

            <?php if (!$logged_in): ?>
                <!-- Bottone Login mobile [non loggato] -->
                <a class="btn btn-outline-light btn-sm" href="login.php?new=0">Login</a>
            <?php else: ?>
                <!-- Icona Profilo mobile [loggato] -->
                <div class="dropdown d-flex align-items-center">
                    <a class="nav-link text-light p-0 d-flex align-items-center" href="#" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor"
                            class="bi bi-person-circle" viewBox="0 0 16 16">
                            <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0" />
                            <path fill-rule="evenodd"
                                d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1" />
                        </svg>
                    </a>
                    <!-- menu dropdown per funzionalità cliccando l'icona utente, area privata e logout [mobile]-->
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark">
                        <?php
                        if (isset($_SESSION['logged_in'])) { // se siamo operatori perché logged_in è usato dal login operatori
                            if ($_SESSION['logged_in'])
                                echo '<li><a class="dropdown-item" href="operatore/dashboard.php">Area Privata</a></li>';
                            else//se logged_in è settato ma è falso per qualche motivo facciamo logout
                                header("location: operatore/logout.php");
                        } else { // se siamo studenti
                            echo '<li><a class="dropdown-item" href="area_personale.php">Area Privata</a></li>';
                        }
                        ?>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="operatore/logout.php">Logout</a></li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar [mobile] -->
        <div class="offcanvas offcanvas-end bg-dark" tabindex="-1" id="sidebarMenu">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title text-white">Menu</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body d-flex flex-column">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php if ($pageTitle == "Home")
                            echo 'active'; ?>" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php if ($pageTitle == "Contatti")
                            echo 'active'; ?>" href="contact.php">Contatti</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php if ($pageTitle == "Menu")
                            echo 'active'; ?>" href="menu.php">Menù</a>
                    </li>

                    <!-- Login/Profilo visibile solo su desktop -->
                    <li class="nav-item d-none d-lg-flex align-items-center">
                        <div class="vr bg-light mx-3" style="height: 40px; opacity: 0.5;"></div>

                        <?php if (!$logged_in): ?>
                            <!-- Bottone Login desktop [non loggato] -->
                            <a class="btn btn-outline-light" href="login.php?new=0">Login</a>
                        <?php else: ?>
                            <!-- Icona Profilo desktop [loggato] -->
                            <div class="dropdown">
                                <a class="nav-link p-0 d-flex align-items-center" href="#" role="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor"
                                        class="bi bi-person-circle" viewBox="0 0 16 16">
                                        <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0" />
                                        <path fill-rule="evenodd"
                                            d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1" />
                                    </svg>
                                </a>
                                <!-- menu dropdown per funzionalità cliccando l'icona utente, area privata e logout [desktop]-->
                                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark">
                                    <?php
                                    if (isset($_SESSION['logged_in'])) { // se siamo operatori perché logged_in è usato dal login operatori
                                        if ($_SESSION['logged_in'])
                                            echo '<li><a class="dropdown-item" href="operatore/dashboard.php">Area Privata</a></li>';
                                        else//se logged_in è settato ma è falso per qualche motivo facciamo logout
                                            header("location: operatore/logout.php");
                                    } else { // se siamo studenti
                                        echo '<li><a class="dropdown-item" href="area_personale.php">Area Privata</a></li>';
                                    }
                                    ?>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item" href="operatore/logout.php">Logout</a></li>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </div>

    </div>
</nav>