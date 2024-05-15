<?php
session_start();
include 'header.html';
include 'navbar.php';

// Database connection
$bdd = new PDO('mysql:host=db;dbname=group9;charset=utf8', 'group9', 'tabodi');

// Récupération des statistiques depuis la base de données
$order_column = isset($_GET['order']) ? $_GET['order'] : 'Nom de la  Colonne de tri'; // Colonne de tri par défaut
$req = $bdd->prepare('SELECT 
                        person.FIRSTNAME, 
                        person.LASTNAME,
                        CONCAT(FORMAT((COUNT(CASE WHEN points.POINTS = 0 THEN 1 ELSE NULL END) / COUNT(*)) * 100, 2), \'%\') AS pecent_zero_points,
                        CONCAT(FORMAT((COUNT(CASE WHEN points.POINTS = 1 THEN 1 ELSE NULL END) / COUNT(*)) * 100, 2), \'%\') AS pecent_one_points,
                        CONCAT(FORMAT((COUNT(CASE WHEN points.POINTS = 2 THEN 1 ELSE NULL END) / COUNT(*)) * 100, 2), \'%\') AS pecent_two_points,
                        CONCAT(FORMAT((COUNT(CASE WHEN points.POINTS = 3 THEN 1 ELSE NULL END) / COUNT(*)) * 100, 2), \'%\') AS pecent_three_points,
                        CONCAT(FORMAT((COUNT(CASE WHEN points.POINTS = 4 THEN 1 ELSE NULL END) / COUNT(*)) * 100, 2), \'%\') AS pecent_four_points,
                        CONCAT(FORMAT((COUNT(CASE WHEN points.POINTS = 5 THEN 1 ELSE NULL END) / COUNT(*)) * 100, 2), \'%\') AS pecent_five_points,
                        ROUND(AVG(points.POINTS), 2) AS Moyenne
                    FROM 
                        person
                    JOIN 
                        points ON points.CANDIDATE_ID = person.ID
                    GROUP BY 
                        person.FIRSTNAME, 
                        person.LASTNAME
                    ORDER BY 
                        ' . $order_column . ' ASC');
$req->execute();
?>

<div class="container d-flex flex-column align-items-center card shadow rounded-2 mt-8 mx-auto custom-bg-color p-5 pt-4 mt-4">
    <h2>Statistiques des candidats</h2>
    <p>Choisir la colonne de tri :</p>
    <form method="get" action="statistique-candidate.php">
        <div class="input-group mb-3 mt-2">
            <select class="form-select" style = "width: 300px;" name="order">
                <option value="">Nom de la Colonne de tri</option>
                <option value="Moyenne" <?php if ($order_column === 'Moyenne') echo 'selected'; ?>>Moyenne</option>
                <option value="pecent_zero_points" <?php if ($order_column === 'pecent_zero_points') echo 'selected'; ?>>0 points</option>
                <option value="pecent_one_points" <?php if ($order_column === 'pecent_one_points') echo 'selected'; ?>>1 point</option>
                <option value="pecent_two_points" <?php if ($order_column === 'pecent_two_points') echo 'selected'; ?>>2 points</option>
                <option value="pecent_three_points" <?php if ($order_column === 'pecent_three_points') echo 'selected'; ?>>3 points</option>
                <option value="pecent_four_points" <?php if ($order_column === 'pecent_four_points') echo 'selected'; ?>>4 points</option>
                <option value="pecent_five_points" <?php if ($order_column === 'pecent_five_points') echo 'selected'; ?>>5 points</option>
                <option value="FIRSTNAME" <?php if ($order_column === 'FIRSTNAME') echo 'selected'; ?>>Prénom</option>
                <option value="LASTNAME" <?php if ($order_column === 'LASTNAME') echo 'selected'; ?>>Nom</option>
            </select>
        </div>
        <div class="d-grid gap-2">
            <button type="submit" class="btn custom-btn">Afficher les statistiques</button>
        </div>
    </form>

    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>Prénom</th>
                <th>Nom</th>
                <th>0 points</th>
                <th>1 point</th>
                <th>2 points</th>
                <th>3 points</th>
                <th>4 points</th>
                <th>5 points</th>
                <th>Moyenne</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $req->fetch(PDO::FETCH_ASSOC)) : ?>
                <tr>
                    <td><?= $row['FIRSTNAME'] ?></td>
                    <td><?= $row['LASTNAME'] ?></td>
                    <td><?= $row['pecent_zero_points'] ?></td>
                    <td><?= $row['pecent_one_points'] ?></td>
                    <td><?= $row['pecent_two_points'] ?></td>
                    <td><?= $row['pecent_three_points'] ?></td>
                    <td><?= $row['pecent_four_points'] ?></td>
                    <td><?= $row['pecent_five_points'] ?></td>
                    <td><?= $row['Moyenne'] ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

