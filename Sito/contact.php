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

<body>
    <?php
    session_start();
    $pageTitle = 'Contatti';
    include 'component/navbar.php';
    ?>

    <div class="p-5 mb-4 bg-primary">
        <div class="p-5 mb-4 rounded-3">
            <div class="container-fluid py-5">
                <h1 class="display-5 fw-bold text-white">I nostri contatti:</h1>
            </div>
        </div>
    </div>


    <div class="container">
        <div class="row justify-content-center align-items-center g-3 g-md-5 mb-5">

            <div class="col-12 col-md-4">
                <div class="card h-100 bg-dark">
                    <div class="card-body">
                        <h4 class="card-title text-white"><i class="bi bi-geo-alt-fill"></i> Indirizzo</h4>
                        <div class="input-group">
                            <input type="text" class="form-control" value="Via del tutto eccezionale" readonly>
                            <button class="btn btn-outline-secondary" type="button" onclick="copyText(this)">
                                <i class="bi bi-clipboard-fill"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="card h-100 bg-dark">
                    <div class="card-body">
                        <h4 class="card-title text-white"><i class="bi bi-telephone-fill"></i> Telefono</h4>
                        <div class="input-group">
                            <input type="text" class="form-control" value="079 123456" readonly>
                            <button class="btn btn-outline-secondary" type="button" onclick="copyText(this)">
                                <i class="bi bi-clipboard-fill"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="card h-100 bg-dark">
                    <div class="card-body">
                        <h4 class="card-title text-white"><i class="bi bi-envelope-at-fill"></i> Mail</h4>
                        <div class="input-group">
                            <input type="text" class="form-control" value="mensachevorrei@info.com" readonly>
                            <button class="btn btn-outline-secondary" type="button" onclick="copyText(this)">
                                <i class="bi bi-clipboard-fill"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center g-5">
            <div class="col-12 mb-4">
                <div class="rounded shadow overflow-hidden" style="height: 40vh;">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1200.9341529711066!2d8.550410643191126!3d40.72212843443487!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x12dc6160732e8219%3A0x917fb5e8f6c3b271!2sOrto%20Botanico%20-%20Universit%C3%A0%20degli%20Studi%20di%20Sassari!5e1!3m2!1sit!2sit!4v1769522300390!5m2!1sit!2sit"
                        style="width: 100%; height: 100%; border: 0;" allowfullscreen="" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </div>

    </div>




    <script>
        function copyText(button) {
            const input = button.parentElement.querySelector('input');
            navigator.clipboard.writeText(input.value);

            const icon = button.querySelector('i');
            icon.className = 'bi bi-clipboard-check-fill';
            setTimeout(() => {
                icon.className = 'bi bi-clipboard-fill';
            }, 1500);
        }
    </script>




    <?php
    include 'component/footer.php';
    ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>

</html>