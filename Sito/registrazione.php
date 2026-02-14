<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
    <title>Registrazione in corso</title>
    <style>
        .errore {
            color: red;
        }
    </style>
    <script>
        function changeTitle() {
            document.title = "Errore nella registrazione";
        }
    </script>
</head>

<body>
    <?php
    function calcolaFascia($isee)
    {
        if ($isee <= 9999) {
            $fascia = 1;
        } else if ($isee >= 10000 && $isee <= 19999) {
            $fascia = 2;
        } else {
            $fascia = 3;
        }
        return $fascia;
    }

    session_start();
    require_once 'config/connect2DB.php';
    //se stai facendo l'accesso tramite il form di registrazione
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nomeUtente = trim($_POST['nomeUtente']);
        $password = trim($_POST['password']);
        $email = trim($_POST['email']);
        $nome = trim($_POST['nome']);
        $cognome = trim($_POST['cognome']);
        $sesso = trim($_POST['sesso']);
        $dataNascita = trim($_POST['dataNascita']);
        $indirizzo = trim($_POST['indirizzo']);
        $cf = strtoupper(trim($_POST['cf']));
        $citta = trim($_POST['citta']);
        $isee = trim($_POST['isee']);

        //controllo di sicurezza aggiuntivo (il form ha già i campi required)
        //se un malintenzionato toglie il required da un campo, riesce a mandare il form ma questo controllo lo blocca
        if (
            empty($nomeUtente) || empty($password) || empty($email) || empty($nome) || empty($cognome) ||
            empty($sesso) || empty($dataNascita) || empty($indirizzo) || empty($cf) || empty($citta) || empty($isee)
        ) {
            header("Location: login.php?error=1&new=1");
            exit();
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $fascia = calcolaFascia($isee);
            try {
                $query = "CALL regNewStudente(:nomeutente, :email, :password, :cf, :nome, :cognome, :sesso, :datanascita, :indirizzo, :citta, :fascia)";
                $stmt1 = $conn->prepare($query);
                $stmt1->bindParam(':nomeutente', $nomeUtente, PDO::PARAM_STR);
                $stmt1->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt1->bindParam(':password', $password_hash, PDO::PARAM_STR);
                $stmt1->bindParam(':cf', $cf, PDO::PARAM_STR);
                $stmt1->bindParam(':nome', $nome, PDO::PARAM_STR);
                $stmt1->bindParam(':cognome', $cognome, PDO::PARAM_STR);
                $stmt1->bindParam(':sesso', $sesso, PDO::PARAM_STR);
                $stmt1->bindParam(':datanascita', $dataNascita, PDO::PARAM_STR);
                $stmt1->bindParam(':indirizzo', $indirizzo, PDO::PARAM_STR);
                $stmt1->bindParam(':citta', $citta, PDO::PARAM_STR);
                $stmt1->bindParam(':fascia', $fascia, PDO::PARAM_INT); 
    
                if ($stmt1->execute()) {
                    $stmt1->closeCursor(); 
                    $_SESSION['nomeUtente'] = $nomeUtente;
                    $_SESSION['agent'] = sha1($_SERVER['HTTP_USER_AGENT']);
                    header("Location: accesso.php");
                    exit();
                } else {
                    header("Location: login.php?error=1&new=1");
                    exit();
                }
            } catch (Exception $e) {
                header("Location: login.php?error=1&new=1");
                session_destroy();
                exit();
            } catch (PDOException $e) {
                header("Location: login.php?error=1&new=1");
                session_destroy();
                exit();
            }
        }
    }
    //se la sessione non esiste, è errata o le variabili non sono settate
    else {
        header("Location: index.php");
        exit();
    }
    ?>
</body>

</html>