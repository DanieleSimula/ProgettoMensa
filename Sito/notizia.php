<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Notizia</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.0/font/bootstrap-icons.min.css"rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
    <style>
        img {
            height: 500px;
            object-fit: cover;
            object-position: center;
        }
    </style>
</head>

<body>
    <?php
        include 'config/connect2DB.php';
        include 'component/navbar.php';

        $id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

        $notizie = [];

        try {
            $stmt = $conn->prepare("CALL GetNotiziaById(:id)");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $notizie = $stmt->fetchAll();
        } catch (PDOException $e) {
            $notizie = [];
        }

    ?>

    <?php if (empty($notizie) || !$notizie[0]["attiva"]): ?>
        <div class="container-lg my-5">
            <!-- Notizia non trovata -->
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-danger text-center" role="alert">
                        <div class="mb-3">
                            <i class="bi bi-exclamation-triangle" style="font-size: 3rem;"></i>
                        </div>
                        <h4 class="alert-heading">Notizia non trovata</h4>
                        <p class="mb-3">La notizia che stai cercando non esiste o è stata rimossa.</p>
                        <hr>
                        <a href="index.php" class="btn btn-outline-danger">
                            <i class="bi bi-arrow-left"></i> Torna alle notizie
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php if($notizie[0]['immagine']): ?>
        <div class="position-relative overflow-hidden">
            <img src="<?= htmlspecialchars($notizie[0]['immagine']) ?>" alt="<?= htmlspecialchars($notizie[0]['titolo']) ?>"
                class="img-fluid w-100" style="max-height: 500px; object-fit: cover;">
        </div>
        <?php endif?>
        <div class="container-lg my-5">
            <!-- Contenuto della notizia -->
            <div class="row mb-5 border p-5 shadow" >
                <div class="col-lg-8 mx-auto">
                    <!-- Titolo principale -->
                    <h1 class="display-4 fw-bold mb-3 text-dark">
                        <?= htmlspecialchars($notizie[0]['titolo']) ?>
                    </h1>

                    <!-- Sottotitolo/Descrizione -->
                    <p class="fs-5 text-secondary fw-normal mb-4 lead">
                        <?= htmlspecialchars($notizie[0]['descrizione']) ?>
                    </p>

                    <!-- Divisore -->
                    <hr class="my-4">

                    <!-- Info autore e data -->
                    <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
                        <div>
                            <p class="mb-0">
                                <strong>Autore:</strong>
                                <span class="text-primary fw-bold">
                                    <?= htmlspecialchars($notizie[0]['autore']) ?>
                                </span>
                            </p>
                        </div>
                        <?php if (!empty($notizie[0]['dataPubblicazione'])): ?>
                            <div class="text-muted small">
                                <i class="bi bi-calendar-event"></i>
                                <?= date('d M Y', strtotime($notizie[0]['dataPubblicazione'])) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Contenuto principale -->
                    <article class="lh-lg mb-5">
                        <p class="fs-6" style="text-align: justify;">
                            <?= nl2br($notizie[0]['contenuto']) ?>
                        </p>
                    </article>

                    <!-- Pulsanti di azione -->
                    <div class="d-flex gap-2">
                        <a href="javascript:history.back()" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Indietro
                        </a>
                        <button class="btn btn-primary" id="shareBtn" onclick="condividi()">
                            <i class="bi bi-link-45deg"></i> Copia il link
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>


    <?php include 'component/footer.php'; ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        function condividi() {
            const url = window.location.href;

            // Copia negli appunti
            navigator.clipboard.writeText(url).then(() => {


                // Cambia icona del pulsante
                const shareBtn = document.getElementById('shareBtn');
                const originalHTML = shareBtn.innerHTML;
                shareBtn.innerHTML = '<i class="bi bi-check-lg"></i> Link copiato!';
                shareBtn.disabled = true;

                // Ripristina dopo 2 secondi
                setTimeout(() => {
                    shareBtn.innerHTML = originalHTML;
                    shareBtn.disabled = false;
                }, 2000);
            }).catch(() => {
                alert('Errore nella copia del link');
            });
        }
    </script>
</body>

</html>