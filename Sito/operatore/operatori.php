<?php
$current_page = 'operatori';
require_once 'includes/session_check.php';

// Admin check aggiuntivo per questa pagina
if ($user_role !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

// Gestione ricerca operatori
$elenco_operatori = [];
$messaggio_operatori = '';
try {
    $query = "CALL get_operatori()";
    $stmt = $conn->prepare($query);
    if ($stmt->execute()) {
        $elenco_operatori = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($elenco_operatori) == 0) {
            $messaggio_operatori = '<div class="alert alert-warning">Nessun operatore trovato con i criteri di ricerca specificati.</div>';
        } else {
            $messaggio_operatori = '<div class="alert alert-success">Trovati ' . count($elenco_operatori) . ' operatore/i.</div>';
        }
    } else {
        $messaggio_operatori = '<div class="alert alert-danger">Errore durante l\'esecuzione della ricerca.</div>';
    }
    $stmt->closeCursor();
    $stmt = null;
} catch (Exception $e) {
    $elenco_operatori = [];
}

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

    <!-- Sezione Operatori -->
    <div class="row">
        <!-- Form Inserimento Operatore -->
        <div class="col-lg-5 mb-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Aggiungi Operatore</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="..\procedure\aggiungi_operatore.php" style="display: inline;">
                        <div class="mb-3">
                            <label for="nome-operatore" class="form-label">Nome Operatore</label>
                            <input type="text" class="form-control" id="nome-operatore"
                                name="nome_operatore" maxlength="30" required>
                            <small class="form-text text-muted">Massimo 30 caratteri</small>
                        </div>
                        <div class="mb-3">
                            <label for="email-operatore" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email-operatore"
                                name="email_operatore" maxlength="30" required>
                            <small class="form-text text-muted">Email dell'operatore</small>
                        </div>
                        <div class="mb-3">
                            <label for="password-operatore" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password-operatore"
                                name="password_operatore" maxlength="30" required>
                            <small class="form-text text-muted">Password dell'operatore</small>
                        </div>
                        <input type="hidden" name="action" value="aggiungi_operatore">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-circle me-2"></i>Aggiungi operatore
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Form Lista Operatori -->
        <div id="operatori-table-container" class="col-lg-7 table-responsive">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Lista Operatori</h5>
                </div>
                <?php if ($elenco_operatori && count($elenco_operatori) > 0): ?>
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Nome operatore</th>
                                <th>Email</th>
                                <th>Elimina</th>
                            </tr>
                        </thead>
                        <tbody id="operatori-table-body">
                            <?php foreach ($elenco_operatori as $row2): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row2['nomeutente']); ?></td>
                                    <td><?php echo htmlspecialchars($row2['email']); ?></td>
                                    <td>
                                        <form method="POST" action="..\procedure\elimina_operatore.php"
                                            style="display: inline;">
                                            <input type="hidden" name="id_operatore"
                                                value="<?php echo $row2['nomeutente']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Elimina</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-center text-muted">Nessun operatore trovato</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
