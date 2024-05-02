<?php
    session_start();
    include 'header.html';
    include 'navbar.php';
?>

<body>
        <?php
            $bdd = new PDO('mysql:host=db;dbname=group9;charset=utf8', 'group9', 'tabodi');
        ?>
        <div class="container d-flex flex-column align-items-center shadow rounded-2 mt-8 mx-auto custom-bg-color p-5 pt-4 mt-4">
            <h1>Sélectionner des personnes</h1>
            <form method="post" action="tables.php">
                <p>
                <div class="input-group mb-3">
                        <input type="text" class="form-control" style="width: 350px;" placeholder="Prénom" name="firstname">
                    </div>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" style="width: 350px;" placeholder="Nom de famille" name="lastname">
                    </div>
                    <div class="d-grid gap-2 mx-auto">
                        <button type="submit" class="btn btn-block btn-primary" style="width: 380px;"> Envoyer</button>
                    </div>
                    <?php 
                        if (isset($_POST['firstname']) || isset($_POST['lastname'])):
                            $firstname = $_POST['firstname'];
                            $lastname = $_POST['lastname'];
                            /*$req contient les tuples de la requête*/
                            $req = $bdd->prepare('SELECT FIRSTNAME, LASTNAME FROM person WHERE FIRSTNAME = :firstname OR LASTNAME = :lastname', [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]); // Prepare pour éviter les injections sql
                            $req->execute(array('firstname' => $firstname, 'lastname' => $lastname));
                            
                            if ($req->rowCount() > 0){
                                while ($tuple = $req->fetch()) {
                                    echo "<p>" . $tuple['FIRSTNAME'] . " " . $tuple['LASTNAME'] . "</p>";
                                }
                            }
                            else{
                                echo "<p>Le prénom '$firstname' n'a pas été trouvé dans la base de données</p>";
                            }
                            
                        endif; 
                    ?>
                </p>
            </form>
        </div>

        <div class="container d-flex flex-column align-items-center shadow rounded-2 mt-8 mx-auto custom-bg-color p-5 pt-4 mt-4">
            <h1>Sélectionner des séries</h1>
            <form method="post" action="tables.php">
            </form>
        </div>

        <div class="container d-flex flex-column align-items-center shadow rounded-2 mt-8 mx-auto custom-bg-color p-5 pt-4 mt-4">
            <h1>Sélectionner des épisodes</h1>
            <form method="post" action="tables.php">
            </form>
        </div>
    </body>
</html>