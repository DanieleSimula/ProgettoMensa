<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
        <!-- Bootstrap CSS -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
        <link href="css/custom.css" rel="stylesheet">
        <title>Acquisto pasti</title>
    </head>
    <body>
        <?php
            session_start();
            require_once '../config/connectStudent.php';

            if($_SERVER['REQUEST_METHOD'] === 'POST') {
                $quantita = $_POST['pasti'];
                $cf = $_SESSION['cf'];
                $fascia = $_SESSION['fascia'];

                $query = "CALL inserisci_consumazione(:cf, :fascia, :quantita);";
                try {
                    $stmt = $conn->prepare($query);
                    $stmt->bindParam(':cf', $cf, PDO::PARAM_STR);
                    $stmt->bindParam(':fascia', $fascia, PDO::PARAM_STR);
                    $stmt->bindParam(':quantita', $quantita, PDO::PARAM_INT);
                    if(!$stmt->execute()) {
                        echo "<div class=\"container text-center mt-5\">
                                <div class=\"d-flex flex-column align-items-center\">
                                    <h2>Errore nell'acquisto!</h2>
                                    <img class=\"img-fluid\" src=\"../risorse/errorIMG.png\" alt=\"Errore\">
                                    <button class=\"btn btn-primary\" onclick=\"window.location.href='../area_personale.php'\">Torna all'area personale</button>
                                </div>
                            </div>";
                    } else {
                        echo "<div class=\"container text-center mt-5\">
                                <div class=\"d-flex flex-column align-items-center\">
                                    <h2>Acquisto effettuato con successo!</h2>
                                    <img class=\"img-fluid\" src=\"../risorse/checkIMG.png\" alt=\"Successo\">
                                    <button class=\"btn btn-primary\" onclick=\"window.location.href='../area_personale.php'\">Torna all'area personale</button>
                                </div>
                            </div>";
                    }
                } catch (PDOException $e) {
                    echo "Errore durante l'inserimento della consumazione: " . $e->getMessage();
                }
                exit();
            } else {
                header("Location: ../area_personale.php");
                exit();
            }
        ?>
    </body>
</html>