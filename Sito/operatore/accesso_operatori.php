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
            //serve a cambiare il titolo della scheda del browser in "Errore di accesso". Viene chiamata dal PHP quando qualcosa va storto.
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
            //controllo delle credenziali nel DB   
            function controlloDB($nomeUtente, $password, $conn) {
                //controllo di sicurezza delle credenziali, controlla se sono vuoti
                if(empty($nomeUtente) || empty($password)){
                    echo '<script>changeTitle();</script>';
                    echo "<h2 class=\"errore\">Errore nella compilazione del form, compila il <a href=\"frontend_login_operatori.php\">form di accesso</a> e riprova.</h2>";
                exit();
            } else {
                //Query SQL per cercare l'hash della password dell'utente.
                $query = "CALL login_operatore(:nomeUtente)";
                try {
                  
                    $stmt = $conn->prepare($query);


                    $stmt->bindParam(':nomeUtente', $nomeUtente, PDO::PARAM_STR);
                  
                    if ($stmt->execute()) {
                     
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                        if($result) {
                            
                            if(password_verify($password, $result['passwordhash'])) {
                                session_unset();
                               
                                $_SESSION['agent'] = sha1($_SERVER['HTTP_USER_AGENT']);
                                $_SESSION['nomeUtente'] = $nomeUtente;
                                $_SESSION['ruolo'] = $result['ruolo'];
                                $_SESSION['logged_in'] = true;
                               
                                header("Location: dashboard.php");
                                exit();
                        
                            } else {
                                echo '<script>changeTitle();</script>';
                                echo "<h2 class=\"errore\">Password errata, compila il <a href=\"frontend_login_operatori.php\">form di accesso</a> e riprova.</h2>";
                                exit();
                            }
                        //se non trova un risultato
                        } else {
                            echo '<script>changeTitle();</script>';
                            echo "<h2 class=\"errore\">Credenziali non valide, compila il <a href=\"frontend_login_operatori.php\">form di accesso</a> e riprova.</h2>";
                            exit();
                        }
                    //se l'esecuzione della query fallisce viene lanciato un errore
                    } else {
                        echo '<script>changeTitle();</script>';
                        echo "<h2 class=\"errore\">Errore di accesso, compila il <a href=\"frontend_login_operatori.php\">form di accesso</a> e riprova.</h2>";
                        throw new Exception("Errore nell'inserimento dell'utente");
                    }
                //catch degli errori di connessione o query
                } catch (PDOException $e) {
                    echo "Errore di connessione o query: " . $e->getMessage();
                    exit();
                }
            }
        }
        session_start();
        //includo il file di connessione al db
        require_once __DIR__ . '/../config/connect2DB.php';
        //se stai facendo l'accesso tramite il form di accesso, controllo se l'utente ha cliccato accedi da un form
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
                //trim() pulisce gli spazi vuoti di inizio e di fine stringa            
                $nomeUtente = trim($_POST['nomeUtente']);
                $password = trim($_POST['password']);
                //chiamo la funzione di controllo delle credenziali
                controlloDB($nomeUtente, $password, $conn);
                //se la sessione esiste e le variabili sono settate, controllo se l'utente è già loggato
                //$_SESSION['agent'] == sha1($_SERVER['HTTP_USER_AGENT']) controlla se l'user agent cambia durante la sessione
            } else if(isset($_SESSION['agent']) && ($_SESSION['agent'] == sha1($_SERVER['HTTP_USER_AGENT']))
                        && isset($_SESSION['nomeUtente']) && isset($_SESSION['password'])) {
                $nomeUtente = $_SESSION['nomeUtente'];
                $password = $_SESSION['password'];
               
                //chiamo la funzione di controllo delle credenziali
                controlloDB($nomeUtente, $password, $conn);
            }
            //se la sessione non esiste, è errata o le variabili non sono settate
            else if (!isset($_SESSION['agent']) || ($_SESSION['agent'] != sha1($_SERVER['HTTP_USER_AGENT']))) {
                //distruggo la sessione e reindirizzo al login
                session_destroy();
                header("Location: frontend_login_operatori.php");
                exit();
            }
        ?>
    </body>
</html>


