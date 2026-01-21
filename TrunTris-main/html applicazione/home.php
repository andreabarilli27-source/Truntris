<?php
session_start();

// Controlla se l'utente è loggato
if (!isset($_SESSION['user_id'])) {
    header("Location: Account.php");
    exit();
}

// Connessione al database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "my_truntris";

$conn = new mysqli($servername, $username, $password, $dbname);

// Controllo connessione
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$schemi = [];
$username = "Utente"; // Default - da modificare se hai il nome utente in sessione
try {
    // Recupera l'ID dell'utente dalla sessione
    $user_id = $_SESSION['user_id'] ?? 0;

    // Recupera gli schemi di impilazione dell'utente
    $sql = "SELECT p.nome, p.bagagliaglio, p.Valigie, p.posizioni, p.data_creazione, p.ultima_modifica, p.id_utente, p.id 
            FROM predefiniti p
            WHERE p.id_utente = $user_id";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $schemi = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $schemi = []; // Nessun risultato trovato
    }
} catch (Exception $e) {
    echo "Errore: " . $e->getMessage();
}

// Imposta il titolo della pagina
$page_title = "Home - I Tuoi Schemi";
require_once 'includes/header.php';
?>

<div class="dashboard">
    <div class="dashboard-header">
        <h1><i class="fas fa-th-large"></i> I Tuoi Schemi</h1>
        
        <div class="stats">
            <div class="stat-card">
                <i class="fas fa-cubes"></i>
                <span class="stat-number"><?= count($schemi) ?></span>
                <span class="stat-label">Schemi Totali</span>
            </div>
        </div>
    </div>
    
    <?php if (count($schemi) > 0): ?>
        <div class="schemas-grid">
            <?php foreach ($schemi as $schema): ?>
                <div class="schema-card fade-in">
                    <div class="schema-header">
                        <h3><i class="fas fa-box"></i> <?= htmlspecialchars($schema['nome']) ?></h3>
                        <span class="schema-date">
                            <i class="far fa-calendar"></i> 
                            <?= date('d/m/Y', strtotime($schema['data_creazione'])) ?>
                        </span>
                    </div>
                    
                    <div class="schema-details">
                        <p><i class="fas fa-ruler-combined"></i> Bagagliaio: <?= htmlspecialchars($schema['bagagliaglio']) ?></p>
                        <p><i class="fas fa-clock"></i> Modificato: <?= htmlspecialchars($schema['ultima_modifica']) ?></p>
                    </div>
                    
                    <div class="schema-actions">
                        <a href="visual3d.php?id=<?= $schema['id'] ?>" class="btn btn-primary">
                            <i class="fas fa-eye"></i> Visualizza
                        </a>
                        <form method="POST" action="deleteSchema.php" 
                              onsubmit="return confirmDelete()" 
                              style="display: inline;">
                            <input type="hidden" name="schema_id" value="<?= $schema['id'] ?>">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Elimina
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="card text-center" style="max-width: 600px; margin: 40px auto;">
            <div style="padding: 40px;">
                <i class="fas fa-inbox" style="font-size: 4rem; color: var(--gray-300); margin-bottom: 20px;"></i>
                <h3 style="color: var(--gray-600); margin-bottom: 15px;">Nessuno Schema Creato</h3>
                <p style="color: var(--gray-500); margin-bottom: 25px;">
                    Non hai ancora creato nessuno schema di impilazione.<br>
                    Crea il tuo primo schema per ottimizzare il bagagliaio!
                </p>
                <a href="InsertSchema.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus-circle"></i> Crea Primo Schema
                </a>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="text-center mt-3">
        <a href="InsertSchema.php" class="btn btn-success">
            <i class="fas fa-plus-circle"></i> Crea Nuovo Schema
        </a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>