<?php
    session_start();
    include 'header.html';
    include 'navbar.php';
?>

<body>
        <?php
            $bdd = new PDO('mysql:host=db;dbname=group9;charset=utf8', 'group9', 'tabodi');
                    
        ?>
        <div class="container d-flex flex-column align-items-center card shadow rounded-2 mt-8 mx-auto custom-bg-color p-5 pt-4 mt-4">
            <h1>Sélectionner des personnes</h1>
            <?php
                if (isset($_POST['firstname']) || isset($_POST['lastname'])):
                    $firstname = $_POST['firstname'];
                    $lastname = $_POST['lastname'];
                    /*$req contient les tuples de la requête*/
                    $req = $bdd->prepare('SELECT FIRSTNAME, LASTNAME FROM person WHERE FIRSTNAME = :firstname OR LASTNAME = :lastname', [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]); // Prepare pour éviter les injections sql
                    $req->execute(array('firstname' => $firstname, 'lastname' => $lastname));
                    
                    
                    if ($req->rowCount() <= 0){
                        if(strlen($firstname) > 50 || strlen($lastname) > 50){
                            echo "<div class='error-box'>Veuillez entrer des données valides </div>";
                        }
                        else{
                            echo "<div class='error-box'>" . $firstname . " " . $lastname . " n'a pas été trouvé dans la base de données</div>";
                        }
                    }                
                endif; 
            ?>
            <form method="post" action="tables.php">
                <p>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" style="width: 400px;" placeholder="Prénom" name="firstname">
                    </div>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" style="width: 400px;" placeholder="Nom de famille" name="lastname">
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn custom-btn">Envoyer</button>
                    </div>
                </p>
            </form>
            <?php
                if ($req != NULL && $req->rowCount() > 0){
                    echo "<div class='table-responsive mt-3'>";
                    echo "<table class='table table-bordered table-hover'>";
                    echo "<thead><tr><th>Prénom</th><th>Nom de famille</th></tr></thead>";
                    echo "<tbody>";
                    while ($tuple = $req->fetch()) {
                        echo "<tr>";
                        echo "<td>" . $tuple['FIRSTNAME'] . "</td>";
                        echo "<td>" . $tuple['LASTNAME'] . "</td>";
                        echo "</tr>";
                    }
                    echo "</tbody></table></div>";
                }
            ?>
        </div>

        <div class="container d-flex flex-column align-items-center card shadow rounded-2 mt-8 mx-auto custom-bg-color p-5 pt-4 mt-4">
            <h1>Sélectionner des séries</h1>
            <form method="post" action="tables.php">
                <p>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control block" style="width: 400px;" placeholder="Nom de la série" name="name">
                    </div>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" style="width: 400px;" placeholder="Réseau" name="network">
                    </div>
                    <div class="input-group mb-3">
                        <input data-provide="datepicker" data-date-format="yyyy-mm-dd" class="form-control" style="width: 400px;" placeholder="Date de début" name="startdate" id="startdate">
                    </div>
                    <div class="input-group mb-3">
                        <input data-provide="datepicker" data-date-format="yyyy-mm-dd" class="form-control" style="width: 400px;" placeholder="Date de fin" name="enddate" id="enddate">
                    </div>

                    <div class="input-group mb-3">
                        <input type="text" class="form-control" style="width: 400px;" placeholder="Taskmaster" name="taskmaster">
                    </div>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" style="width: 400px;" placeholder="Assistant" name="assistant">
                    </div>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" style="width: 400px;" placeholder="Champion" name="champion">
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn custom-btn">Envoyer</button>
                    </div>
                    
                </p>
            </form>
            <?php
                if ($req != NULL && $req->rowCount() > 0){
                    echo "<div class='table-responsive mt-3'>";
                    echo "<table class='table table-bordered table-hover'>";
                    echo "<thead><tr><th>Série</th><th>Réseau</th><th>Date de début</th><th>Date de fin</th><th>Taskmaster</th><th>Assistant</th><th>Champion</th></tr></thead>";
                    echo "<tbody>";
                    while ($tuple = $req->fetch()) {
                        echo "<tr>";
                        echo "<td>" . $tuple['FIRSTNAME'] . "</td>";
                        echo "<td>" . $tuple['LASTNAME'] . "</td>";
                        echo "</tr>";
                    }
                    echo "</tbody></table></div>";
                }
            ?>
            <?php
                if (isset($_POST['firstname']) || isset($_POST['lastname'])):
                    $firstname = $_POST['firstname'];
                    $lastname = $_POST['lastname'];
                    /*$req contient les tuples de la requête*/
                    $req = $bdd->prepare('SELECT FIRSTNAME, LASTNAME FROM person WHERE FIRSTNAME = :firstname OR LASTNAME = :lastname', [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]); // Prepare pour éviter les injections sql
                    $req->execute(array('firstname' => $firstname, 'lastname' => $lastname));
                    
                    
                    if ($req->rowCount() <= 0){
                        if(strlen($firstname) > 50 || strlen($lastname) > 50){
                            echo "<div class='error-box'>Veuillez entrer des données valides </div>";
                        }
                        else{
                            echo "<div class='error-box'>" . $firstname . " " . $lastname . " n'a pas été trouvé dans la base de données</div>";
                        }
                    }                
                endif; 
            ?>
        </div>

        <div class="container d-flex flex-column align-items-center card shadow rounded-2 mt-8 mx-auto custom-bg-color p-5 pt-4 mt-4">
            <h1>Sélectionner des épisodes</h1>
            <form method="post" action="tables.php">
            </form>
        </div>
    </body>
</html>
