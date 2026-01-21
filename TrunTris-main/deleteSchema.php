<?php
session_start();

// Controlla se l'utente è loggato
if (!isset($_SESSION['user_id'])) {
    header("Location: Account.php");
    exit();
}

if (isset($_POST['schema_id'])) {
    // Connessione al database
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "my_truntris";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $schema_id = $_POST['schema_id'];
    $user_id = $_SESSION['user_id'];

    // Elimina lo schema solo se appartiene all'utente corrente
    $sql = "DELETE FROM predefiniti WHERE id = ? AND id_utente = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $schema_id, $user_id);
    
    if ($stmt->execute()) {
        header("Location: home.php?deleted=true");
    } else {
        header("Location: home.php?error=true");
    }

    $conn->close();
} else {
    header("Location: home.php");
}
?>