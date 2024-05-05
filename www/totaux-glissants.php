<?php
session_start();
include 'header.html';
include 'navbar.php';

// Database connection
$bdd = new PDO('mysql:host=db;dbname=group9;charset=utf8', 'group9', 'tabodi');

if (isset($_POST['name'])) {
    $name = $_POST['name'];

    // Prepare the SQL query
    $req = $bdd->prepare('SELECT NAME FROM series WHERE NAME = :name');
    $req->bindParam(':name', $name, PDO::PARAM_STR);
    $req->execute();

    if ($req->rowCount() <= 0) {
        echo "<div class='error-box'>Vous n'avez pas choisi une série</div>";
    }
    else {
        $req2 = $bdd->prepare('SELECT FIRSTNAME, LASTNAME, SUM(POINTS) AS TOTAL_POINTS, EPISODE_NUMBER
                               FROM person, points                               
                               WHERE SERIES_NAME =:name
                               AND points.CANDIDATE_ID = person.ID
                               GROUP BY person.FIRSTNAME, person.LASTNAME, points.EPISODE_NUMBER
                               ORDER BY points.EPISODE_NUMBER ASC, person.FIRSTNAME ASC');
        
        $req2->bindParam(':name', $name, PDO::PARAM_STR);
        $req2->execute();

        echo "<style>
        table {
        width: 100%;
        border-collapse: collapse;
        }
        th, td {
        border: 1px solid black;
        }
        caption {
            caption-side: top; /* Position de la légende au-dessus de la table */
            font-weight: bold; /* Pour un style plus prononcé */
        }
        </style>";

        echo "<table>";

        echo "<caption>Totaux glissants des candidats : $name</caption>"; // Ajoutez le titre de la table

        echo "<tr>";

        $candidates = [];
        $list = [];

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
                foreach ($list as $item) {
                    echo "<td>$item</td>";
                }
                echo "</tr>";

                $list = []; //empty list of totals 
            }
        }

        echo "</table>";
    }
}

?>

<body>
    <div class="container d-flex flex-column align-items-center card shadow rounded-2 mt-8 mx-auto custom-bg-color p-5 pt-4 mt-4">
        <h1>Sélectionner une série</h1>
        <form method="post" action="totaux-glissants.php">
            <div class="input-group mb-3">
                <!-- Dropdown list for series names -->
                <select class="form-select" name="name">
                    <option value="">Sélectionner une série</option>
                    <?php
                    // Fetch all series names from the database
                    $req = $bdd->query('SELECT NAME FROM series');
                    while ($row = $req->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='" . $row['NAME'] . "'>" . $row['NAME'] . "</option>";
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
