<?php
    session_start();
    include 'header.html';
    include 'navbar.php';

    $bdd = new PDO('mysql:host=db;dbname=group9;charset=utf8', 'group9', 'tabodi');
?>

<body>
    <div class="container d-flex flex-column align-items-center card shadow rounded-2 mt-8 mx-auto custom-bg-color p-5 pt-4 mt-4">
        <h2>Sélectionner un épisode</h2>
        <form method="post" action="episode.php">
            <div class="input-group mb-3 mt-2">
                <select class="form-select" style="width: 300px;" name="title">
                    <option value="">Titre de l'épisode</option>
                    <?php

                        $series_req = $bdd->query('SELECT SERIES_NAME FROM series ORDER BY SERIES_NAME');

                        while ($series_row = $series_req->fetch(PDO::FETCH_ASSOC)) {
                            $series_name = $series_row['SERIES_NAME'];
                            echo "<optgroup label='$series_name'>";

                            // Requête pour obtenir les épisodes de cette série
                            $episode_req = $bdd->prepare('SELECT TITLE, EPISODE_NUMBER FROM episode WHERE SERIES_NAME = :series_name ORDER BY SERIES_NAME');
                            $episode_req->bindParam(':series_name', $series_name, PDO::PARAM_STR);
                            $episode_req->execute();

                            // Boucle à travers chaque épisode de cette série
                            while ($episode_row = $episode_req->fetch(PDO::FETCH_ASSOC)) {
                                echo "<option value='" . $episode_row['TITLE'] . "'>" . $episode_row['EPISODE_NUMBER'] . " " . $episode_row['TITLE'] . "</option>";
                            }

                            echo "</optgroup>";
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
    if (isset($_POST['title']) && $_POST['title'] != "") {
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
        } else{
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
                    echo "<div class='success-box mt-2''>L'épisode a été mis à jour avec succès (rafraîchir la page pour voir les modifications).</div>";
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
                    echo "<div class='error-box mt-2'>Le candidat spécifié n'existe pas dans la base de donnée. Pour être défini en tant que gagnant de cet épisode, un candidat doit remplir l'un des critères suivants :
                        <ul>
                            <li>Avoir le plus de points accumulés.</li>
                            <li>Gagner une tâche d'égalité spécifique à cet épisode.</li>
                        </ul>
                        <form method='get' action='ajout-candidat.php'>
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
                                    )
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
                            echo "<div class='success-box mt-2'>Le gagnant de cette épisode a bien été mis à jour (rafraîchir la page pour voir les modifications).</div>";
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
    <div class='container d-flex flex-column align-items-center card shadow rounded-2 mt-8 mx-auto custom-bg-color p-5 pt-4 mt-4'>
        <h2>Ajoutez des épisodes à une série non terminée</h2>
        <form method='post' action='episode.php'>
            <div class="input-group mb-3 mt-2">
                <select class="form-select" style = "width: 300px;" name="series_name2">
                    <option value="">Nom de la série</option>
                    <?php
                        $req3 = $bdd->query('SELECT SERIES_NAME FROM series WHERE CHAMPION_ID IS NULL');
                        while ($row = $req3->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value='" . $row['SERIES_NAME'] . "'>" . $row['SERIES_NAME'] . "</option>";
                        }
                    ?>
                </select>
            </div>

            <input type='number' class='form-control mb-3' name='count_episode' placeholder="Nombre d'épisode à ajouter" min = '1'>

            <div class="d-grid gap-2">
                    <button type="submit" class="btn custom-btn">Envoyer</button>
                </div>
        </form>
    </div>
                
<?php 
    if (isset($_POST['series_name2']) && $_POST['series_name2'] != "") {
        $seriesName2 = $_POST['series_name2'];

?>
    <?php
        if (isset($_POST['count_episode']) && $_POST['count_episode'] != "") {
            $count_episode = $_POST['count_episode'];
            $series_name = $_POST['series_name2'];

            // On récupère le numéro de l'épisode le plus récent dans la série sélectionnée
            $req_episode_number = $bdd->prepare("SELECT MAX(EPISODE_NUMBER) AS max_episode_number FROM episode WHERE SERIES_NAME = ?");
            $req_episode_number->execute([$series_name]);
            $row = $req_episode_number->fetch(PDO::FETCH_ASSOC);
            $episode_number = ($row['max_episode_number'] !== null) ? $row['max_episode_number'] + 1 : 1;

            if(isset($_POST['titles']) && isset($_POST['airdates'])){
                $titles = $_POST['titles'];
                $airdates = $_POST['airdates'];

                $req_episode = $bdd->prepare("INSERT INTO episode (SERIES_NAME, EPISODE_NUMBER, TITLE, AIRDATE) VALUES (:series_name2, :episode_number, :titles, :airdate)");

                for ($i = 0; $i < count($titles); $i++) {
                    $req_episode->execute(array(
                        'series_name2' => $seriesName2,
                        'episode_number' => $episode_number + $i,
                        'titles' => $titles[$i],
                        'airdate' => date('Y-m-d', strtotime($airdates[$i]))
                    ));

                    if($req_episode->rowCount() <= 0){
                        echo "<div class='error-box'>Un problème est survenu lors de l'ajout de l'épisode</div>";
                    } else{
                        echo "<div class='success-box'>L'épisode '$titles[$i]' a été ajouté avec succès à la série $seriesName2</div>";
                    }
                }
            }

            ?>
            <div class='container d-flex flex-column align-items-center card shadow rounded-2 mt-8 mx-auto custom-bg-color p-5 pt-4 mt-4 mb-4'>
                <h2>Ajoutez des épisodes à la série : <?php echo $series_name; ?></h2>
                <form method='post' action='episode.php'>
                    <input type="hidden" name="count_episode" value="<?php echo $count_episode; ?>">
                    <input type="hidden" name="series_name2" value="<?php echo $series_name; ?>">

                    <table class='table table-bordered mt-3'>
                        <thead>
                            <tr>
                                <th style="text-align: center;">Numéro</th>
                                <th style="text-align: center;">Titre de l'épisode</th>
                                <th style="text-align: center;">Date de diffusion</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($i = 0; $i < $count_episode; $i++) { ?>
                                <tr>
                                    <td style="vertical-align: middle; text-align: center;"><?php echo $episode_number + $i; ?></td>
                                    <td><input type='text' class='form-control' name='titles[]' placeholder="Titre" required></td>
                                    <td><input data-provide="datepicker" data-date-format="yyyy-mm-dd" class="form-control" placeholder="Date" name="airdates[]" required></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>

                    <div class="d-grid gap-2">
                        <button type='submit' style="width: 150px;" class='btn custom-btn'>Ajouter</button>
                    </div>
                </form>
            </div>
    <?php } ?>
<?php
    }
?>
