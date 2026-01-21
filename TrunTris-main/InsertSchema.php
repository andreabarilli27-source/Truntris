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
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inserisci Nuovo Schema</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: #fff;
            padding: 15px;  /* Reduced from 20px */
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 95%;  /* Adjusted width */
            max-width: 450px;  /* Increased slightly to accommodate the inputs */
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        input, textarea {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1rem;
        }
        button {
            padding: 8px;  /* Reduced from 10px */
            background-color: #FF9800;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }
        button:hover {
            background-color: #E68900;
        }

        .back-button {
            width: 100%;
            text-align: center;
            margin-top: 20px;
            display: inline-block;
            padding: 10px 0px;
            background-color: #FF9800;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .back-button:hover {
            background-color: #E68900;
        }

        .add-button {
            margin-bottom: 15px;
        }

        .input-group {
            display: flex;
            align-items: center;
            gap: 5px;  /* Reduced from 10px */
            margin-bottom: 10px;
            width: 100%;
        }

        .input-group input {
            flex: 1;
            width: 60px;  /* Set a fixed width for number inputs */
            padding: 8px;  /* Reduced from 10px */
            margin-bottom: 0;
            font-size: 0.9rem;  /* Slightly smaller font */
        }

        .remove-valigia {
            background-color: #FF0000;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            padding: 4px 8px;  /* Reduced padding */
            font-size: 1rem;
            min-width: 30px;  /* Ensure consistent width */
        }

        .remove-valigia:hover {
            background-color: #CC0000;
        }

        .valigia-group {
            width: 100%;
            margin-bottom: 5px;  /* Reduced from 10px */
        }
    </style>
    <script>
        function addValigia() {
            const container = document.getElementById('valigie-container');
            const newGroup = document.createElement('div');
            newGroup.className = 'valigia-group';
            newGroup.innerHTML = `
                <div class="input-group">
                    <input type="number" name="valigie_array[]" placeholder="Larghezza" required min="1">
                    <input type="number" name="valigie_array[]" placeholder="Altezza" required min="1">
                    <input type="number" name="valigie_array[]" placeholder="Profondità" required min="1">
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
</head>
<body>
    <div class="container">
        <h1>Inserisci Nuovo Schema</h1>
        <form onsubmit="handleSubmit(event)" method="post">
            <input type="text" name="nome_schema" placeholder="Nome dello schema" required>
            <input type="text" name="dimensioni_bagagliaio" placeholder="Dimensioni del bagagliaio (es. 100x80x50 cm)" required>
            
            <div id="valigie-container">
                <div class="valigia-group">
                    <div class="input-group">
                        <input type="number" name="valigie_array[]" placeholder="Larghezza" required min="1">
                        <input type="number" name="valigie_array[]" placeholder="Altezza" required min="1">
                        <input type="number" name="valigie_array[]" placeholder="Profondità" required min="1">
                        <button type="button" class="remove-valigia" onclick="removeValigia(this)" style="display:none;">×</button>
                    </div>
                </div>
            </div>
            
            <button type="button" onclick="addValigia()" class="add-button">+ Aggiungi Valigia</button>
            <input type="hidden" name="id_user" value="<?php echo $user; ?>">
            <button type="submit">Salva Schema</button>
        </form>
        <a href="home.php" class="back-button">← Torna agli schemi</a>
    </div>
</body>
</html>