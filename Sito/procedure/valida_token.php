<?php
require_once '../config/connect2DB.php';

header('Content-Type: application/json');

// Ricevi il token dal POST
$data = json_decode(file_get_contents('php://input'), true);
$token = $data['token'] ?? '';

if (empty($token)) {
    echo json_encode(['success' => false, 'message' => 'Token mancante']);
    exit();
}

try {
    // Chiama la procedura
    $stmt = $conn->prepare("CALL valida_token(?)");
    $stmt->execute([$token]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'esito' => $result['esito'],
            'studente' => $result['nome_studente'],
            'tipo_pasto' => $result['tipo_pasto'],
            'message' => 'Accesso consentito'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Errore nella validazione']);
    }
    
} catch (PDOException $e) {
    $message = $e->getMessage();
    
    if (strpos($message, 'non valido') !== false) {
        $msg = 'Token non valido';
    } elseif (strpos($message, 'scaduto') !== false) {
        $msg = 'Token scaduto';
    } elseif (strpos($message, 'già utilizzato') !== false) {
        $msg = 'Pasto già utilizzato';
    } elseif (strpos($message, 'Fuori orario') !== false) {
        $msg = 'Fuori orario mensa';
    } else {
        $msg = 'Errore: ' . $message;
    }
    
    echo json_encode(['success' => false, 'message' => $msg]);
}
?>