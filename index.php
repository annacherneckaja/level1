<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>My level1</title>
</head>
<body>

<?php require "blocks/header.php"?>
    <div class="album py-5 bg-light">
        <div class="container">
            <div class="row">
                <?php
                for ($i = 0; $i < 6; $i++):
                    ?>
                    <div class="col-md-4">
                        <div class="card mb-4 shadow-sm align-items-center">
                            <svg class="bd-placeholder-img card-img-top" width="100%" height="25">
                                <rect width="100%" height="100%" fill="#147cc9"></rect>
                                <text x="40%" y="50%" fill="#eceeef" dy=".3em">Task <?php echo($i + 1) ?></text>

                            </svg>

                            <div class="card-body">
                                <p class="card-text">здесь можно написать задачу</p>
                                <div class="d-flex justify-content-between">
                                    <a type="button" class="btn btn-outline-primary" href ="level1_<?php echo($i + 1) ?>.php">Решение</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>


</body>
</html>