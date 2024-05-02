<?php
    session_start();
    include 'header.html';
    include 'navbar.php';
?>

<body>
    <div class="container d-flex flex-column align-items-center shadow rounded-2 mt-8 mx-auto custom-bg-color p-5 pt-4 mt-4">
        <img src="letter-seal.png" alt="Taskmaster image">
        <?php if (isset($_SESSION['login'])): ?>
            <h1>Bienvenue <?php echo $_SESSION['login']; ?> !</h1>

        <?php else: ?>
            <h1>Bienvenue sur Taskmaster</h1>
            <p>Veuillez vous connecter pour accéder à des outils permettant de manipuler la base de données.</p>
        <?php endif; ?>
    </div>
</body>

