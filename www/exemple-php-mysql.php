<?php
session_start();
include 'header.html';
include 'navbar.php';
?>
    <!-- Connexion a la base de données -->
    <?php
    $bdd = new PDO('mysql:host=db;dbname=group9;charset=utf8', 'group9', 'tabodi');
    ?>
    <body>
        <div class="container d-flex flex-column align-items-center shadow rounded-2 mt-8 mx-auto custom-bg-color p-5 pt-4 mt-4">
            <h1>Utilisateurs</h1>
            <?php
            /*$req contient les tuples de la requête*/
            $req = $bdd->query('SELECT * FROM users');
            /*On affiche tous les résultats de la requête*/
            while ($tuple = $req->fetch()) {
                echo "<p>" . $tuple['Login'] . " " . $tuple['Pass'] . "</p>";
            }
            ?>
        </div>
    </body>
</html>