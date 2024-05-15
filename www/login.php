<?php
    session_start();
    include 'header.html';
    include 'navbar.php';
?>

<body>
        <?php
            if (isset($_POST['disconnect'])) {
                session_unset();
                echo '<script>window.location.replace("login.php");</script>';
            }
            
            $bdd = new PDO('mysql:host=db;dbname=group9;charset=utf8', 'group9', 'tabodi');
            if ($bdd == NULL)
                echo "Problème de connexion";
            if (isset($_POST["login"])) {
                //Prepared statement to avoid sql injection
                $req = $bdd->prepare("SELECT * FROM users WHERE Login = :login AND Pass = :pass", [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
                $req->execute(array('login' => $_POST["login"], 'pass' => $_POST["pass"]));
                $tuple = $req->fetch();
                if ($tuple) {
                    $_SESSION['login'] = $tuple["Login"];
                    echo '<script>window.location.replace("index.php");</script>';
                } else
                    echo "<div class='error-box'>Votre login/mot de passe est incorrect</div>";
            }
        ?>
        <div class="container d-flex flex-column align-items-center card shadow rounded-2 mt-8 mx-auto custom-bg-color p-5 pt-4 mt-4 text-center">
            <?php if (isset($_SESSION['login'])): ?>
                <form method="post" action="login.php">
                    <input type="hidden" name="disconnect" value="yes">
                    <button type="submit" class="btn btn-block custom-btn" style="width: 380px;">Déconnexion</button>
                </form>
            <?php else: ?>
                <h2>Se connecter</h2>
                <form method="post" action="login.php">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" style="width: 350px;" placeholder="Nom d'utilisateur" name="login" required>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control" style="width: 350px;" placeholder="Mot de passe" name="pass" required>
                    </div>
                    
                    <div class="d-grid gap-2 mx-auto">
                        <button type="submit" class="btn btn-block custom-btn" style="width: 380px;">Connexion</button>
                    </div>
                </form>
                
            <?php endif; ?>
        </div>
    </body>
</html>

