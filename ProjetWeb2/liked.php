<?php
    session_start();
    include "Donnees.inc.php";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/> 
    <title>Recettes likés</title>
    <link rel="stylesheet" href="../styles.css">
    <script>
        window.onload = function () {
            document.querySelectorAll(".heartFull").forEach(heart => {
                heart.addEventListener("click", (event) => {
                    const id = event.currentTarget.id;
                    event.currentTarget.src = "Photos/heartLess.png";
                    event.currentTarget.class = "heartLess";
                    event.currentTarget.alt = "coeur vide";
                    fetch(`retirerLike.php?cocktail=${encodeURIComponent(id)}`, {
                    method: "GET",
                    cache: "no-store"
                    })
                    .then(response => response.text())
                    .then(result => {
                        console.log("Réponse du serveur :", result);
                    })
                    .catch(error => console.error("Erreur :", error));
                });
            });
        }
    </script>
</head>
<body>
    <!--menu de haut de page-->
    <header>
        <ul>
            <li><a href="index.php">Navigation</a></li>
            <li><a href="liked.php">Recettes</a><img src="Photos/heartFull.png" alt="coeur rouge" height="20"></li>
            <li><form><!--à faire--></form></li>
            <li><ul><?php
                if(isset($_SESSION["login"])) { 
                    ?><li><?php
                    echo $_SESSION["login"];
                    ?>
                    </li>
                    <li>
                        <form action="profil.php">
                            <input type="submit" value="Profil">
                        </form>
                    </li>
                    <li>
                        <form action="deconnexion.php">
                            <input type="submit" value="Se déconnecter">
                        </form>
                    </li>
                    <?php
                } else {
                    ?>
                    <li>
                        <form method="POST" action="connexion.php?page=liked">
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
    <!--liste des recettes liké-->
    <div id="recettes">
    <?php
        //si l'utilisateur est connecté passer par le fichier json
        $arrayLiked;
        if(isset($_SESSION["login"])) {
            $json = file_get_contents("user.json");
            $data = json_decode($json, true);
            foreach($data as $indice => $user) {
                if($user["login"] === $_SESSION["login"]) {
                    $arrayLiked = $user["liked"];
                    break;
                }
            }
        //si l'utilisateur est déconnecter utiliser la varriable de session "liked"
        } else if(isset($_SESSION["liked"])) {
            $arrayLiked = $_SESSION["liked"];
        } else {
            $arrayLiked = array();
        }
        foreach($arrayLiked as $recette) {?>
            <div id ="<?php echo $recette;?>" style="border: solid;">
                <?php echo $recette;?> 
            <?php
            //afficher la photo si elle existe
            $textPhoto = "Photos/".str_replace(' ', '_', $recette).".jpg";
            if(file_exists($textPhoto)){?>
                <img src="<?php echo $textPhoto?>" alt="<?php echo $textPhoto?>" height="200"/>
                <?php
            } else {
                ?><img src="Photos/default.jpg" alt="default for <?php echo $textPhoto?>" height="200"/><?php
            }
            foreach($Recettes as $recInfos) {
                if($recInfos['titre'] === $recette) {
                    ?><ul><?php
                    foreach($recInfos['index'] as $ingr) {
                        echo "<li>".$ingr."</li>";
                    }
                    break;
                    ?></ul><?php
                }
            }
            ?>
            <img src="Photos/heartFull.png" alt="coeur entié" height="20" class="heartFull" id="<?php echo $recette;?>">
        </div><?php
        }   
    ?></div>
</body>
</html>