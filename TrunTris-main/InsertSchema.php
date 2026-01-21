<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: Account.php");
    exit();
}
$user = $_SESSION['user_id'];

$page_title = "Crea Nuovo Schema";
require_once 'includes/header.php';
?>

<div class="form-container">
    <h1 class="form-title"><i class="fas fa-plus-circle"></i> Crea Nuovo Schema</h1>
    
    <form onsubmit="handleSubmit(event)" method="post">
        <div class="form-group">
            <input type="text" name="nome_schema" placeholder="Nome dello schema" 
                   class="form-control" required>
        </div>
        
        <div class="form-group">
            <input type="text" name="dimensioni_bagagliaio" 
                   placeholder="Dimensioni del bagagliaio (es. 100x80x50 cm)" 
                   class="form-control" required>
        </div>
        
        <div class="form-group">
            <label class="form-label">Valigie:</label>
            <div id="valigie-container">
                <div class="valigia-group">
                    <div class="input-group">
                        <input type="number" name="valigie_array[]" placeholder="Larghezza" 
                               class="form-control" required min="1">
                        <input type="number" name="valigie_array[]" placeholder="Altezza" 
                               class="form-control" required min="1">
                        <input type="number" name="valigie_array[]" placeholder="Profondità" 
                               class="form-control" required min="1">
                        <button type="button" class="remove-valigia" 
                                onclick="removeValigia(this)" style="display:none;">×</button>
                    </div>
                </div>
            </div>
            
            <button type="button" onclick="addValigia()" class="btn btn-outline mt-2">
                <i class="fas fa-plus"></i> Aggiungi Valigia
            </button>
        </div>
        
        <input type="hidden" name="id_user" value="<?php echo $user; ?>">
        
        <button type="submit" class="btn btn-primary btn-block">
            <i class="fas fa-save"></i> Salva Schema
        </button>
    </form>
    
    <div class="text-center mt-3">
        <a href="home.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Torna agli schemi
        </a>
    </div>
</div>

<!-- Mantieni lo stesso JavaScript -->
<script>
    // ... mantieni il tuo JavaScript esistente ...
</script>

<?php require_once 'includes/footer.php'; ?>
