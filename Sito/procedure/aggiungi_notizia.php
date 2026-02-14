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
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //connessione db
    require_once __DIR__ . '/../config/connectOperatore.php';
    $titolo = $_POST['titolo'];
    $descrizione = $_POST['descrizione'];
    $contenuto = $_POST['contenuto'];
    $immagine = $_FILES['immagine'];
    $autore = $_SESSION['nomeUtente'];

    //blocco try catch per gestire errori
    try {
        if (empty($titolo) || empty($descrizione) || empty($contenuto) || empty($immagine)) {
            throw new Exception("Campi obbligatori mancanti");
        }

        $percorsorisorenotizie = "../risorse/notizie/";
        $percorsorisorenotizieupload = "risorse/notizie/";

        if (!is_dir($percorsorisorenotizie)) {
            echo 'la directory non esiste';
        }

        if (isset($_FILES['immagine']) && $_FILES['immagine']['error'] == 0) {
            $name = $_FILES['immagine']['name'];
            $tmpName = $_FILES['immagine']['tmp_name'];
            $nomefile = basename($name); //rende il nome sicuro

            $percorso = $percorsorisorenotizie . $nomefile;
            $percorsoupload = $percorsorisorenotizieupload . $nomefile;
        }

        if (move_uploaded_file($tmpName, $percorso)) {
            echo "File caricato con successo in: " . $percorso;
        } else {
            echo "Errore durante il caricamento del file.";
        }



        $sql = "CALL inserisci_notizia(?, ?, ?, ?, ?)";
        //php invia al db la struttura
//il db controlla se la struttura sql è corretta
////il db compila la query e aspetta il dato reale da inserire


        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            //in PDO gli erori sono gestiti tramite eccezioni
            $info = $conn->errorInfo();
            echo "errore nella query";
            throw new Exception("Errore nella preparazione della query: " . $info[2]);
        }

        // 1 indica il primo punto di domanda '?'
        $stmt->bindParam(1, $titolo, PDO::PARAM_STR);
        $stmt->bindParam(2, $descrizione, PDO::PARAM_STR);
        $stmt->bindParam(3, $contenuto, PDO::PARAM_STR);
        $stmt->bindParam(4, $percorsoupload, PDO::PARAM_STR);
        $stmt->bindParam(5, $autore, PDO::PARAM_STR);

        // Esecuzione
        $stmt->execute();

        // Chiusura (in PDO basta impostare a null o unset)
        $stmt = null;
        $conn = null;

        // SUCCESSO
        header("Location: ../operatore/notizie.php?msg=success_consumazione");
        exit();


    } catch (Exception $e) {
        echo "". $e->getMessage() ."";
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
        function changeTitle() {
            document.title = "Errore di inserimento";
        }
    </script>
    <style>
        .errore {
            color: red;
        }
    </style>
</head>

</html>