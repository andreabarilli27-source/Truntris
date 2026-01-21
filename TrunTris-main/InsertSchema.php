<?php
session_start();
// Controlla se l'utente è loggato
if (!isset($_SESSION['user_id'])) {
    // Reindirizza alla pagina di login se non loggato
    header("Location: Account.php");
    exit();
}
// Recupera il nome dell'utente dalla sessione
$user = $_SESSION['user_id'];

$page_title = "Inserisci Nuovo Schema";
require_once 'includes/header.php';
?>

<div class="form-container">
    <h1 class="form-title"><i class="fas fa-plus-circle"></i> Inserisci Nuovo Schema</h1>
    
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
            <label class="form-label">Valigie (larghezza, altezza, profondità):</label>
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

<script>
    function addValigia() {
        const container = document.getElementById('valigie-container');
        const newGroup = document.createElement('div');
        newGroup.className = 'valigia-group';
        newGroup.innerHTML = `
            <div class="input-group">
                <input type="number" name="valigie_array[]" placeholder="Larghezza" required min="1" class="form-control">
                <input type="number" name="valigie_array[]" placeholder="Altezza" required min="1" class="form-control">
                <input type="number" name="valigie_array[]" placeholder="Profondità" required min="1" class="form-control">
                <button type="button" class="remove-valigia" onclick="removeValigia(this)">×</button>
            </div>
        `;
        container.appendChild(newGroup);
        
        // Show all remove buttons if there's more than one group
        const removeButtons = document.querySelectorAll('.remove-valigia');
        if (removeButtons.length > 1) {
            removeButtons.forEach(button => button.style.display = 'block');
        }
    }

    function removeValigia(button) {
        const groupToRemove = button.closest('.valigia-group');
        const container = document.getElementById('valigie-container');
        container.removeChild(groupToRemove);
        
        // Hide the last remaining remove button if only one group left
        const removeButtons = document.querySelectorAll('.remove-valigia');
        if (removeButtons.length === 1) {
            removeButtons[0].style.display = 'none';
        }
    }

    function handleSubmit(event) {
        event.preventDefault();
        
        // Raccogli tutti i valori delle valigie
        const valigie = [];
        const inputs = document.querySelectorAll('input[name="valigie_array[]"]');
        inputs.forEach(input => {
            valigie.push(parseInt(input.value));
        });

        // Crea una stringa di valigie nel formato richiesto (x,y,z,x,y,z,...)
        const valigie_string = valigie.join(',');
        
        // Ottieni le altre informazioni dal form
        const nome_schema = event.target.nome_schema.value;
        const dimensioni_bagagliaio = event.target.dimensioni_bagagliaio.value;
        const id_user = document.querySelector('input[name="id_user"]').value;

        // Crea un form nascosto per inviare i dati
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'visual3d.php';

        // Aggiungi i campi nascosti
        const fields = {
            'nome_schema': nome_schema,
            'trunk_size': dimensioni_bagagliaio,
            'valigie': valigie_string,
            'id_user': id_user
        };

        for (const [key, value] of Object.entries(fields)) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            form.appendChild(input);
        }

        // Aggiungi il form al documento e invialo
        document.body.appendChild(form);
        form.submit();
    }
</script>

<?php require_once 'includes/footer.php'; ?>
