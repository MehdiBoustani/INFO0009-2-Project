<?php
session_start();
include 'header.html';
include 'navbar.php';

// Database connection
$bdd = new PDO('mysql:host=db;dbname=group9;charset=utf8', 'group9', 'tabodi');
?>

<body>
    <div class="container d-flex flex-column align-items-center card shadow rounded-2 mt-8 mx-auto custom-bg-color p-5 pt-4 mt-4">
        <h1>Ajout de candidat</h1>
        <form method="post" action="ajout-candidate.php">
            <div class="input-group mb-3">
                <input type="text" class="form-control" style="width: 400px;" placeholder="Prénom" name="firstname">
            </div>
            <div class="input-group mb-3">
                <input type="text" class="form-control" style="width: 400px;" placeholder="Nom" name="lastname">
            </div>

            <div class="form-group">
                <textarea id="job" name="job" class="form-control"placeholder="Métiers (un par ligne)" rows="5"></textarea>
            </div>

            <div class="d-grid gap-2 mt-3">
                <button type="submit" class="btn custom-btn">Soumettre</button>
            </div>
        </form>
    </div>
</body>
</html>


<?php

// Vérifier si le formulaire a été soumis
if (isset($_POST['firstname'], $_POST['lastname'], $_POST['job'])) {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $job = explode("\n", trim($_POST['job']));
    $job = array_map('trim', $job);
    $job = array_filter($job);

    // Vérifier si au moins un métier est saisi
    if (!empty($job)) {

        // Insérer le candidat dans la table Person
        $req = $bdd->prepare("INSERT INTO person (FIRSTNAME, LASTNAME) VALUES (:firstname, :lastname)");
        $req->bindParam(':firstname', $firstname);
        $req->bindParam(':lastname', $lastname);
        $req->execute();
                  

        // Récupérer l'ID du nouveau candidat
        $candidate_Id = $bdd->lastInsertId();

        // Insérer les métiers du candidat dans la table job
        foreach ($job as $metier) {
            $req = $bdd->prepare("INSERT INTO job (CANDIDATE_ID,JOB) VALUES (:candidate_Id, :job)");
            $req->bindParam(':candidate_Id', $candidate_Id);
            $req->bindParam(':job', $metier);
            $req->execute();
        }

        // Afficher la liste des métiers
        echo '<ul>';
        foreach ($job as $metier) {
            echo '<li>' . $metier . '</li>';
        }
        echo '</ul>';

    } else {
        echo '<p>Saisir au moins un métier.</p>';
    }
}
?>