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
    $cf = $_POST['cf'];
    $fascia = $_POST['fascia'];
    $quantita = $_POST['quantita'];

//blocco try catch per gestire errori
try{
    
if (empty($cf) || empty($fascia) || empty($quantita)) {
    throw new Exception("Campi obbligatori mancanti");
}

$sql= "CALL inserisci_consumazione(?,?,?)";
//php invia al db la struttura
//il db controlla se la struttura sql è corretta
////il db compila la query e aspetta il dato reale da inserire


$stmt = $conn->prepare($sql);

if (!$stmt) {
        //in PDO gli erori sono gestiti tramite eccezioni
            $info = $conn->errorInfo();
            throw new Exception("Errore nella preparazione della query: " . $info[2]);
        }

// 1 indica il primo punto di domanda '?'
$stmt->bindParam(1, $cf, PDO::PARAM_STR);
$stmt->bindParam(2, $fascia, PDO::PARAM_INT);
$stmt->bindParam(3, $quantita, PDO::PARAM_INT);

// Esecuzione
$stmt->execute();

// Chiusura (in PDO basta impostare a null o unset)
$stmt = null;
$conn = null;

// SUCCESSO
header("Location: ../operatore/studenti.php?msg=success_consumazione");
exit();       


}catch(Exception $e){
    $error_message = $e->getMessage();
    exit();
}
}
?>
<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
        <!-- Bootstrap CSS -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
        <link href="css/custom.css" rel="stylesheet">
        <title>Inserimento in corso</title>
        <script>
            //serve a cambiare il titolo della scheda del browser in "Errore di inserimento". Viene chiamata dal PHP quando qualcosa va storto.
            function changeTitle(){
                document.title = "Errore di inserimento";
            }
        </script>
        <style>
            .errore{
                color: red;
            }
        </style>
    </head>
</html>

