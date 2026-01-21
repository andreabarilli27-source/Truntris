<?php
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

if (!empty($_POST)) {
    $nome_schema = $_POST['nome_schema'];
    $dimensioni_bagagliaio = $_POST['dimensioni_bagagliaio'];
    $user = $_POST['user'];
    $data = json_decode($_POST['data'], true);
    if ($data) {
        $valigie = "";
        $posizioni = "";

        // Concatena i dati delle valigie e delle posizioni
        foreach ($data['valigie_inserite'] as $valigia) {
            $valigie .= implode(",", $valigia) . " ";
        }
        foreach ($data['posizioni'] as $posizione) {
            $posizioni .= implode(",", $posizione) . " ";
        }

        // Inserisci i dati nel database
        $sql = "INSERT INTO Predefiniti (nome, bagagliaglio, Valigie, posizioni, id_utente) 
                VALUES ('$nome_schema', '$dimensioni_bagagliaio', '$valigie', '$posizioni', '$user')";

        if ($conn->query($sql) === TRUE) {
            // Reindirizza alla pagina home.php
            header("Location: home.php");
            exit();
        } else {
            echo "Errore durante l'inserimento dei dati: " . $conn->error;
        }
    } else {
        echo "<script>alert('Errore: Nessun dato ricevuto dal server Python.');</script>";
    }
} else {
    echo "<script>alert('Errore: Nessun dato ricevuto.');</script>";
}
?>