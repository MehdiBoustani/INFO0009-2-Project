<?php
    session_start();
    include 'header.html';
    include 'navbar.php';

    // Database connection
    $bdd = new PDO('mysql:host=db;dbname=group9;charset=utf8', 'group9', 'tabodi');

    // Vérifier si le formulaire a été soumis
    if (isset($_POST['firstname'])&& isset($_POST['lastname'])&& isset($_POST['job'])) {
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $job = explode("\n", trim($_POST['job']));
        $job = array_map('trim', $job);
        $job = array_filter($job);

        // Vérifier si au moins un métier est saisi
        if (!empty($job)) {

            // Vérifier si le candidat existe déjà dans la table Person
            $req = $bdd->prepare("SELECT ID FROM person WHERE FIRSTNAME = :firstname AND LASTNAME = :lastname");
            $req->bindParam(':firstname', $firstname);
            $req->bindParam(':lastname', $lastname);
            $req->execute();

            if ($req->rowCount() > 0) {
                // Le candidat existe déjà, afficher un message approprié
                echo "<div class='error-box'>Le candidat avec le même prénom et nom existe déjà dans la base de données.</div>";
            } else {
                
                // Insérer le nom et le prénom du candidat dans la table Person
                $req = $bdd->prepare("INSERT INTO person (FIRSTNAME, LASTNAME) VALUES (:firstname, :lastname)");
                $req->bindParam(':firstname', $firstname);
                $req->bindParam(':lastname', $lastname);
                $req->execute();

                // Récupérer l'ID du nouveau candidat
                $candidate_Id = $bdd->lastInsertId();
                $req = $bdd->prepare("INSERT INTO candidate (ID) VALUES (:id)");
                $req->bindParam(':id', $candidate_Id);
                $req->execute();

                // Insérer les métiers du candidat dans la table job avec le même ID
                foreach ($job as $metier) {
                    $req = $bdd->prepare("INSERT INTO job (CANDIDATE_ID, JOB) VALUES (:candidate_Id, :job)");
                    $req->bindParam(':candidate_Id', $candidate_Id);
                    $req->bindParam(':job', $metier);
                    $req->execute();
                }
            }

        } else {
            echo "<div class='error-box'>Veuillez saisir au moins un métier.</div>";
        }
    }
?>

<body>
    <div class="container d-flex flex-column align-items-center card shadow rounded-2 mt-8 mx-auto custom-bg-color p-5 pt-4 mt-4">
        <h2>Ajout de candidat</h2>
        <form method="post" action="ajout-candidat.php">
            <div class="input-group mb-3 mt-2">
                <input type="text" class="form-control" style="width: 400px;" placeholder="Prénom" name="firstname" required>
            </div>
            <div class="input-group mb-3">
                <input type="text" class="form-control" style="width: 400px;" placeholder="Nom" name="lastname" required>
            </div>

            <div class="form-group">
                <textarea id="job" name="job" class="form-control" placeholder="Métiers (un par ligne)" rows="5" required></textarea>
            </div>

            <div class="d-grid gap-2 mt-3">
                <button type="submit" class="btn custom-btn">Soumettre</button>
            </div>
        </form>
    </div>
</body>
</html>