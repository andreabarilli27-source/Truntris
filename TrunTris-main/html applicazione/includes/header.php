<?php
// Controlla se la sessione è già avviata
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TrunTris - <?php echo $page_title ?? 'Ottimizzazione Bagagliaio'; ?></title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS Custom -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/responsive.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
</head>
<body>
    <header class="site-header">
        <nav class="navbar">
            <div class="logo-container">
                <img src="img/Logo.png" alt="Logo TrunTris" class="logo">
                <span class="brand-name">TrunTris</span>
            </div>
            
            <div class="nav-links">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="home.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'home.php' ? 'active' : '' ?>">
                        <i class="fas fa-home"></i> Home
                    </a>
                    <a href="InsertSchema.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'InsertSchema.php' ? 'active' : '' ?>">
                        <i class="fas fa-plus-circle"></i> Nuovo Schema
                    </a>
                    <div class="user-menu">
                        <span class="user-greeting">
                            <i class="fas fa-user-circle"></i> 
                            ID: <?php echo htmlspecialchars($_SESSION['user_id']); ?>
                        </span>
                        <a href="logout.php" class="logout-btn">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                <?php else: ?>
                    <a href="Account.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'Account.php' ? 'active' : '' ?>">
                        <i class="fas fa-sign-in-alt"></i> Login/Registrati
                    </a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
    
    <main class="container">