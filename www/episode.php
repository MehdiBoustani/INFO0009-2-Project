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
        $titleSelected = $_POST['title'];

        // On fait un joint à gauche pour avoir tous les élements de l'épisode ainsi que du nom et prénom du gagnant
        $req = $bdd->prepare('SELECT episode.*, person.FIRSTNAME AS WINNER_FIRSTNAME, person.LASTNAME AS WINNER_LASTNAME
                            FROM episode 
                            LEFT JOIN person ON episode.WINNER_ID = person.ID 
                            WHERE episode.TITLE = :title');
        
        $req->bindParam(':title', $titleSelected, PDO::PARAM_STR);
        $req->execute();

        if ($req->rowCount() <= 0) {
            echo "<div class='error-box'>Vous n'avez pas choisi d'épisode</div>";
        } else {
            $tuple = $req->fetch();

            $newTitle = isset($_POST['new_title']) ? $_POST['new_title'] : $tuple['TITLE'];
            $airdate = isset($_POST['airdate']) ? date('Y-m-d', strtotime($_POST['airdate'])) : $tuple['AIRDATE'];
            $winnerFirstname = isset($_POST['winner_firstname']) ? $_POST['winner_firstname'] : $tuple['WINNER_FIRSTNAME'];
            $winnerLastname = isset($_POST['winner_lastname']) ? $_POST['winner_lastname'] : $tuple['WINNER_LASTNAME'];

            // Vérification si au moins une valeur est différente
            $fieldsChanged = (
                $newTitle !== $tuple['TITLE'] ||
                $airdate !== $tuple['AIRDATE']
            );

            if ($fieldsChanged) {

                $req2 = $bdd->prepare('UPDATE episode SET TITLE = :new_title, AIRDATE = :airdate WHERE TITLE = :title');

                $req2->execute(array(
                    'new_title' => $newTitle,
                    'airdate' => $airdate,
                    'title' => $titleSelected
                ));

                if ($req2->errorCode() === "00000") {
                    echo "<div class='success-box mt-2''>L'épisode a été mis à jour avec succès.</div>";
                } else {
                    $errorInfo = $req2->errorInfo();
                    echo "<div class='error-box mt-2''>Une erreur s'est produite lors de la mise à jour des informations : {$errorInfo[2]}</div>";
                }
            }

            $fieldsChanged2 = (
                $winnerFirstname !== $tuple['WINNER_FIRSTNAME'] ||
                $winnerLastname !== $tuple['WINNER_LASTNAME']
            );
                
            if($fieldsChanged2){

                // On sélectionne les noms et prénoms de tous les candidats
                $req_person = $bdd->prepare('SELECT ID FROM candidate NATURAL JOIN person WHERE FIRSTNAME = :winner_firstname AND LASTNAME = :winner_lastname');
                $req_person->execute(array(
                    'winner_firstname' => $winnerFirstname,
                    'winner_lastname' => $winnerLastname
                ));
                
                $person_id = $req_person->fetchColumn();
    
                // Si le gagnant n'existe pas, on renvoie une erreur
                if (!$person_id) {
                    echo "<div class='error-box mt-2''>Le candidat spécifié n'existe pas dans la base de donnée. Pour être défini en tant que gagnant de cet épisode, un candidat doit remplir l'un des critères suivants :
                        <ul>
                            <li>Avoir le plus de points accumulés.</li>
                            <li>Gagner une tâche d'égalité spécifique à cet épisode.</li>
                        </ul>
                        <form method='get' action='ajout-candidate.php'>
                            <button type='submit' class='btn custom-btn'>Créer un candidat ?</button>
                        </form>

                        </div>
                        ";
                }

                else{
                    $req_winner = $bdd->prepare('
                        SELECT DISTINCT person.ID
                        FROM person
                        INNER JOIN points ON points.CANDIDATE_ID = person.ID
                        LEFT JOIN tiebreakerresult ON tiebreakerresult.CANDIDATE_ID = person.ID
                        WHERE person.FIRSTNAME = :winner_firstname
                            AND person.LASTNAME = :winner_lastname
                            AND (
                                (SELECT SUM(p.POINTS) 
                                FROM points p
                                WHERE p.CANDIDATE_ID = person.ID 
                                    AND p.EPISODE_NUMBER = :episode_number
                                    AND p.SERIES_NAME = :series_name
                                ) >= (
                                    SELECT MAX(total_points) 
                                    FROM (
                                        SELECT SUM(p.POINTS) AS total_points
                                        FROM points p
                                        WHERE p.EPISODE_NUMBER = :episode_number
                                            AND p.SERIES_NAME = :series_name
                                        GROUP BY p.CANDIDATE_ID
                                    ) AS subquery
                                )
                                OR (
                                    tiebreakerresult.CANDIDATE_ID = person.ID
                                    AND tiebreakerresult.EPISODE_NUMBER = :episode_number
                                    AND tiebreakerresult.WON = 1 
                                    AND points.SERIES_NAME = :series_name
                                )
                            )
                    ');
                    $req_winner->execute(array(
                        'winner_firstname' => $winnerFirstname,
                        'winner_lastname' => $winnerLastname,
                        'episode_number' => $tuple['EPISODE_NUMBER'],
                        'series_name' => $tuple['SERIES_NAME']
                    ));


                    $win = $req_winner->fetchColumn();

                    if($win){
                        $req_updateWinner = $bdd->prepare('UPDATE episode SET WINNER_ID = :winner_id WHERE TITLE = :title');
                        $req_updateWinner->execute(array(
                            'winner_id' => $person_id,
                            'title' => $titleSelected,

                        ));
                        if($req_updateWinner->rowCount() >= 0){
                            echo "<div class='success-box mt-2'>Le gagnant de cette épisode a bien été mis à jour.</div>";
                        }
                    }
                    else{
                        echo "<div class='error-box mt-2'>Le candidat spécifié n'est pas un gagnant valide.</div>";
                    }
                }
                
            }
?>
            <div class="container d-flex flex-column align-items-center card shadow rounded-2 mt-8 mx-auto custom-bg-color p-5 pt-4 mt-4">
                <h2>Mettre à jour les informations de l'épisode</h2>
                <form id="update_form" method='post' action='episode.php'>
                    <table class='table table-bordered mt-3'>
                        <caption>Seul le titre et la date de diffusion peuvent être mis à jour</caption>
                        <thead>
                            <tr>
                                <th style="text-align: center;">Série</th>
                                <th style="text-align: center;">Episode</th>
                                <th style="text-align: center;">Titre</th>
                                <th style="text-align: center;">Date de diffusion</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="vertical-align: middle;"><?php echo $tuple['SERIES_NAME']; ?></td>
                                <td style="vertical-align: middle; text-align: center;"><?php echo $tuple['EPISODE_NUMBER']; ?></td>
                                <td><input type='text' class='form-control' name='new_title' value="<?php echo $tuple['TITLE']; ?>"></td>
                                <td><input data-provide='datepicker' data-date-format='yyyy-mm-dd' class='form-control' name='airdate' value="<?php echo $tuple['AIRDATE']; ?>"></td>
                            </tr>
                        </tbody>
                    </table>
                    <input type="hidden" name="title" value="<?php echo $titleSelected; ?>">
                    
                    <button type='submit' onClick="window.location.reload();" class='btn custom-btn'>Mettre à jour</button>
                </form>
            </div>

            <div class='container d-flex flex-column align-items-center card shadow rounded-2 mt-8 mx-auto custom-bg-color p-5 pt-4 mt-4'>
            <h2><?php echo ($tuple['WINNER_FIRSTNAME'] === null && $tuple['WINNER_LASTNAME'] === null) ? "Définir le gagnant de l'épisode" : "Mettre à jour le gagnant de l'épisode"; ?></h2>
                <form method='post' action='episode.php'>
                    <div class='input-group mb-3 mt-3'>
                        <label for='winner_firstname' class='input-group-text' style='width: 85px;'>Prénom:</label>
                        <input type='text' class='form-control' name='winner_firstname' style='width: 300px;' placeholder='Prénom' value='<?php echo $tuple['WINNER_FIRSTNAME'];?>'>
                    </div>

                    <div class='input-group mb-3'>
                        <label for='winner_lastname' class='input-group-text' style='width: 85px;'>Nom:</label>
                        <input type='text' class='form-control' name='winner_lastname' style='width: 300px;' placeholder='Nom' value='<?php echo $tuple['WINNER_LASTNAME'];?>'>
                    </div>

                    <input type="hidden" name="title" value="<?php echo $titleSelected; ?>">

                    <button type='submit' class='btn custom-btn'>
                        <?php echo ($tuple['WINNER_FIRSTNAME'] === null && $tuple['WINNER_LASTNAME'] === null) ? "Définir le gagnant" : "Mettre à jour le gagnant"; ?>
                    </button>
                </form>
            </div>
<?php
        }
    }
    
?>

