<?php
session_start();
require_once 'config/connectStudent.php';
if (
    !isset($_SESSION['agent']) || ($_SESSION['agent'] != sha1($_SERVER['HTTP_USER_AGENT']))
    || !isset($_SESSION['nomeUtente'])
) {
    session_destroy();
    header("Location: index.php");
    exit();
} else {
    if (isset($_SESSION['logged_in'])) {
        header("Location: index.php");
        exit();
    }
    $nomeUtente = $_SESSION['nomeUtente'];
    $query = "CALL getInfoStudente(:nomeUtente)";

    try {
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':nomeUtente', $nomeUtente, PDO::PARAM_STR);
        if ($stmt->execute()) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                $nome = $result['nome'];
                $cognome = $result['cognome'];
                $sesso = $result['sesso'];
                $datanascita = $result['datanascita'];
                $cf = $result['cf'];
                $indirizzo = $result['indirizzo'];
                $citta = $result['citta'];
                $fascia = $result['fascia'];
                $pasti = $result['pasti'];
                $_SESSION['cf'] = $cf;
                $_SESSION['fascia'] = $fascia;
            } else {
                throw new Exception("Impossibile recuperare i dati");
            }
        } else {
            throw new Exception("Errore nell'inserimento dell'utente");
        }
        $query = "CALL getTariffa(:fasciaIsee)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':fasciaIsee', $fascia, PDO::PARAM_STR);
        if ($stmt->execute()) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                $costo_pasto = $result['costo'];
                $_SESSION['costo_pasto'] = $costo_pasto;
            }
        } else {
            throw new Exception("Impossibile recuperare i dati");
        }
    } catch (Exception $e) {
        echo "Errore di connessione o query: " . $e->getMessage();
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <title>Area Personale</title>
</head>

<body class="bg-light">
    <?php include_once 'component/navbar.php'; ?>

    <div class="container-fluid px-4 px-lg-5 mt-4 pt-3">
        <!-- Banner benvenuto -->
        <div class="bg-primary text-white rounded-3 p-4 mb-4">
            <h1 class="fw-bold mb-1"><?php echo 'Benvenuto, ' . $nome . ' ' . $cognome; ?></h1>
            <p class="mb-0 opacity-75">Gestisci i tuoi pasti e il tuo profilo</p>
        </div>

        <!-- Riga 3 card -->
        <div class="row g-4 mb-4">
            <!-- Pasti disponibili -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center d-flex flex-column justify-content-center p-4">
                        <h6 class="text-uppercase text-muted fw-semibold small">Pasti Disponibili</h6>
                        <p class="display-3 fw-bold text-primary my-3"><?php echo $pasti; ?></p>
                        <div class="d-grid">
                            <button type="button" class="btn btn-primary btn-lg fw-semibold" id="btnGeneraBuono">
                                Utilizza Buono
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fascia e costo -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center d-flex flex-column justify-content-center p-4">
                        <h6 class="text-uppercase text-muted fw-semibold small">Fascia ISEE</h6>
                        <p class="display-5 fw-bold my-3"><?php echo $fascia; ?></p>
                        <p class="text-muted mb-3">Costo per pasto: <strong
                                class="text-dark">&euro;<?php echo number_format($costo_pasto, 2); ?></strong></p>
                        <form action="pagamento.php" method="post" class="d-grid mt-auto">
                            <button type="submit" class="btn btn-success btn-lg fw-semibold">Carica Pasti</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Info studente -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h6 class="text-uppercase text-muted fw-semibold small mb-3">I tuoi dati</h6>
                        <table class="table table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-muted ps-0 py-2">Nome</td>
                                    <td class="fw-semibold text-end pe-0 py-2"><?php echo $nome . ' ' . $cognome; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0 py-2">Codice Fiscale</td>
                                    <td class="fw-semibold text-end pe-0 py-2"><?php echo $cf; ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0 py-2">Data di nascita</td>
                                    <td class="fw-semibold text-end pe-0 py-2">
                                        <?php echo date('d/m/Y', strtotime($datanascita)); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0 py-2">Indirizzo</td>
                                    <td class="fw-semibold text-end pe-0 py-2"><?php echo $indirizzo; ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0 py-2">Città</td>
                                    <td class="fw-semibold text-end pe-0 py-2"><?php echo $citta; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal QR Code -->
    <div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title" id="qrModalLabel">Il tuo buono pasto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div id="qrcode" class="d-inline-block p-2 bg-white border rounded mb-3"></div>
                    <p class="fs-5 mb-1">Scadenza tra: <span id="countdown" class="fw-bold text-primary">05:00</span>
                    </p>
                    <p class="text-muted small mb-0">Mostra questo QR code al tornello</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast alert -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080">
        <div id="alertToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto" id="toastTitle">Notifica</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="toastMessage"></div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('btnGeneraBuono').addEventListener('click', function () {
            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Generazione...';

            fetch('procedure/genera_token.php')
                .then(response => response.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = 'Utilizza Buono';

                    if (data.success) {
                        document.getElementById('qrcode').innerHTML = '';
                        new QRCode(document.getElementById('qrcode'), {
                            text: data.token,
                            width: 200,
                            height: 200,
                            colorDark: '#000000',
                            colorLight: '#ffffff',
                            correctLevel: QRCode.CorrectLevel.H
                        });

                        avviaCountdown(data.scadenza);

                        document.getElementById('qrModalLabel').textContent =
                            data.esistente ? 'Buono già attivo' : 'Il tuo buono pasto';

                        const modal = new bootstrap.Modal(document.getElementById('qrModal'));
                        modal.show();

                        avviaPollingToken(modal);
                    } else {
                        mostraToast('Errore', data.message, 'danger');
                    }
                })
                .catch(error => {
                    btn.disabled = false;
                    btn.innerHTML = 'Utilizza Buono';
                    mostraToast('Errore', 'Errore di connessione', 'danger');
                });
        });

        function avviaCountdown(scadenza) {
            const countdownEl = document.getElementById('countdown');
            const scadenzaDate = new Date(scadenza);

            const interval = setInterval(function () {
                const now = new Date();
                const diff = scadenzaDate - now;

                if (diff <= 0) {
                    clearInterval(interval);
                    countdownEl.textContent = 'SCADUTO';
                    countdownEl.classList.remove('text-primary');
                    countdownEl.classList.add('text-danger');
                    return;
                }

                const minuti = Math.floor(diff / 60000);
                const secondi = Math.floor((diff % 60000) / 1000);

                countdownEl.textContent =
                    String(minuti).padStart(2, '0') + ':' +
                    String(secondi).padStart(2, '0');

                if (diff < 60000) {
                    countdownEl.classList.remove('text-primary');
                    countdownEl.classList.add('text-danger');
                } else {
                    countdownEl.classList.remove('text-danger');
                    countdownEl.classList.add('text-primary');
                }
            }, 1000);

            document.getElementById('qrModal').addEventListener('hidden.bs.modal', function () {
                clearInterval(interval);
            });
        }

        let pollingInterval = null;

        function avviaPollingToken(modal) {
            if (pollingInterval) clearInterval(pollingInterval);

            // Prende il valore attuale dal server come baseline
            fetch('procedure/controlla_token.php')
                .then(response => response.json())
                .then(data => {
                    if (!data.success) return;
                    const pastiIniziali = data.pasti;

                    pollingInterval = setInterval(function () {
                        fetch('procedure/controlla_token.php')
                            .then(response => response.json())
                            .then(data => {
                                if (data.success && data.pasti < pastiIniziali) {
                                    clearInterval(pollingInterval);
                                    pollingInterval = null;

                                    // Aggiorna il numero di pasti nella card
                                    document.querySelector('.display-3.fw-bold.text-primary').textContent = data.pasti;

                                    modal.hide();
                                    mostraToast('Buono utilizzato', 'Il tuo buono pasto è stato validato con successo!', 'success');
                                }
                            })
                            .catch(() => { });
                    }, 2000); //ogni quanti secondi viene eseguita la funzione
                })
                .catch(() => { });

            document.getElementById('qrModal').addEventListener('hidden.bs.modal', function () {
                if (pollingInterval) {
                    clearInterval(pollingInterval);
                    pollingInterval = null;
                }
            }, { once: true });
        }

        function mostraToast(titolo, messaggio, tipo = 'info') {
            document.getElementById('toastTitle').textContent = titolo;
            document.getElementById('toastMessage').textContent = messaggio;

            const toastEl = document.getElementById('alertToast');
            toastEl.classList.remove('bg-danger', 'bg-success', 'bg-warning', 'text-white');

            if (tipo === 'danger') {
                toastEl.classList.add('bg-danger', 'text-white');
            } else if (tipo === 'success') {
                toastEl.classList.add('bg-success', 'text-white');
            }

            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        }
    </script>
</body>

</html>