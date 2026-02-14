<?php 
session_start();
$id = null;
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


$error_message ="";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //connessione db
    require_once __DIR__ . '/../config/connectOperatore.php';

    $id = $_POST['id_notizia'];
//blocco try catch per gestire errori
try{
    if (empty($id)){
    throw new Exception("L'id della notizia è obbligatorio");
    }
    $sql= "CALL toggle_notizia(?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        //in PDO gli erori sono gestiti tramite eccezioni
    $info = $conn->errorInfo();
    throw new Exception("Errore nella preparazione della query: " . $info[2]);
    }

    $stmt->bindParam(1, $id, PDO::PARAM_STR);

    if(!$stmt->execute()){
        throw new Exception("Errore nell'aggiornamento dello stato della notizia");
    }

    // Chiusura (in PDO basta impostare a null o unset)
    $stmt = null;
    $conn = null;

    // SUCCESSO
    header("Location: ../operatore/notizie.php?msg=success_toggle_notizia");
    exit();    

}catch(Exception $e){
    $error_message = $e->getMessage();
    echo "<script>window.onload = function() { changeTitle(); };</script>";
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

