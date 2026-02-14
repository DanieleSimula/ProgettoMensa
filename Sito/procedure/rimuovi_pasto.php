<?php 
session_start();

// 1. Controllo Autenticazione
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: frontend_login_operatori.php');
    exit();
}
if ($_SESSION['ruolo'] !== 'operator' && $_SESSION['ruolo'] !== 'admin') {
    header('Location: frontend_login_operatori.php');
    exit();
}

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../config/connectOperatore.php';
    
    $cf = $_POST['cf'] ?? '';

    try {
        if (empty($cf)) {
            throw new Exception("Codice fiscale mancante.");
        }

        // 2. Chiamata alla procedura
        $sql = "CALL rimuovi_consumazione(?)";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $info = $conn->errorInfo();
            throw new Exception("Errore Prepare: " . $info[2]);
        }

        $stmt->bindParam(1, $cf, PDO::PARAM_STR);

        // 3. Esecuzione con controllo
        if (!$stmt->execute()) {
            $info = $stmt->errorInfo();
            throw new Exception("Errore SQL: " . $info[2]);
        }

        $stmt = null;
        $conn = null;

        // Successo
        header("Location: ../operatore/studenti.php?msg=success_rimozione_consumazione");
        exit();       

    } catch(Exception $e) {
        $error_message = $e->getMessage();
        header("Location: ../operatore/studenti.php?msg=errore_rimozione_consumazione");
    }
}
?>