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
            <label for="prenomNom">Prénom et nom:</label>
            <input type="text" id ="prenomNom" name="prenomNom" required>

            <label for="job">Métiers (un par ligne):</label>
            <textarea id ="job" name="text" rows="5"></textarea>

            <input type="submit" value="Soumettre">
        </form>
    </div>
</body>
</html>


<?php

// Vérifier si le formulaire a été soumis
if (isset($_POST['prenomNom']) && isset($_POST['text'])) {
    $prenomNom = trim($_POST['prenomNom']);
    $job = explode("\n", trim($_POST['text']));
    $job = array_map('trim', $job);
    $job = array_filter($job);

    // Vérifier si au moins un métier est saisi
        if (!empty($job)) {

            // Vérifier si le candidat existe déjà
            $req = $bdd->prepare("SELECT * FROM candidate WHERE prenomNom = :prenomNom");
            $req->bindParam(':prenomNom', $prenomNom);
            $req->execute();
            $existing_candidate = $req->fetch();

            if ($existing_candidate) {
                // Récupérer l'ID du candidat existant
                $candidate_Id = $existing_candidate['ID'];
            } else {
                // Ajouter un nouveau candidat à la base de données
                $req = $bdd->prepare("INSERT INTO candidate (prenomNom) VALUES (:prenomNom)");
                $req->bindParam(':prenomNom', $prenomNom);
                $req->execute();
                // Récupérer l'ID du nouveau candidat
                $candidate_Id = $bdd->lastInsertId();
            }

            // Insérer les métiers du candidat dans la table job
            foreach ($job as $metier) {
                $req = $bdd->prepare("INSERT INTO job (CANDIDATE_ID, JOB) VALUES (:candidate_Id, :job)");
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

        }else {
            echo '<p>saisir au moins un métier.</p>';
        }
}else {
        echo '<p>saisir le prénom et le nom du candidat ainsi que ses métiers.</p>';
}


?>