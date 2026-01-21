<!-- filepath: c:\TUTTO QUELLO IMPORTANTE\TrunTris_Desktop\html applicazione\home.php -->
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TrunTris - Home</title>
    <style>
        body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      background-color: #ffffff;
      color: #333;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: space-between;
      height: 100vh;
        }

        header {
            width: 100%;
            background-color: #ffffff;
            color: #FF9800;
            padding: 20px;
            text-align: center;
            box-sizing: border-box;
        }

        header h1 {
            margin: 0;
            font-size: 2rem;
        }

        .menu {
            display: flex;
            flex-direction: column;
            width: 100%;
            text-align:center;
            }

        .menu button {
            padding: 10px;
            font-size: 1rem;
            color: #FF9800;
            background-color: #ffffff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
        }

        .menu button:hover {
            background-color: #FF9800;
            color: #ffffff;
        }

        h1 {
            text-align: center;
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
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        .logout {
            position: absolute;
            top: 10px;
            right: 30px;
        }

        .logo {
            position: absolute;
            top: 5px;
            left: 0px;
        }

        .delete-btn {
            background-color: #ff4444;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 5px 10px;
            cursor: pointer;
            margin-left: 10px;
        }

        .delete-btn:hover {
            background-color: #cc0000;
        }

        .schema-info {
            flex-grow: 1;
        }
    </style>
</head>
<body>
    <div class="logo">
        <img src="img/Logo.png" alt="Logo TrunTris" style="width: 100px; height: auto;">
    </div>
    <header>
        <h1>TrunTris</h1>
    </header>
    <div class="logout" onclick="location.href='WorkInProgress.html'">
        <a href="logout.php"><img src="img/logout.png" alt="Logout" style="width: 50px; height: auto;"> </a>
    </div>
    <main>
        <h1>I tuoi schemi di impilazione</h1>
    <?php if (count($schemi) > 0): ?>
        <div class="schemi-list">
            <h2>Lista degli schemi</h2>
            <?php foreach ($schemi as $schema): ?>
                <div class="schema-item">
                    <div class="schema-info" onclick="location.href='visual3d.php?id=<?php echo htmlspecialchars($schema['id']); ?>'" style="cursor: pointer;">
                        <h3><?php echo htmlspecialchars($schema['nome']); ?></h3>
                        <p><small>Creato il: <?php echo htmlspecialchars($schema['data_creazione']); ?></small></p>
                    </div>
                    <form action="deleteSchema.php" method="POST" style="display: inline;" onsubmit="return confirm('Sei sicuro di voler eliminare questo schema?');">
                        <input type="hidden" name="schema_id" value="<?php echo htmlspecialchars($schema['id']); ?>">
                        <button type="submit" class="delete-btn">Elimina</button>
                    </form>
                </div>
            <?php endforeach; ?>
            <button class="add-schema-btn" onclick="location.href='InsertSchema.php'">Crea un nuovo schema</button>
        </div>
    <?php else: ?>
        <div class="empty-message">
            <p>Non hai ancora creato nessuno schema di impilazione.</p>
            <button class="add-schema-btn" onclick="location.href='InsertSchema.php'">Crea un nuovo schema</button>
        </div>
    <?php endif; ?>
    </main>


</body>
</html>