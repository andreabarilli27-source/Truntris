<!-- filepath: c:\TUTTO QUELLO IMPORTANTE\TrunTris_Desktop\html applicazione\predefiniti.php -->
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
try {
    // Recupera l'ID dell'utente dalla sessione (da implementare in fase di login)
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

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I tuoi schemi di impilazione</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        h1 {
            font-size: 2rem;
            margin-bottom: 20px;
        }
        .schemi-list {
            width: 90%;
            max-width: 600px;
            margin: 20px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .schemi-list h2 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }
        .schema-item {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        .schema-item:hover {
            background-color: #f5f5f5;
        }
        .schema-item:last-child {
            border-bottom: none;
        }
        .schema-item h3 {
            margin: 0;
            font-size: 1.2rem;
        }
        .schema-item p {
            margin: 5px 0;
            font-size: 0.9rem;
            color: #666;
        }
        .empty-message {
            text-align: center;
            font-size: 1.2rem;
            color: #666;
        }
        .add-schema-btn {
            padding: 10px 20px;
            font-size: 1rem;
            color: #ffffff;
            background-color: #FF9800;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 20px;
        }
        .add-schema-btn:hover {
            background-color: #E68900;
        }
    </style>
</head>
<body>
    <h1>I tuoi schemi di impilazione</h1>
    <?php if (count($schemi) > 0): ?>
        <div class="schemi-list">
            <h2>Lista degli schemi</h2>
            <?php foreach ($schemi as $schema): ?>
                <a href="visualizzaSchema.php?id=<?php echo htmlspecialchars($schema['id']); ?>" style="text-decoration: none; color: inherit;">
                    <div class="schema-item">
                        <h3><?php echo htmlspecialchars($schema['nome']); ?></h3>
                        <p><small>Creato il: <?php echo htmlspecialchars($schema['data_creazione']); ?></small></p>
                    </div>
                </a>
            <?php endforeach; ?>
            <button class="add-schema-btn" onclick="location.href='InsertSchema.php'">Crea un nuovo schema</button>
        </div>
    <?php else: ?>
        <div class="empty-message">
            <p>Non hai ancora creato nessuno schema di impilazione.</p>
            <button class="add-schema-btn" onclick="location.href='InsertSchema.php'">Crea un nuovo schema</button>
        </div>
    <?php endif; ?>
</body>
</html>