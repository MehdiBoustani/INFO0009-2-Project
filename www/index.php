<?php
session_start();
include 'header.html';
include 'navbar.php';
?>
<body>
    <div class="container d-flex flex-column align-items-center shadow rounded-2 mt-8 mx-auto custom-bg-color p-5 pt-4 mt-4">
        <?php if (isset($_SESSION['login'])): ?>
            <h1>Bienvenue <?php echo $_SESSION['login']; ?></h1>
            <h2>Entrez un petit texte</h2>
            <form method="post" action="login.php">
                <p>
                    <input type="text" class="form-control" name="texte">
                    <button type="submit" class="btn btn-block btn-primary" style="margin-top: 10px;"> Envoyer</button>
                    <?php if (isset($_POST['texte'])): ?>
                        <p>Vous avez écrit : <?php echo htmlentities($_POST['texte']) . "<br>" ?></p>
                    <?php endif; ?>
                </p>
            </form>
        <?php else: ?>
            <img src="letter-seal.png" alt="Taskmaster image">
            <h1>Bienvenue sur Taskmaster</h1>
        <?php endif; ?>
    </div>
</body>

