<nav class="navbar navbar-expand-md navbar-dark bg-custom">
    <div class="container-fluid">
        <a class="navbar-brand" href="./index.php">Taskmaster</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <?php if(isset($_SESSION['login'])): ?>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class = "nav-link" href="./tables.php">Selection de données</a>
                    </li>
                    <li class="nav-item">
                        <a class = "nav-link" href="./ajout-candidate.php">Ajout de candidat</a>
                    </li>
                    <li class="nav-item">
                        <a class = "nav-link" href="./episode.php">Modification d'épisode</a>
                    </li>
                    <li class="nav-item">
                        <a class = "nav-link" href="./statistique-candidate.php">Statistiques des candidats</a>
                    </li>
                    <li class="nav-item">
                        <a class = "nav-link" href="./table-points.php">Points par tâche pour un épisode</a>
                    </li>
                    <li class="nav-item">
                        <a class = "nav-link" href="./affiche-personne.php">Nombre et liste d'épisodes gagnés par personne</a>
                    </li>
                    <li class="nav-item">
                        <a class = "nav-link" href="./totaux-glissants.php">Totaux cumulatifs des candidats</a>
                    </li>
                  
                </ul>
            <?php endif; ?>
        </div>
        <ul class="navbar-nav ml-auto">
            <?php if(isset($_SESSION['login'])): ?>
                <li class="nav-item">
                    <form method="post" action="login.php">
                        <input type="hidden" name="disconnect" value="yes">
                        <button type="submit" class="nav-link btn btn-link">Déconnexion</button>
                    </form>
                </li>
            <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="./login.php">Connexion</a>
                </li>
            <?php endif; ?>
        </ul>
        
    </div>
</nav>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>


