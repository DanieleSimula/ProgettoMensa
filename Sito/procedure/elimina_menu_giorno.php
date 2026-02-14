<?php 
session_start();

//controllo se è loggato
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: frontend_login_operatori.php');
    exit();
}

//controllo ruolo
if ($_SESSION['ruolo'] !== 'operator' && $_SESSION['ruolo'] !== 'admin') {
    header('Location: frontend_login_operatori.php');
    exit();
}

//inizializzo variabile errore html
$error_message ="";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //connessione db
    require_once __DIR__ . '/../config/connectOperatore.php';
    $data = $_POST['data_menu'];
    $pasto = $_POST['pranzo_cena'];

//blocco try catch per gestire errori
try{
    
if (empty($data) || ($pasto !== '0' && empty($pasto))) {
    throw new Exception("data o pasto vuoto");
}

$sql= "CALL rimuovi_menu_giorno(?, ?)";


$stmt = $conn->prepare($sql);

if (!$stmt) {
        //in PDO gli erori sono gestiti tramite eccezioni
            $info = $conn->errorInfo();
            throw new Exception("Errore nella preparazione della query: " . $info[2]);
        }

// 1 indica il primo punto di domanda '?'
$stmt->bindParam(1, $data, PDO::PARAM_STR);
$stmt->bindParam(2, $pasto, PDO::PARAM_STR);

// Esecuzione
$stmt->execute();

// Chiusura (in PDO basta impostare a null o unset)
$stmt = null;
$conn = null;

// SUCCESSO
header("Location: ../operatore/menu.php?msg=success_eliminazione");
exit();       


}catch(Exception $e){
    header("Location: ../operatore/menu.php?msg=errore_eliminazione&dettaglio=" . urlencode($e->getMessage()));
    exit();
}
}
?>
