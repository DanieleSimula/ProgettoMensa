<?php
    session_start();
    require_once 'config/connectStudent.php';

    //se la sessione non esiste, è errata o le variabili non sono settate
    if (!isset($_SESSION['agent']) || ($_SESSION['agent'] != sha1($_SERVER['HTTP_USER_AGENT']))
        || !isset($_SESSION['nomeUtente'])) {
        session_destroy();
        header("Location: index.php");
        exit();
    }
    //altrimenti recupero i dati dalla sessione
    else {
        $costo_pasto = $_SESSION['costo_pasto'];
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
        <link href="css/form_style.css" rel="stylesheet">
        <title>Form di pagamento</title>
        <script>
            function calcolaCosto(nPasti, costo) {
                document.getElementById("costo").innerText = "€ " + (nPasti * costo).toFixed(2);
            }
            
            document.getElementById('myForm').addEventListener('submit', function(event) {
                if(document.getElementById("metodoPagamento").value == ""
                    || document.getElementById("pasti").checkValidity().rangeOverflow) {
                    event.preventDefault();
                    alert("Per favore, seleziona un metodo di pagamento e inserisci la quantità di pasti.");
                }
            }); 
        </script>
        <style>
            #costo{
                font-size: xxx-large;
            }
        </style>
    </head>
    <body>
        <?php include_once 'component/navbar.php' ?>
        <form action="procedure/acquisto_pasto.php" method="post" id="form">
            <h1>Inserisci i dati di pagamento</h1>
            <div class="container mt-5">
                <div class="container text-center">
                    <div class="row align-items-start section">
                        <label for="metodoPagamento" class="form-label">Metodo di pagamento</label>
                        <select class="form-select" name="metodoPagamento" id="metodoPagamento" required>
                            <option value="" selected>Scegli un'opzione</option>
                            <option value="visa">Visa</option>
                            <option value="mastercard">Mastercard</option>
                            <option value="paypal">PayPal</option>
                        </select>
                        <br><br><br>
                        <?php echo '<input type="number" name="pasti" id="pasti" min="1" max="100" value="0" onchange="calcolaCosto(this.value, ' . $costo_pasto . ')">'; ?>
                        <label for="pasti" id="costo">€ 0,00</label>
                    </div>
                    <hr>
                    <div class="bottom_form">
                        <div class="row align-items-start">
                            <div class="col">
                                <button type="submit" class="btn btn-primary">PROCEDI AL PAGAMENTO</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </body>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</html>