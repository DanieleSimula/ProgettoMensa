<?php
session_start();

if (isset($_SESSION['agent']) || isset($_SESSION['logged_in'])){
    header("location: index.php");
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
    <link href="css/form_style.css" rel="stylesheet">
    <script>
        function confermapsw(valore) {
            if (document.getElementById("password").value != valore) {
                alert("Le password non corrispondono, riprova.");
                document.getElementById("pswConferma").value = "";
                document.getElementById("pswConferma").focus();
            }
        }
    </script>
</head>

<body>
    <?php include_once 'component/navbar.php' ?>

    <!-- FORM LOGIN / REGISTRAZIONE -->
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10">
                <?php
                if (isset($_GET['new']) && $_GET['new'] == '1') {
                    echo '<form action="registrazione.php" class="needs-validation" novalidate method="post">';
                    echo '<h1 class="text-center mb-4">Inserisci i tuoi dati e registrati</h1>';
                } //needs-validation e un tipo di classe per bs5 novalidate disabilia gli avvisi default del browser
                else {
                    echo '<form action="accesso.php" class="needs-validation" novalidate method="post">';
                    echo '<h1 class="text-center mb-4">Inserisci i tuoi dati per accedere</h1>';
                }
                ?>

                <!-- Sezione Nome Utente e Password sempre in verticale -->
                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <?php
                        if (isset($_GET['error']) && $_GET['error'] == '1')
                            echo '<div class="alert alert-danger"> Credenziali non valide </div>';
                        ?>
                        <label for="nomeUtente" class="form-label">Nome Utente</label>
                        <input type="text" autocomplete="off" class="form-control" name="nomeUtente" id="nomeUtente"
                            tabindex="1" pattern="[A-Za-z0-9]{3,30}" minlength="3" maxlength="30" required>
                        <div class="invalid-feedback">Il nome utente deve essere di minimo 3 caratteri e massimo di 30
                            può contenere solo lettere e numeri</div>
                    </div>
                    <div class="col-12">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" autocomplete="off" class="form-control" name="password" id="password"
                            tabindex="2" pattern="[A-Za-z0-9]+" minlength="3" required>
                        <!-- pattern="(?=.*[A-Z])(?=.*[0-9])(?=.*[@#!?]).+" sarebbe più corretto questo-->
                        <div class="invalid-feedback">La password deve essere di minimo 3 caratteri e può contenere solo lettere e numeri</div>
                    </div>
                </div>

                <!-- Conferma Password (solo per registrazione) -->
                <?php if (isset($_GET['new']) && $_GET['new'] == '1'): ?>
                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label for="pswConferma" class="form-label">Conferma Password</label>
                            <input type="password" class="form-control" name="pswConferma" id="pswConferma" tabindex="3"
                                pattern="[A-Za-z0-9]+" minlength="3" required onchange="confermapsw(this.value)">
                        </div>
                    </div>
                <?php endif; ?>

                <!--Se è una nuova registrazione, carico la seconda parte del form-->
                <?php if (isset($_GET['new']) && $_GET['new'] == '1')
                    include 'component/form_register.html'; ?>

                <!-- Pulsanti -->
                <div class="row g-3 mt-4 mb-4">
                    <?php
                    if (isset($_GET['new']) && $_GET['new'] == '1') {
                        echo '<div class="col-12 col-md-6 text-center text-md-start">
                                            <button type="submit" class="btn btn-primary w-100" tabindex="13">REGISTRATI</button>
                                        </div>
                                        <div class="col-12 col-md-6 text-center text-md-end">
                                            <a href="login.php?new=0" class="d-block mt-2" tabindex="14">Hai già un account? Accedi</a>
                                        </div>';
                    } else {
                        echo '<div class="col-12 col-md-6 text-center text-md-start">
                                            <button type="submit" class="btn btn-primary w-100" tabindex="13">ACCEDI</button>
                                        </div>
                                        <div class="col-12 col-md-6 text-center text-md-end">
                                            <a href="login.php?new=1" class="d-block mt-2" tabindex="14">Sei un nuovo utente? Registrati</a>
                                        </div>';
                    }
                    ?>
                </div>
                </form>
            </div>
        </div>
    </div>

    <?php include_once 'component/footer.php' ?>
    <script>
        (() => {
            'use strict'
            // Cerca tutti i form con classe 'needs-validation'
            const forms = document.querySelectorAll('.needs-validation')

            // Cicla su ogni form trovato ma li restituisce in nodelist convertiamo con Array
            Array.from(forms).forEach(form => {

                // Mette una "sentinella" che ascolta l'evento submit
                form.addEventListener('submit', event => {

                    // Controlla se gli input sono validi
                    if (!form.checkValidity()) {
                        event.preventDefault()      // Blocca l'invio del form
                        event.stopPropagation()     // Ferma il bubbling
                    }

                    // Aggiunge la classe che attiva lo stile BS5 
                    // (bordi rossi/verdi e messaggi feedback)
                    form.classList.add('was-validated')

                }, false) // false = ascolta nella fase di bubbling
            })
        })()
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>

</html>