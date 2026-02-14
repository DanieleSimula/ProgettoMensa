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
    $titolo = $_POST['nome_piatto'];
    $descrizione = $_POST['descrizione_piatto'];


//blocco try catch per gestire errori
try{
    if (empty($titolo) || empty($descrizione)) {
    throw new Exception("Campi obbligatori mancanti");
}

$sql= "CALL aggiungi_piatto(?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bindParam(1, $titolo, PDO::PARAM_STR);
$stmt->bindParam(2, $descrizione, PDO::PARAM_STR);

if (!$stmt->execute()) {
    $info = $stmt->errorInfo();
    //se execute fallisce ma non lancia un'eccezione, gestisco l'errore manualmente
    if($info[1] == 1062){ // codice errore per chiave duplicata (es. email già esistente)
        throw new Exception("Piatto già esistente");
    }else{
        throw new Exception("Errore nell'esecuzione della query: " . $info[2]);
    }
   }

// SUCCESSO
header("Location: ../operatore/piatti.php?msg=success_inserimento_piatto");
exit();       

}catch(Exception $e){
    $error_message = $e->getMessage();
    header("Location: ../operatore/piatti.php?msg=error_inserimento_piatto&error=" . urlencode($error_message));
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

