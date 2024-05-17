<?php
    session_start();
    include 'header.html';
    include 'navbar.php';

// Database connection
$bdd = new PDO('mysql:host=db;dbname=group9;charset=utf8', 'group9', 'tabodi');

// Exécution de la requête
$sql = "
SELECT p.ID, p.FIRSTNAME, p.LASTNAME, 
       COUNT(e.WINNER_ID) AS nb_wins, 
       GROUP_CONCAT(e.TITLE ORDER BY e.AIRDATE SEPARATOR ', ') AS list_won_episodes
FROM person p
LEFT JOIN episode e ON p.ID = e.WINNER_ID
GROUP BY p.ID, p.FIRSTNAME, p.LASTNAME;
";
$req = $bdd->query($sql);

// Récupération des résultats
$persons = $req->fetchAll();

?>
<div class="container d-flex flex-column align-items-center card shadow rounded-2 mt-8 mx-auto custom-bg-color p-5 pt-4 mt-4 mb-4">
    <h2> Nombre et liste d'épisodes gagnés par personne</h2>
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Nombre d'épisodes gagnés</th>
                <th>Liste des épisodes gagnés</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($persons as $person): ?>
                <tr>
                    <td><?= ($person['ID']) ?></td>
                    <td><?= ($person['LASTNAME']) ?></td>
                    <td><?= ($person['FIRSTNAME']) ?></td>
                    <td><?= ($person['nb_wins']) ?></td>
                    <td><?= ($person['list_won_episodes']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>


