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

    $nome_menu = trim($_POST['nome_menu'] ?? '');

    // Raccolgo i piatti selezionati
    $piatti_ids = [];
    if (!empty($_POST['piatti']) && is_array($_POST['piatti'])) {
        foreach ($_POST['piatti'] as $id) {
            $piatti_ids[] = intval($id);
        }
    }

    try {
        if (empty($nome_menu)) {
            throw new Exception("Il nome del menu è obbligatorio");
        }

        if (count($piatti_ids) === 0) {
            throw new Exception("Seleziona almeno un piatto");
        }

        // Passo gli id piatti come stringa CSV alla procedura
        $piatti_csv = implode(',', $piatti_ids);

        $stmt = $conn->prepare("CALL aggiungi_menu(:nome, :piatti_csv)");
        $stmt->bindParam(':nome', $nome_menu, PDO::PARAM_STR);
        $stmt->bindParam(':piatti_csv', $piatti_csv, PDO::PARAM_STR);
        $stmt->execute();
        $stmt->closeCursor();

        header("Location: ../operatore/menu.php?msg=success_inserimento_menu");
        exit();

    } catch (Exception $e) {
        $error_message = $e->getMessage();
        header("Location: ../operatore/menu.php?msg=error_inserimento_menu&error=" . urlencode($error_message));
        exit();
    }
}
?>
