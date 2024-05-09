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
                            echo "<div class='error-box'>Veuillez entrer des données valides</div>";
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
                        <input type="text" class="form-control block" style="width: 400px;" placeholder="Nom de la série" name="series_name">
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
                if (isset($_POST['series_name']) || isset($_POST['network']) || isset($_POST['startdate']) || isset($_POST['enddate']) || isset($_POST['taskmaster']) || isset($_POST['assistant']) || isset($_POST['champion'])){
                    $name = $_POST['series_name'];
                    $network = $_POST['network'];
                    $startdate = date('Y-m-d', strtotime($_POST['startdate']));
                    $enddate = date('Y-m-d', strtotime($_POST['enddate']));
                    $taskmaster = $_POST['taskmaster'];
                    $assistant = $_POST['assistant'];
                    $champion = $_POST['champion'];;
                    
                    $req = $bdd->prepare('SELECT series.SERIES_NAME, series.NETWORK, series.STARTDATE, series.ENDDATE, 
                        p1.FIRSTNAME AS taskmaster_firstname, p1.LASTNAME AS taskmaster_lastname,
                        p2.FIRSTNAME AS assistant_firstname, p2.LASTNAME AS assistant_lastname,
                        p3.FIRSTNAME AS champion_firstname, p3.LASTNAME AS champion_lastname
                        FROM series 
                        LEFT JOIN taskmaster ON series.TASKMASTER_ID = taskmaster.ID 
                        LEFT JOIN assistant ON series.ASSISTANT_ID = assistant.ID 
                        LEFT JOIN candidate ON series.CHAMPION_ID = candidate.ID 
                        LEFT JOIN person p1 ON taskmaster.ID = p1.ID
                        LEFT JOIN person p2 ON assistant.ID = p2.ID
                        LEFT JOIN person p3 ON candidate.ID = p3.ID
                        WHERE series.SERIES_NAME = :series_name 
                            OR series.NETWORK = :network 
                            OR series.STARTDATE = :startdate 
                            OR series.ENDDATE = :enddate 
                            OR (p1.FIRSTNAME = :taskmaster OR p1.LASTNAME = :taskmaster)
                            OR (p2.FIRSTNAME = :assistant OR p2.LASTNAME = :assistant)
                            OR (p3.FIRSTNAME = :champion OR p3.LASTNAME = :champion);
                    ');
                    
                    $req->execute(array(
                        'series_name' => $name,
                        'network' => $network,
                        'startdate' => $startdate,
                        'enddate' => $enddate,
                        'taskmaster' => $taskmaster,
                        'assistant' => $assistant,
                        'champion' => $champion
                    ));

                    if ($req != NULL && $req->rowCount() > 0) {
                        echo "<div class='table-responsive mt-3'>";
                        echo "<table class='table table-bordered table-hover'>";
                        echo "<thead><tr><th>Série</th><th>Réseau</th><th>Date de début</th><th>Date de fin</th><th>Taskmaster</th><th>Assistant</th><th>Champion</th></tr></thead>";
                        echo "<tbody>";
                        while ($tuple = $req->fetch()) {
                            echo "<tr>";
                            echo "<td>" . $tuple['SERIES_NAME'] . "</td>";
                            echo "<td>" . $tuple['NETWORK'] . "</td>";
                            echo "<td>" . $tuple['STARTDATE'] . "</td>";
                            echo "<td>" . $tuple['ENDDATE'] . "</td>";
                            echo "<td>" . $tuple['taskmaster_firstname'] . " " . $tuple['taskmaster_lastname'] . "</td>";
                            echo "<td>" . $tuple['assistant_firstname'] . " " . $tuple['assistant_lastname'] . "</td>";
                            echo "<td>" . $tuple['champion_firstname'] . " " . $tuple['champion_lastname'] . "</td>";
                            echo "</tr>";
                        }
                        echo "</tbody></table></div>";
                    } else {
                        echo "<div class='error-box'>Aucun résultat trouvé pour les critères de recherche spécifiés</div>";
                    }
                }
                
            ?>

        </div>

        <div class="container d-flex flex-column align-items-center card shadow rounded-2 mt-8 mx-auto custom-bg-color p-5 pt-4 mt-4">
            <h1>Sélectionner des épisodes</h1>
            <form method="post" action="tables.php">
            </form>
        </div>
    </body>
</html>
