<?php
$current_page = 'menu';
require_once 'includes/session_check.php';

// Procedura per ottenere tutti i piatti
$piatti = [];
try {
    $stmt = $conn->prepare("CALL get_piatti()");
    $stmt->execute();
    $piatti = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
} catch (Exception $e) {
    $piatti = [];
}

// Procedura per lista menu
$elenco_menu = [];
try {
    $stmt = $conn->prepare("CALL get_elenco_menu()");
    $stmt->execute();
    $elenco_menu = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
} catch (Exception $e) {
    $elenco_menu = [];
}

// Procedura per menu del giorno programmati
$menu_programmati = [];
try {
    $stmt = $conn->prepare("CALL get_menu_programmati()");
    $stmt->execute();
    $menu_programmati = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
} catch (Exception $e) {
    $menu_programmati = [];
}

// Riuso elenco_menu per la select
$menu_list = $elenco_menu;

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>
<div class="col-md-9 col-lg-10 main-content">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col">
            <h1>Benvenuto <?php echo htmlspecialchars($user_role); ?></h1>
        </div>
    </div>

    <!-- Sezione Menu -->
    <div class="row">
        <!-- Form Creazione Nuovo Menu -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Crea Nuovo Menu</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="..\procedure\aggiungi_menu.php">
                        <div class="mb-3">
                            <label class="form-label">Nome Menu *</label>
                            <input type="text" class="form-control" name="nome_menu" autocomplete="off"required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Piatti</label>
                            <input type="text" class="form-control" placeholder="Cerca piatto..."
                                id="search-piatti">
                            <div class="list-group mt-1" id="results-piatti"
                                style="max-height:200px; overflow-y:auto;"></div>
                            <div class="mt-2" id="selected-piatti"></div>
                        </div>

                        <button type="submit" class="btn btn-success w-100">
                            Crea Menu
                        </button>
                    </form>
                </div>
            </div>

            <!-- Form Assegna Menu del Giorno -->
            <div class="card mt-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Assegna Menu del Giorno</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="..\procedure\assegna_menu_giorno.php">
                        <div class="mb-3">
                            <label for="data-menu" class="form-label">Data *</label>
                            <input type="date" class="form-control" id="data-menu" name="data_menu" required>
                        </div>
                        <div class="mb-3">
                            <label for="tipo-pasto" class="form-label">Tipo Pasto *</label>
                            <select class="form-select" id="tipo-pasto" name="tipo_pasto" required>
                                <option value="">Seleziona tipo</option>
                                <option value="0">Pranzo</option>
                                <option value="1">Cena</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="menu-id" class="form-label">Menu *</label>
                            <select class="form-select" id="menu-id" name="menu_id" required>
                                <option value="">Seleziona menu</option>
                                <?php foreach ($menu_list as $menu): ?>
                                    <option value="<?php echo htmlspecialchars($menu['id']); ?>">
                                        <?php echo htmlspecialchars($menu['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-calendar-plus me-2"></i>Assegna Menu
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Lista Menu -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Lista Menu</h5>
                </div>
                <div class="card-body">
                    <?php if (count($elenco_menu) > 0): ?>
                        <table class="table table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Nome Menu</th>
                                    <th>Azioni</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($elenco_menu as $menu): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($menu['id']); ?></td>
                                        <td><?php echo htmlspecialchars($menu['nome']); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalMenu<?php echo $menu['id']; ?>">
                                                Dettagli
                                            </button>
                                            <form method="POST" action="..\procedure\elimina_menu.php"
                                                style="display: inline;">
                                                <input type="hidden" name="menu_id"
                                                    value="<?php echo $menu['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Sei sicuro di voler eliminare questo menu?')">
                                                    Elimina
                                                </button>
                                            </form>
                                        </td>
                                    </tr>

                                    <!-- Modal per dettagli menu -->
                                    <div class="modal fade" id="modalMenu<?php echo $menu['id']; ?>" >
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">
                                                        Piatti del Menu: <?php echo htmlspecialchars($menu['nome']); ?>
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <?php
                                                    $stmt_piatti = $conn->prepare("CALL get_piatti_menu(:menu_id)");
                                                    $stmt_piatti->bindParam(':menu_id', $menu['id'], PDO::PARAM_INT);
                                                    $stmt_piatti->execute();
                                                    $piatti_menu = $stmt_piatti->fetchAll(PDO::FETCH_ASSOC);
                                                    $stmt_piatti->closeCursor();

                                                    if (count($piatti_menu) > 0):
                                                        ?>
                                                        <ul class="list-group">
                                                            <?php foreach ($piatti_menu as $piatto): ?>
                                                                <li class="list-group-item">
                                                                    <strong><?php echo htmlspecialchars($piatto['nome']); ?></strong><br>
                                                                    <small class="text-muted">
                                                                        <?php echo htmlspecialchars($piatto['descrizione']); ?>
                                                                    </small>
                                                                </li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    <?php else: ?>
                                                        <p class="text-muted">Nessun piatto associato a questo menu</p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-center text-muted">Nessun menu disponibile</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Menu del Giorno Programmati -->
            <div class="card mt-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-calendar3 me-2"></i>Menu del Giorno Programmati</h5>
                </div>
                <div class="card-body">
                    <?php if (count($menu_programmati) > 0): ?>
                        <table class="table table-sm table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Data</th>
                                    <th>Pasto</th>
                                    <th>Menu</th>
                                    <th>Azioni</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($menu_programmati as $mp): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($mp['data'])); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $mp['pranzo_cena'] == 0 ? 'info' : 'warning'; ?>">
                                                <?php echo $mp['pranzo_cena'] == 0 ? 'Pranzo' : 'Cena'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($mp['menu_nome']); ?></td>
                                        <td>
                                            <form method="POST" action="..\procedure\elimina_menu_giorno.php"
                                                style="display: inline;">
                                                <input type="hidden" name="data_menu" value="<?php echo $mp['data']; ?>">
                                                <input type="hidden" name="pranzo_cena" value="<?php echo $mp['pranzo_cena']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Rimuovere questo menu del giorno?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-center text-muted">Nessun menu programmato</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
<script>
    // Passa array piatti a JavaScript
    const piatti = <?= json_encode($piatti, JSON_HEX_TAG) ?>;
</script>
<script src="assets/js/menu_search.js"></script>
