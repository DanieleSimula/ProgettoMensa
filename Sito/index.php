<!DOCTYPE html>
<html lang="it">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">

  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/custom.css" rel="stylesheet">
  <style>
    .carousel-inner {
      height: 500px;
    }

    .carousel-item img {
      height: 500px;
      object-fit: cover;
      object-position: center;
    }

    .card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
        transform: translateY(-8px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }
  </style>
</head>


<body>
  <?php
  session_start();
  include 'config/connect2DB.php';

  try {
    $stmt = $conn->query("CALL GetNotizie()");
    $notizie = $stmt->fetchAll();

  } catch (PDOException $e) {
    $notizie = [];
  }

  $pageTitle = "Home";
  include 'component/navbar.php';
  ?>




  <div id="carouselTimer" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
    <div class="carousel-inner">
      <div class="carousel-item active">
        <img src="risorse\cibo.jpg" class="d-block w-100" alt="...">
      </div>
      <div class="carousel-item">
        <img src="risorse\servizio.jpg" class="d-block w-100" alt="...">
      </div>
      <div class="carousel-item">
        <img src="risorse\tavoli.png" class="d-block w-100" alt="...">
      </div>
    </div>
  </div>
  <div class="container my-5">

    <?php if (empty($notizie)): ?>
      <div class="alert alert-info text-center">
        <h5>Nessuna notizia al momento</h5>
        <p class="mb-0">Torna a trovarci presto per aggiornamenti!</p>
      </div>

    <?php else: ?>
      <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">

        <?php foreach ($notizie as $notizia): ?>
          <div class="col">
            <div class="card h-100">

              <?php if ($notizia['immagine']): ?>
                <img src="<?= $notizia['immagine'] ?> " class="card-img-top" alt="">
              <?php endif; ?>

              <div class="card-body">
                <h5 class="card-title">
                  <?= $notizia['titolo'] ?>
                </h5>
                <p class="card-text">
                  <?= $notizia['descrizione'] ?>
                </p>
                <a href="notizia.php?id=<?= $notizia['id'] ?>" class="btn btn-primary">Leggi di più</a>
              </div>

              <div class="card-footer text-muted">
                <?= date('d/m/Y', strtotime($notizia['dataPubblicazione'])) ?>
              </div>

            </div>
          </div>
        <?php endforeach; ?>

      </div>
    <?php endif; ?>


  </div>
  </div>

  <?php
  include 'component/footer.php';
  ?>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>

</html>