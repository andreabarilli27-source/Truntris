<!-- filepath: c:\TUTTO QUELLO IMPORTANTE\TrunTris_Desktop\html applicazione\Account.php -->
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

// Gestione del form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'register') {
        // Registrazione
        $nome = $_POST['nome'];
        $cognome = $_POST['cognome'];
        $data_nascita = $_POST['data_nascita'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

        $sql = "INSERT INTO Users (nome, cognome, data_nascita, username, email, password) 
                VALUES ('$nome', '$cognome', '$data_nascita', '$username', '$email', '$password')";
        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Registration successful!');</script>";
        } else {
            echo "<script>alert('Error: " . $conn->error . "');</script>";
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'login') {
        // Login
        $usernameOrEmail = $_POST['username_or_email'];
        $password = $_POST['password'];

        $sql = "SELECT * FROM Users WHERE username='$usernameOrEmail' OR email='$usernameOrEmail'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Avvia la sessione e salva il nome dell'utente
                session_start();
                $_SESSION['user_id'] = $user['ID'];

                // Reindirizza alla home
                header("Location: home.php");
                exit();
            } else {
                echo "<script>alert('Invalid password!');</script>";
            }
        } else {
            echo "<script>alert('User not found!');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration and Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 400px;
            box-sizing: border-box;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        input {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1rem;
        }
        button {
            padding: 10px;
            background-color: #FF9800; /* Colore secondario arancione */
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }
        button:hover {
            background-color: #E68900; /* Arancione più scuro per hover */
        }
        .toggle {
            text-align: center;
            margin-top: 10px;
        }
        .toggle a {
            color: #FF9800; /* Colore secondario arancione */
            text-decoration: none;
            font-size: 0.9rem;
        }
        .toggle a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container" id="form-container">
        <h2>Register</h2>
        <form method="POST" id="register-form">
            <input type="hidden" name="action" value="register">
            <input type="text" name="nome" placeholder="Nome" required>
            <input type="text" name="cognome" placeholder="Cognome" required>
            <input type="date" name="data_nascita" placeholder="Data di Nascita" required>
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Register</button>
        </form>
        <div class="toggle">
            Already have an account? <a href="#" id="show-login">Login</a>
        </div>
    </div>

    <script>
        const formContainer = document.getElementById('form-container');
        const showLogin = document.getElementById('show-login');

        showLogin.addEventListener('click', (e) => {
            e.preventDefault();
            formContainer.innerHTML = `
                <h2>Login</h2>
                <form method="POST" id="login-form">
                    <input type="hidden" name="action" value="login">
                    <input type="text" name="username_or_email" placeholder="Username or Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit">Login</button>
                </form>
                <div class="toggle">
                    Don't have an account? <a href="#" id="show-register">Register</a>
                </div>
            `;
            document.getElementById('show-register').addEventListener('click', (e) => {
                e.preventDefault();
                location.reload();
            });
        });
    </script>
</body>
</html>