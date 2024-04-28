<?php
session_start();
include 'header.html';
include 'navbar.php';
?>
    <body>
        <div class="container d-flex flex-column align-items-center shadow rounded-2 mt-8 mx-auto custom-bg-color p-5 pt-4 mt-4">
            <h1>Registre National</h1>
            <!-- Insertion de code PHP -->
            <?php
            for ($i = 1; $i <= 4; $i++) {
                echo "<p>Paragraphe $i </p>";
            }
            ?>
        </div>
    </body>
</html>