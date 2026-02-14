<?php
session_start();
require_once '../config/connectStudent.php';

header('Content-Type: application/json');

if (!isset($_SESSION['nomeUtente']) || !isset($_SESSION['agent']) ||
    $_SESSION['agent'] != sha1($_SERVER['HTTP_USER_AGENT'])) {
    echo json_encode(['success' => false, 'message' => 'Sessione non valida']);
    exit();
}

try {
    $nomeUtente = $_SESSION['nomeUtente'];

    $stmt = $conn->prepare("CALL getInfoStudente(:nomeUtente)");
    $stmt->bindParam(':nomeUtente', $nomeUtente, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo json_encode([
            'success' => true,
            'pasti' => (int)$result['pasti']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Studente non trovato']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Errore: ' . $e->getMessage()]);
}
?>
