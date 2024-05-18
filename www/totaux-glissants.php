<?php
session_start();
include 'header.html';
include 'navbar.php';

// Database connection
$bdd = new PDO('mysql:host=db;dbname=group9;charset=utf8', 'group9', 'tabodi');
?>

<body>
    <div class="container d-flex flex-column align-items-center card shadow rounded-2 mt-8 mx-auto custom-bg-color p-5 pt-4 mt-4">
        <h2>Sélectionner une série</h2>
        <form method="post" action="totaux-glissants.php">
            <div class="input-group mb-3 mt-2">
                <!-- Dropdown list for series names -->
                <select class="form-select" style = "width: 300px;" name="series_name">
                    <option value="">Nom de la série</option>
                    <?php
                        // Fetch all series names from the database
                        $req = $bdd->query('SELECT SERIES_NAME FROM series ORDER BY SERIES_NAME');
                        while ($row = $req->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value='" . $row['SERIES_NAME'] . "'>" . $row['SERIES_NAME'] . "</option>";
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
    if (isset($_POST['series_name'])) {
        $name = $_POST['series_name'];

        $series_name = htmlspecialchars($name); 
        
        $req2 = $bdd->prepare('SELECT person.FIRSTNAME, person.LASTNAME, SUM(points.POINTS) AS TOTAL_POINTS, points.EPISODE_NUMBER
                            FROM person
                            INNER JOIN points ON points.CANDIDATE_ID = person.ID                      
                            WHERE SERIES_NAME =:series_name
                            GROUP BY person.FIRSTNAME, person.LASTNAME, points.EPISODE_NUMBER
                            ORDER BY points.EPISODE_NUMBER ASC, person.FIRSTNAME ASC');
        
        $req2->bindParam(':series_name', $name, PDO::PARAM_STR);
        $req2->execute();

        echo "<div class='container d-flex flex-column align-items-center card shadow rounded-2 mt-8 mx-auto custom-bg-color p-5 pt-4 mt-4'>";
        echo "<table class='table table-bordered table-hover'>";

        echo "<caption style='caption-side: top; font-weight: bold;'>Totaux glissants des candidats : $name</caption>"; // Ajoutez le titre de la table

        echo "<tr>";
        echo "<th>Episode</th>";

        $candidates = [];
        $list = [];
        $count = 1;

        while ($row = $req2->fetch(PDO::FETCH_ASSOC)) {
            $candidateName = $row['FIRSTNAME'] . ' ' . $row['LASTNAME'];
            if (!in_array($candidateName, $candidates)) {
                $candidates[] = $candidateName;
                echo "<th>{$candidateName}</th>";
            }

            $slidingTotal = 0;
            if (isset($slidingTotals[$candidateName])) {
                $slidingTotal = $slidingTotals[$candidateName];
            } 
            else {
                $slidingTotal = 0;
            }
            $slidingTotal += $row['TOTAL_POINTS'];
            $slidingTotals[$candidateName] = $slidingTotal;

            $list[] = $slidingTotal;
            if (count($list) == 5) {
                echo "<tr>";
                echo "<td>$count</td>";
                foreach ($list as $item) {
                    echo "<td>$item</td>";
                }
                echo "</tr>";

                $list = [];
                $count++;
            }
        }

        echo "</table></div>";
        
    }
?>
