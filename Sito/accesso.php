<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
        <!-- Bootstrap CSS -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
        <link href="css/custom.css" rel="stylesheet">
        <title>Accesso in corso</title>
        <script>
            function changeTitle(){
                document.title = "Errore di accesso";
            }
        </script>
        <style>
            .errore{
                color: red;
            }
        </style>
    </head>
    <body>
        <?php
            function controlloDB($nomeUtente, $password, $conn) {
                //controllo di sicurezza aggiuntivo (il form ha già i campi required)
                //se un malintenzionato toglie il required da un campo, riesce a mandare il form ma questo controllo lo blocca
                if(empty($nomeUtente) || empty($password)){
                    echo '<script>changeTitle();</script>';
                    echo "<h2 class=\"errore\">Errore nella compilazione del form, compila il <a href=\"login.php?new=0\">form di accesso</a> e riprova.</h2>";
                    exit();
                } else {
                    $query = "CALL getUserStudente(:nomeUtente)";
                    try {
                        $stmt = $conn->prepare($query);
                        $stmt->bindParam(':nomeUtente', $nomeUtente, PDO::PARAM_STR);
                        if ($stmt->execute()) {
                            $result = $stmt->fetch(PDO::FETCH_ASSOC);
                            if($result) {
                                if(password_verify($password, $result['passwordhash'])) {
                                    $_SESSION['nomeUtente'] = $nomeUtente;
                                    $_SESSION['agent'] = sha1($_SERVER['HTTP_USER_AGENT']);
                                    header("Location: area_personale.php");
                                    exit();
                                }
                            }
                        }
                        header("Location: login.php?error=1&new=0");
                        exit();
                    } catch (PDOException $e) {
                        echo "Errore di connessione o query: " . $e->getMessage();
                        exit();
                    }
                }
            }
            session_start();
            require_once 'config/connect2DB.php';
            //se stai facendo l'accesso tramite il form di accesso
            //oppure se la sessione esiste e le variabili sono settate
            if($_SERVER['REQUEST_METHOD'] === 'POST') {
                $nomeUtente = trim($_POST['nomeUtente']);
                $password = trim($_POST['password']);
                controlloDB($nomeUtente, $password, $conn);
            } else if(isset($_SESSION['agent']) && ($_SESSION['agent'] == sha1($_SERVER['HTTP_USER_AGENT']))
                        && isset($_SESSION['nomeUtente'])) {
                header("Location: area_personale.php");
                exit();
            }
            //se la sessione non esiste, è errata o le variabili non sono settate
            else if (!isset($_SESSION['agent']) || ($_SESSION['agent'] != sha1($_SERVER['HTTP_USER_AGENT']))
                || !isset($_SESSION['nomeUtente'])) {
                session_destroy();
                header("Location: index.php");
                exit();
            }
        ?>
    </body>
</html>