<?php
session_start();
?>
    <?php include 'header.html'; ?>
    <?php include 'navbar.html'; ?>
    <body>
        <?php
        //Retirer les variables de session si on s'est déconnectés
        if (isset($_POST['disconnect'])) {
            session_unset();
        }
        $bdd = new PDO('mysql:host=db;dbname=group9;charset=utf8', 'group9', 'tabodi');
        if ($bdd == NULL)
            echo "Problème de connection";
        if (isset($_POST["login"])) {
            $req = $bdd->query("SELECT * FROM users WHERE Login = '" . $_POST["login"] . "' AND Pass = '" . $_POST["pass"] . "' ");
            $tuple = $req->fetch();
            if ($tuple) {
                $_SESSION['login'] = $tuple["Login"];
            } else
                echo "Votre login/mot de passe est incorrect<br><br>";
        }
        if (isset($_SESSION['login'])) {
            echo "<h1>Bienvenue " . $_SESSION['login'] . "</h1><br>";
            if (isset($_POST['texte']))
                echo "Vous avez écrit : " . $_POST['texte'] . "<br>";
        ?>
    
            <!-- Formulaire pour se déconnecter -->
            <form method="post" action="login.php">
                <p>
                    <input type="hidden" name="disconnect" value="yes">
                    <input type="submit" value="Deconnection">
                </p>
            </form>
            <h2>Entrez un petit texte</h2>
            <form method="post" action="login.php">
                <p>
                    <input type="text" name="texte">
                    <input type="submit" value="Envoyer">
                </p>
            </form>
        <?php
        } else {
        ?>
        <div class="container d-flex flex-column align-items-center shadow rounded-2 mt-8 mx-auto custom-bg-color p-5 pt-4 mt-4">
            <h1>Veuillez entrer vos identifiants</h1>
            <form method="post" action="login.php">
                <p>
                    <input type="text" name="login" required>
                    <input type="password" name="pass" required>
                    <input type="submit" value="Envoyer">
                </p>
            </form>
        </div>
        <?php
        }
        ?>
    </body>
</html>