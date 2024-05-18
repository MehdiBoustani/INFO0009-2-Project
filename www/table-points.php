<?php
    session_start();
    include 'header.html';
    include 'navbar.php';

    $bdd = new PDO('mysql:host=db;dbname=group9;charset=utf8', 'group9', 'tabodi');
?>

<body>
    <div class="container d-flex flex-column align-items-center card shadow rounded-2 mt-8 mx-auto custom-bg-color p-5 pt-4 mt-4">
        <h2>Sélectionner un épisode</h2>
        <form method="post" action="table-points.php">
            <div class="input-group mb-3 mt-2">
                <select class="form-select" style="width: 300px;" name="title">
                    <option value="">Titre de l'épisode</option>
                    <?php
                        $series_req = $bdd->query('SELECT SERIES_NAME FROM series ORDER BY SERIES_NAME');
                        while ($seriesRow = $series_req->fetch(PDO::FETCH_ASSOC)) {
                            $seriesName = $seriesRow['SERIES_NAME']; 
                            echo "<optgroup label='$seriesName'>";
                            // Requête pour obtenir les épisodes de cette série
                            $episode_req = $bdd->prepare('SELECT TITLE, EPISODE_NUMBER FROM episode WHERE SERIES_NAME = ? ORDER BY EPISODE_NUMBER');
                            $episode_req->execute([$seriesName]);
                            // Boucle à travers chaque épisode de cette série
                            while ($episodeRow = $episode_req->fetch(PDO::FETCH_ASSOC)) {
                                echo "<option value='" . $episodeRow['TITLE'] . "' data-episode='" . $episodeRow['EPISODE_NUMBER'] . "'>" . $episodeRow['EPISODE_NUMBER'] . " " . $episodeRow['TITLE'] . "</option>";
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

        $req = $bdd->prepare('SELECT SERIES_NAME, EPISODE_NUMBER FROM episode WHERE TITLE = :title');

        $req->execute(array(
            'title' => $titleSelected
        ));

        if($req->rowCount() <= 0){
            echo "<div class='error-box'>Erreur lors de la récupération des données</div>";
        }
        else{
            $row = $req->fetch(PDO::FETCH_ASSOC);
            $seriesName2 = $row['SERIES_NAME'];
            $episodeNumber2 = $row['EPISODE_NUMBER'];
        }

        $req2 = $bdd->prepare('SELECT t.TASK_NUMBER, t.DESCRIPTION, CONCAT(per.FIRSTNAME, " ", per.LASTNAME) AS FULLNAME, p.POINTS
                                FROM points p
                                JOIN candidate c ON p.CANDIDATE_ID = c.ID
                                JOIN person per ON c.ID = per.ID
                                JOIN feature f ON p.SERIES_NAME = f.SERIES_NAME AND c.ID = f.CANDIDATE_ID
                                JOIN task t ON p.SERIES_NAME = t.SERIES_NAME AND p.EPISODE_NUMBER = t.EPISODE_NUMBER AND p.TASK_NUMBER = t.TASK_NUMBER
                                WHERE p.SERIES_NAME = :series_name AND p.EPISODE_NUMBER = :episode_number
                                ORDER BY f.CHAIR, t.TASK_NUMBER');

        $req2->execute(array(
            'series_name' => $seriesName2,
            'episode_number' => $episodeNumber2,
        ));

        if ($req2->rowCount() <= 0) {
            echo "<div class='error-box mt-3'>Cet épisode ne possède aucune tâche.</div>";
            
        } else {
            echo "<div class='container d-flex flex-column align-items-center card shadow rounded-2 mt-8 mx-auto custom-bg-color p-5 pt-4 mt-4'>";
            echo "<h3>Résultats de l'épisode '$titleSelected'</h3>";
            echo "<table class='table table-bordered mt-3'>";
            echo "<thead><tr><th>Candidats</th><th><a href='#task_descriptions' class='black-link'>Tâches</a></th><th>Points</th></tr></thead>";
            echo "<tbody>";

            $currentCandidate = null;
            $totalPoints = 0;

            while ($row = $req2->fetch(PDO::FETCH_ASSOC)) {
                if ($row['FULLNAME'] !== $currentCandidate) {
                    echo "<tr><td colspan='4' class='table-info' style='background-color: #982627; color: white;'><strong>{$row['FULLNAME']}</strong></td></tr>";
                    $currentCandidate = $row['FULLNAME'];
                }

                echo "<tr>";
                echo "<td></td>";
                echo "<td>" . $row['TASK_NUMBER'] . "</td>";
                echo "<td>" . $row['POINTS'] . "</td>";
                echo "</tr>";

                $totalPoints += $row['POINTS'];
            }

            echo "</tbody>";

            echo "<tfoot'><tr><td colspan='2'><strong>Total</strong></td><td><strong>$totalPoints</strong></td></tr></tfoot>";
            echo "</table>";
            echo "</div>";
            echo "<div id='task_descriptions' class='container d-flex flex-column align-items-center card shadow rounded-2 mt-8 mx-auto custom-bg-color p-5 pt-4 mt-4 mb-4'>";
            echo "<h3>Description des tâches</h3>";

            echo "<table class='table table-bordered mt-3'>";
            $req3 = $bdd->prepare('SELECT DISTINCT DESCRIPTION, TASK_NUMBER FROM task WHERE SERIES_NAME = :series_name AND EPISODE_NUMBER = :episode_number ORDER BY TASK_NUMBER');
            $req3->execute(array(
                'series_name' => $seriesName2,
                'episode_number' => $episodeNumber2,
            ));
            while ($row = $req3->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr> <td>" . $row['TASK_NUMBER'] . "</td><td colspan='3'>" . $row['DESCRIPTION'] . "</td></tr>";
            }
            echo "</tfoot>";
            echo "</table>";

        }
    }
?>

