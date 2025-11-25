<?php 
    include "../php/Donnees.inc.php";
    session_start();
    if(isset($_POST["elem"])) {
        $cocktail = $_POST["elem"];
    } else {

    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/> 
    <title><?php echo $cocktail?></title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <header>
        <ul>
            <li><a href="index.php">Navigation</a></li>
            <li><a href="liked.php">Recettes</a><img src="../Photos/heartFull.png" alt="coeur rouge" height="20"></li>
            <li><!--recherche via syntaxe-->
                <form id="recherche" action="recherche.php" method="POST" onsubmit="return validerRecherche();">
                    <label>Recherche</label>
                    <input type="text" id="rechercheText" name="rechercheText" />
                    <input type="submit" value="Valider">
                </form>
            </li>
            <li><ul><?php
                if(isset($_SESSION["login"])) { 
                    ?><li><?php
                    echo $_SESSION["login"];
                    ?>
                    </li>
                    <li>
                        <form action="../php/profil.php">
                            <input type="submit" value="Profil">
                        </form>
                    </li>
                    <li>
                        <form action="../php/deconnexion.php">
                            <input type="submit" value="Se déconnecter">
                        </form>
                    </li>
                    <?php
                } else {
                    ?>
                    <li>
                        <form method="POST" action="../php/connexion.php?page=index">
                            <label for="login">Login</label>
                            <input type="text" id="login" name="login" required><br><br>

                            <label for="mdp">Mot de passe :</label>
                            <input type="password" id="mdp" name="mdp" required><br><br>

                            <input type="submit" value="Connexion">
                        </form>
                    </li>
                    <?php
                        if(isset($_GET["err"])) {
                            if($_GET["err"] == "psw") {
                                ?>
                                    <li style="color: red;">mot de passe incorrecte</li>
                                <?php
                            } else if($_GET["err"] == "login") {
                                ?>
                                    <li style="color: red;">login introuvable</li>
                                <?php
                            }
                        }
                    ?>
                    <li>
                        <form action="register.php">
                            <input type="submit" value="S'inscrire">
                        </form>
                    </li>
                    <?php
                }
            ?></ul></li>
        </ul>
    </header>

    <?php
        foreach($Recettes as $indice => $cocktails) {
            if($cocktails['titre'] === $cocktail) {
                ?>
                <div id ="recette" style="border: solid;" class="cocktail">
                    <h1><?php echo $cocktail; ?></h1><?php
                    //afficher la photo si elle existe
                    $textPhoto = "../Photos/".str_replace(' ', '_', $cocktail).".jpg";
                    ?>
                        <h2>Ingrédients :</h2>
                        <p> <?php echo $cocktails['ingredients'] ?></p>
                        <h2>Recette :</h2>
                        <p> <?php echo $cocktails['preparation'] ?></p>
                    <?php
                    if(file_exists($textPhoto)){?>
                        <img src="<?php echo $textPhoto?>" alt="<?php echo $textPhoto?>" height="200"/>
                        <?php
                    } else {
                        ?><img src="../Photos/default.jpg" alt="default for <?php echo $textPhoto?>" height="200"/><?php
                    }
                ?></div><?php
            }
        }
    ?>
</body>