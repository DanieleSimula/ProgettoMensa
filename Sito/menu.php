<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.0/font/bootstrap-icons.min.css"
        rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
</head>
<style>
    .card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
        transform: translateY(-8px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }
</style>

<body>
    <?php
    session_start();
    $pageTitle = 'Menu';
    include 'component/navbar.php';
    
    // Ottieni la data dal parametro GET o usa la data odierna
    $dataSelezionata = $_GET['data'] ?? date('Y-m-d');
    ?>

    <!-- Alert per menu non disponibile -->
    <div class="container mt-4">
        <div id="alertContainer"></div>
    </div>

    <div class="row justify-content-center mt-4">
        <div class="col-auto">
            <div class="d-flex align-items-center gap-3 bg-light p-3 rounded-4 shadow-sm">
                <i class="bi bi-calendar-event fs-4 text-primary"></i>
                <input type="date" id="menuDate" class="form-control form-control-lg border-0 bg-transparent"
                    style="max-width: 200px;" value="<?php echo htmlspecialchars($dataSelezionata); ?>">
            </div>
        </div>
    </div>


    <div class="row justify-content-center g-5 mt-4 mx-4">
        <!-- Colonna PRANZO -->
        <div class="col-md-5 border shadow p-5 g-4 mb-5" id="colonnaPranzo">
            <h1 class="display-1 text-center">PRANZO</h1>
            <div class="card rounded-4 mb-4">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item text-muted">Caricamento...</li>
                </ul>
            </div>
        </div>

        <!-- Colonna CENA -->
        <div class="col-md-5 border shadow p-5 g-4 mb-5" id="colonnaCena">
            <h1 class="display-1 text-center">CENA</h1>
            <div class="card rounded-4 mb-4">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item text-muted">Caricamento...</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const datePicker = document.getElementById('menuDate');
            const alertContainer = document.getElementById('alertContainer');

            // Carica il menu della data corrente (dal GET o oggi)
            caricaMenu(datePicker.value);

            // Evento al cambio data
            datePicker.addEventListener('change', function () {
                const dataSelezionata = this.value;
                
                // Aggiorna l'URL con la nuova data
                const url = new URL(window.location.href);
                url.searchParams.set('data', dataSelezionata);
                window.history.pushState({}, '', url);
                
                // Carica il menu
                caricaMenu(dataSelezionata);
            });

            function mostraAlert(tipo, messaggio) {
                const iconMap = {
                    'success': 'bi-check-circle-fill',
                    'danger': 'bi-exclamation-triangle-fill',
                    'warning': 'bi-exclamation-circle-fill',
                    'info': 'bi-info-circle-fill'
                };

                alertContainer.innerHTML = `
                    <div class="alert alert-${tipo} alert-dismissible fade show d-flex align-items-center" role="alert">
                        <i class="bi ${iconMap[tipo]} me-2"></i>
                        <div>${messaggio}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
            }

            function nascondiAlert() {
                alertContainer.innerHTML = '';
            }

            function caricaMenu(data) {
                // Nascondi eventuali alert precedenti
                nascondiAlert();

                fetch('procedure/get_menu.php?data=' + data)
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            aggiornaColonna('pranzo', result.data.pranzo);
                            aggiornaColonna('cena', result.data.cena);
                        } else {
                            svuotaColonne();
                            mostraAlert('warning', `<strong>Attenzione!</strong> Nessun menu disponibile per il giorno ${formatData(data)}.`);
                        }
                    })
                    .catch(error => {
                        console.error('Errore:', error);
                        svuotaColonne();
                        mostraAlert('danger', `<strong>Errore!</strong> Impossibile caricare il menu. Riprova più tardi.`);
                    });
            }

            function formatData(dataString) {
                const data = new Date(dataString);
                const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                return data.toLocaleDateString('it-IT', options);
            }

            function aggiornaColonna(tipo, piatti) {
                const colonna = tipo === 'pranzo' ?
                    document.getElementById('colonnaPranzo') :
                    document.getElementById('colonnaCena');

                const lista = colonna.querySelector('.list-group');
                lista.innerHTML = '';

                if (piatti.length > 0) {
                    piatti.forEach(piatto => {
                        const li = document.createElement('li');
                        li.className = 'list-group-item d-flex justify-content-between align-items-center flex-wrap';

                        let html = `<span>${piatto.piatto_nome}</span>`;

                        if (piatto.allergeni) {
                            const allergeniArray = piatto.allergeni.split(', ');
                            html += '<div class="mt-1">';
                            allergeniArray.forEach(allergene => {
                                html += `<span class="badge bg-warning text-dark me-1"><i class="bi bi-exclamation-triangle-fill"></i> ${allergene}</span>`;
                            });
                            html += '</div>';
                        }

                        li.innerHTML = html;
                        lista.appendChild(li);
                    });
                } else {
                    const li = document.createElement('li');
                    li.className = 'list-group-item text-muted';
                    li.textContent = 'Non disponibile';
                    lista.appendChild(li);
                }
            }

            function svuotaColonne() {
                document.querySelectorAll('.card .list-group').forEach(lista => {
                    lista.innerHTML = '<li class="list-group-item text-muted">Non disponibile</li>';
                });
            }
        });
    </script>

    <?php
    include 'component/footer.php';
    ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>

</html>