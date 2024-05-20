<?php
    session_start();
    include 'header.html';
    include 'navbar.php';

    $bdd = new PDO('mysql:host=db;dbname=group9;charset=utf8', 'group9', 'tabodi');
?>

<body>
    <div class="container d-flex flex-column align-items-center card shadow rounded-2 mt-8 mx-auto custom-bg-color p-5 pt-4 mt-4">
        <h2>Sélectionner des personnes</h2>
        <?php
            if (isset($_POST['firstname']) || isset($_POST['lastname'])):
                $firstname = $_POST['firstname'];
                $lastname = $_POST['lastname'];

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
        <form method="post" action="selection-donnees.php">
            <p>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" style="width: 500px;" placeholder="Prénom" name="firstname">
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
        <h2>Sélectionner des séries</h2>
        
        <form method="post" action="selection-donnees.php">
            <p>
                <div class="input-group mb-3">
                    <input type="text" class="form-control block" style="width: 400px;" placeholder="Nom de la série" name="series_name">
                </div>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" style="width: 400px;" placeholder="Réseau" name="network">
                </div>
                <div class="input-group mb-3">
                    <input data-provide="datepicker" data-date-format="yyyy-mm-dd" class="form-control" style="width: 400px;" placeholder="Date de début" name="startdate">
                </div>
                <div class="input-group mb-3">
                    <input data-provide="datepicker" data-date-format="yyyy-mm-dd" class="form-control" style="width: 400px;" placeholder="Date de fin" name="enddate">
                </div>

                <div class="input-group mb-3">
                    <label for="taskmaster_firstname" style="width: 105px;" class="input-group-text">Taskmaster:</label>
                    <input type="text" class="form-control" style="width: 200px;" placeholder="Prénom" name="taskmaster_firstname">
                    <input type="text" class="form-control" style="width: 200px;" placeholder="Nom" name="taskmaster_lastname">
                </div>
                <div class="input-group mb-3">
                    <label for="assistant_firstname" style="width: 105px;" class="input-group-text">Assistant:</label>
                    <input type="text" class="form-control" style="width: 200px;" placeholder="Prénom" name="assistant_firstname">
                    <input type="text" class="form-control" style="width: 200px;" placeholder="Nom" name="assistant_lastname">
                </div>
                <div class="input-group mb-3">
                    <label for="champion_firstname" style="width: 105px;" class="input-group-text">Champion:</label>
                    <input type="text" class="form-control" style="width: 200px;" placeholder="Prénom" name="champion_firstname">
                    <input type="text" class="form-control" style="width: 200px;" placeholder="Nom" name="champion_lastname">
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn custom-btn">Envoyer</button>
                </div>
                
            </p>
        </form>

        <?php
            if (isset($_POST['series_name']) || isset($_POST['network']) || isset($_POST['startdate']) || isset($_POST['enddate']) || isset($_POST['taskmaster_firstname']) || isset($_POST['taskmaster_lastname']) || isset($_POST['assistant_firstname']) || isset($_POST['assistant_lastname']) || isset($_POST['champion_firstname']) || isset($_POST['champion_lastname'])){
                $name = $_POST['series_name'];
                $network = $_POST['network'];
                $startdate = date('Y-m-d', strtotime($_POST['startdate']));
                $enddate = date('Y-m-d', strtotime($_POST['enddate']));
                $taskmaster_firstname = $_POST['taskmaster_firstname'];
                $taskmaster_lastname = $_POST['taskmaster_lastname'];
                $assistant_firstname = $_POST['assistant_firstname'];
                $assistant_lastname = $_POST['assistant_lastname'];
                $champion_firstname = $_POST['champion_firstname'];
                $champion_lastname = $_POST['champion_lastname'];
                
                $req = $bdd->prepare('SELECT series.SERIES_NAME, series.NETWORK, series.STARTDATE, series.ENDDATE, 
                    taskmaster.FIRSTNAME AS taskmaster_firstname, taskmaster.LASTNAME AS taskmaster_lastname,
                    assistant.FIRSTNAME AS assistant_firstname, assistant.LASTNAME AS assistant_lastname,
                    champion.FIRSTNAME AS champion_firstname, champion.LASTNAME AS champion_lastname
                    FROM series 
                    INNER JOIN person taskmaster ON series.TASKMASTER_ID = taskmaster.ID
                    INNER JOIN person assistant ON series.ASSISTANT_ID = assistant.ID
                    LEFT JOIN person champion ON series.CHAMPION_ID  = champion.ID
                    WHERE series.SERIES_NAME = :series_name 
                        OR series.NETWORK = :network 
                        OR series.STARTDATE = :startdate 
                        OR series.ENDDATE = :enddate 
                        OR (taskmaster.FIRSTNAME = :taskmaster_firstname OR taskmaster.LASTNAME = :taskmaster_lastname)
                        OR (assistant.FIRSTNAME = :assistant_firstname OR assistant.LASTNAME = :assistant_lastname)
                        OR (champion.FIRSTNAME = :champion_firstname OR champion.LASTNAME = :champion_lastname);
                ');
                
                $req->execute(array(
                    'series_name' => $name,
                    'network' => $network,
                    'startdate' => $startdate,
                    'enddate' => $enddate,
                    'taskmaster_firstname' => $taskmaster_firstname,
                    'taskmaster_lastname' => $taskmaster_lastname,
                    'assistant_firstname' => $assistant_firstname,
                    'assistant_lastname' => $assistant_lastname,
                    'champion_firstname' => $champion_firstname,
                    'champion_lastname' => $champion_lastname
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
    <div class="container d-flex flex-column align-items-center card shadow rounded-2 mt-8 mx-auto custom-bg-color p-5 pt-4 mt-4 mb-4">
        <h2>Sélectionner des épisodes</h2>
        <form method="post" action="selection-donnees.php">
            <p>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" style="width: 300px;" placeholder="Nom de la série" name="series_name2">
                </div>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" style="width: 400px;" placeholder="Numéro de l'épisode" name="episode_number">
                </div>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" style="width: 400px;" placeholder="Titre" name="title">
                </div>
                <div class="input-group mb-3">
                    <input data-provide="datepicker" data-date-format="yyyy-mm-dd" class="form-control" style="width: 400px;" placeholder="Date de diffusion" name="airdate">
                </div>
                <div class="input-group mb-3">
                <label for="champion_firstname" style="width: 105px;" class="input-group-text">Gagnant:</label>
                    <input type="text" class="form-control" style="width: 200px;" placeholder="Prénom" name="winner_firstname">
                    <input type="text" class="form-control" style="width: 200px;" placeholder="Nom" name="winner_lastname">
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn custom-btn">Envoyer</button>
                </div>
                
            </p>
        </form>
        <?php
            if (isset($_POST['series_name2']) || isset($_POST['episode']) || isset($_POST['title']) || isset($_POST['airdate']) || isset($_POST['winner_firstname']) || isset($_POST['winner_lastname'])){
                $seriesName = $_POST['series_name2'];
                $episodeNumber = $_POST['episode_number'];
                $title = $_POST['title'];
                $airdate = date('Y-m-d', strtotime($_POST['airdate']));
                $winnerFirstname = $_POST['winner_firstname'];
                $winnerLastname = $_POST['winner_lastname'];
                
                $req = $bdd->prepare('SELECT episode.SERIES_NAME, episode.EPISODE_NUMBER, episode.TITLE, episode.AIRDATE, 
                    winner.FIRSTNAME AS winner_firstname, winner.LASTNAME AS winner_lastname
                    FROM episode
                    LEFT JOIN person winner ON episode.WINNER_ID = winner.ID
                    WHERE episode.SERIES_NAME = :series_name2 
                        OR episode.EPISODE_NUMBER = :episode_number 
                        OR episode.TITLE = :title
                        OR episode.AIRDATE = :airdate 
                        OR (winner.FIRSTNAME = :winner_firstname OR winner.LASTNAME = :winner_lastname)
                    ORDER BY episode.EPISODE_NUMBER, episode.SERIES_NAME
                ');
                
                $req->execute(array(
                    'series_name2' => $seriesName,
                    'episode_number' => $episodeNumber,
                    'title' => $title,
                    'airdate' => $airdate,
                    'winner_firstname' => $winnerFirstname,
                    'winner_lastname' => $winnerLastname
                ));

                if ($req != NULL && $req->rowCount() > 0) {
                    echo "<div class='table-responsive mt-3'>";
                    echo "<table class='table table-bordered table-hover'>";
                    echo "<thead><tr><th>Série</th><th>Episode</th><th>Titre</th><th>Date de diffusion</th><th>Gagnant</th></tr></thead>";
                    echo "<tbody>";
                    while ($tuple = $req->fetch()) {
                        echo "<tr>";
                        echo "<td>" . $tuple['SERIES_NAME'] . "</td>";
                        echo "<td>" . $tuple['EPISODE_NUMBER'] . "</td>";
                        echo "<td>" . $tuple['TITLE'] . "</td>";
                        echo "<td>" . $tuple['AIRDATE'] . "</td>";
                        echo "<td>" . $tuple['winner_firstname'] . " " . $tuple['winner_lastname'] . "</td>";
                        echo "</tr>";
                    }
                    echo "</tbody></table></div>";
                } else {
                    echo "<div class='error-box'>Aucun résultat trouvé pour les critères de recherche spécifiés</div>";
                }
            }
        ?>
    </div>
</body>
</html>
