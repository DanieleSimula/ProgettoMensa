<?php
session_start();

// Controllo se è loggato
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../operatore/frontend_login_operatori.php');
    exit();
}

// Controllo ruolo
if ($_SESSION['ruolo'] !== 'operator' && $_SESSION['ruolo'] !== 'admin') {
    header('Location: ../operatore/frontend_login_operatori.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../config/connectOperatore.php';

    $data_menu = $_POST['data_menu'] ?? '';
    $pranzo_cena = $_POST['tipo_pasto'] ?? '';
    $menu_id = $_POST['menu_id'] ?? '';

    try {
        if (empty($data_menu) || $pranzo_cena === '' || empty($menu_id)) {
            throw new Exception("Tutti i campi sono obbligatori");
        }

        $pranzo_cena = intval($pranzo_cena);
        $menu_id = intval($menu_id);

        $stmt = $conn->prepare("CALL assegna_menu_giorno(:data, :pranzo_cena, :menuID)");
        $stmt->bindParam(':data', $data_menu, PDO::PARAM_STR);
        $stmt->bindParam(':pranzo_cena', $pranzo_cena, PDO::PARAM_INT);
        $stmt->bindParam(':menuID', $menu_id, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->closeCursor();

        header("Location: ../operatore/menu.php?msg=success_assegna_menu");
        exit();

    } catch (Exception $e) {
        $error_message = $e->getMessage();
        header("Location: ../operatore/menu.php?msg=error_assegna_menu&error=" . urlencode($error_message));
        exit();
    }
}
?>
