<?php
header('Content-Type: application/json');
//senza questo il browser non gestisce correttamente DS

$data = $_GET['data'] ?? date('Y-m-d'); //?? se è null da la data di oggi

require_once '../config/connect2DB.php';

$response = [
    'success' => true,
    'data' =>
    [
        'pranzo' => [],
        'cena' => []
    ]
];

try {
    $stmt = $conn->prepare("CALL GetMenuDelGiorno(?)");
    $stmt->execute([$data]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($results) === 0) {
        echo json_encode(['success' => false, 'error' => 'Nessun menu trovato']);
        exit;
    }

    foreach ($results as $row) {
        if ($row['tipo_pasto'] == 'Pranzo') {
            $response['data']['pranzo'][] = $row;
        } else {
            $response['data']['cena'][] = $row;
        }
    }

    echo json_encode($response);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Errore nel recupero del menu']);
}
?>
