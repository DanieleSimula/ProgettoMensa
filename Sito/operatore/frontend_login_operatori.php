<?php
session_start();

if (isset($_SESSION["logged_in"])) {
    if ($_SESSION["logged_in"]) {
        header("location: dashboard.php");
    } else {
        header("location: logout.php");
    }
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <?php
    if (isset($_GET['new']) && $_GET['new'] == '1')
        echo '<title>Registrati - Mensa che vorrei</title>';
    else
        echo '<title>Accedi - Mensa che vorrei</title>';
    ?>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
    <link href="css/login_style.css" rel="stylesheet">
</head>

<body>
    <?php
    echo '<form action="accesso_operatori.php" method="post">';
    ?>
    <!-- FORM LOGIN  -->
    <div class="container mt-5 border p-5 shadow">
        <div class="container text-center">
            <div class="row justify-content-center">
                <h3 class="mb-4">Inserire i dati operatore o admin</h3>
                <div class="col-4" center>
                    <label for="nomeUtente" class="form-label">Nome Utente</label>
                    <input type="text" class="form-control" name="nomeUtente" id="nomeUtente" required>
                </div>
            </div>
            <div class="row justify-content-center">

                <div class="col-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" id="password" required>
                </div>

            </div>

            <div class="bottom_form mt-5">
                <div class=col>
                    <button type="submit" class="btn btn-primary">ACCEDI</button>
                </div>
            </div>
        </div>
        </form>
</body>

</html>