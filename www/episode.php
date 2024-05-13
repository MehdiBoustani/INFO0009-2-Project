<?php
    session_start();
    include 'header.html';
    include 'navbar.php';

    // Database connection
    $bdd = new PDO('mysql:host=db;dbname=group9;charset=utf8', 'group9', 'tabodi');
?>

<body>
    <div class="container d-flex flex-column align-items-center card shadow rounded-2 mt-8 mx-auto custom-bg-color p-5 pt-4 mt-4">
        <h2>Sélectionner un épisode</h2>
        <form method="post" action="episode.php">
            <div class="input-group mb-3 mt-2">
                <select class="form-select" style = "width: 300px;" name="title">
                    <option value="">Titre de l'épisode</option>
                    <?php
                        $req = $bdd->query('SELECT TITLE FROM episode');
                        while ($row = $req->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value='" . $row['TITLE'] . "'>" . $row['TITLE'] . "</option>";
                        }
                    ?>
                </select>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn custom-btn">Envoyer</button>
            </div>
        </form>
    </div>
</body>
</html>

<?php
    if (isset($_POST['title'])) {
        $title = $_POST['title'];

        // Prepare the SQL query
        $req = $bdd->prepare('SELECT episode.*, person.FIRSTNAME AS WINNER_FIRSTNAME, person.LASTNAME AS WINNER_LASTNAME
                            FROM episode 
                            LEFT JOIN person ON episode.WINNER_ID = person.ID 
                            WHERE episode.TITLE = :title');
        
        $req->bindParam(':title', $title, PDO::PARAM_STR);
        $req->execute();

        if ($req->rowCount() <= 0) {
            echo "<div class='error-box'>Vous n'avez pas choisi d'épisode</div>";
        } else {
            $tuple = $req->fetch();

            if (
                isset($_POST['series_name']) && $_POST['series_name'] !== $tuple['SERIES_NAME'] ||
                isset($_POST['episode_number']) && $_POST['episode_number'] !== $tuple['EPISODE_NUMBER'] ||
                isset($_POST['new_title']) && $_POST['new_title'] !== $tuple['TITLE'] ||
                isset($_POST['airdate']) && $_POST['airdate'] !== $tuple['AIRDATE'] ||
                isset($_POST['winner_firstname']) && $_POST['winner_firstname'] !== $tuple['WINNER_FIRSTNAME'] ||
                isset($_POST['winner_lastname']) && $_POST['winner_lastname'] !== $tuple['WINNER_LASTNAME']
            ){
                $seriesName = $_POST['series_name'];
                $episodeNumber = $_POST['episode_number'];
                $newTitle = $_POST['new_title'];
                $airdate = date('Y-m-d', strtotime($_POST['airdate']));
                $winnerFirstname = $_POST['winner_firstname'];
                $winnerLastname = $_POST['winner_lastname'];
    
                // Check if the winner exists in the person table
                $req_person = $bdd->prepare('SELECT ID FROM person WHERE FIRSTNAME = :winner_firstname AND LASTNAME = :winner_lastname');
                $req_person->execute(array(
                    'winner_firstname' => $winnerFirstname,
                    'winner_lastname' => $winnerLastname
                ));
                $person_id = $req_person->fetchColumn();
    
                // If the winner does not exist, insert them into the person table
                if (!$person_id) {
                    $req_insert_person = $bdd->prepare('INSERT INTO person (FIRSTNAME, LASTNAME) VALUES (:winner_firstname, :winner_lastname)');
                    $req_insert_person->execute(array(
                        'winner_firstname' => $winnerFirstname,
                        'winner_lastname' => $winnerLastname
                    ));
    
                    // Retrieve the ID of the new person
                    $person_id = $bdd->lastInsertId();
                }
    
                $req2 = $bdd->prepare('UPDATE episode SET SERIES_NAME = :series_name, EPISODE_NUMBER = :episode_number, 
                TITLE = :new_title, AIRDATE = :airdate, WINNER_ID = :winner_id WHERE TITLE = :title');
    
                $req2->execute(array(
                    'series_name' => $seriesName,
                    'episode_number' => $episodeNumber,
                    'new_title' => $newTitle,
                    'airdate' => $airdate,
                    'winner_id' => $person_id,
                    'new_title' => $title
                ));
    
                if ($req2 != NULL && $req2->rowCount() > 0) {
                    echo "<div class='success-box'>Les informations ont été mises à jour avec succès.</div>";
                } else {
                    echo "<div class='error-box'>Une erreur s'est produite lors de la mise à jour des informations.</div>";
                }
            }
            
            echo "<div class='container d-flex flex-column align-items-center card shadow rounded-2 mt-8 mx-auto custom-bg-color p-5 pt-4 mt-4'>";
            echo "<h2>Mettre à jour les informations de l'épisode</h2>";
            echo "<form method='post' action='episode.php'>";
            echo "<table class='table table-bordered mt-3'>";
            echo "<thead><tr><th>Série</th><th>Episode</th><th>Titre</th><th>Date de diffusion</th></tr></thead>";
            echo "<tbody>";
    
            echo "<tr>";
            echo "<td><input type='text' class='form-control' name='series_name' value='" . $tuple['SERIES_NAME'] . "'></td>";
            echo "<td><input type='number' class='form-control' name='episode_number' value='" . $tuple['EPISODE_NUMBER'] . "' min='1'></td>";
            echo "<td><input type='text' class='form-control' name='new_title' value='" . $tuple['TITLE'] . "'></td>";
            echo "<td><input data-provide='datepicker' data-date-format='yyyy-mm-dd' class='form-control' name='airdate' value='" . $tuple['AIRDATE'] . "'></td>";
            echo "</tr>";
    
            echo "</tbody></table>";
            echo "<button type='submit' class='btn custom-btn'>Mettre à jour</button>";
            echo "</form>";
            echo "</div>";

            echo "<div class='container d-flex flex-column align-items-center card shadow rounded-2 mt-8 mx-auto custom-bg-color p-5 pt-4 mt-4'>";
            echo "<h2>Définir ou mettre à jour le gagnant de l'épisode</h2>";

            echo "<form method='post' action='episode.php'>";

            echo "<div class='input-group mb-3 mt-3'>";
            echo "<label for='champion_firstname' class='input-group-text' style='width: 85px;'>Prénom:</label><input type='text' class='form-control' name='winner_firstname' style='width: 300px;' placeholder='Prénom' value='" . $tuple['WINNER_FIRSTNAME'] . "'> ";
            echo "</div>";

            echo "<div class='input-group mb-3'>";
            echo "<label for='champion_lastname' class='input-group-text' style='width: 85px;'>Nom:</label><input type='text' class='form-control' name='winner_lastname' style='width: 300px;' placeholder='Nom' value='" . $tuple['WINNER_LASTNAME'] . "'>";
            echo "</div>";

            echo "<button type='submit' class='btn custom-btn'>Mettre à jour le gagnant</button>";
            echo "</form>";
            echo "</div>";
        }
    }
?>

