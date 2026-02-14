<?php
session_start();
require_once '../config/connectStudent.php';

header('Content-Type: application/json');

// Verifica sessione
if (!isset($_SESSION['nomeUtente']) || !isset($_SESSION['agent']) || 
    $_SESSION['agent'] != sha1($_SERVER['HTTP_USER_AGENT'])) {
    echo json_encode(['success' => false, 'message' => 'Sessione non valida']);
    exit();
}

try {
    
    $cf = $_SESSION['cf'];
    $codice_segreto = 'CHIAVE_SEGRETA_MENSA_2026';
    
    // Chiama la procedura
    $stmt = $conn->prepare("CALL genera_token(?, ?)");
    $stmt->execute([$cf, $codice_segreto]);
    $token = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($token) {
        echo json_encode([
            'success' => true,
            'token' => $token['token'],
            'pasto' => $token['pasto'],
            'scadenza' => $token['scadenza'],
            'esistente' => (bool)$token['esistente']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Errore nella generazione del token']);
    }
    
} catch (PDOException $e) {
    $message = $e->getMessage();
    
    if (strpos($message, 'pasti disponibili') !== false) {
        echo json_encode(['success' => false, 'message' => 'Non hai pasti disponibili']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Errore: ' . $message]);
    }
}
?>