<?php
    include "../php/Donnees.inc.php";
    include "../php/convertUnderScore.php";
    session_start();

    $totalAliment = array();

    //initialiser les varriable filAriane et fil pour la suite
    if(isset($_SESSION["filAriane"])) { 
        $fil = $_SESSION["filAriane"];
        $filArray = explode(".", $fil);
        unset($filArray[key(array_slice($filArray, -1, 1, true))]);
    } else {
        $fil = "Aliment.";
        $filArray = array ( 0 => "Aliment");
    }
    
    if(isset($_GET["selection"])) $select = convert_to_space($_GET["selection"]); //initialisation de la clef de recherche
    else $select = "Aliment";

    //permet de gérer le fait que l'utilisateur revienne en arrière
    if(isset($filArray)){
        foreach($filArray as $index => $value){
            
            if($value === $select) { 
                if(key(array_slice($filArray, -1, 1, true)) != $index){
                    $filArray = array_slice($filArray, 0, $index+1);
                    $fil = "";
                    break;
                }
            }
        }
        if($fil === "") {
            foreach($filArray as $index => $value) {
                $fil = $fil.$value.".";
            }
        }
    }

    //fonction pour trouver toutes les feuilles de l'algorythme a partir de la clef sur laquel on est actuellement
    function trouverToutDescendant($node, $hierarchie){
        if(empty($hierarchie[$node]['sous-categorie'])){
            return [$node];
        }
        $retourArray = array(0 => $node);
        foreach($hierarchie[$node]["sous-categorie"] as $elems){
            $retourArray = array_merge($retourArray, trouverToutDescendant($elems, $hierarchie));
        }
        return $retourArray;
    }

    //fonction permettant de trouver une recette a partir d'un ingrédient passer en parametre
    function trouverRecettes($ingredient, $recettes){
        $retour = [];
        foreach($recettes as $recette) {
            foreach($recette['index'] as $ingredients){
                if($ingredients === $ingredient){
                    array_push($retour, $recette['titre']);
                    break;
                }
            }
        }
        return $retour;
    }
    $testee = [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/> 
    <title>Navigation : <?php echo $select?></title>
    <link rel="stylesheet" href="../styles.css">    
    <script>
        //script utilisé pour update la varriable de session visant a garder en mémoire le fil d'ariane
        const beforeAriane = "<?php echo addslashes($fil); ?>";
        window.addEventListener("DOMContentLoaded", () => { 
            document.querySelectorAll(".elemClickable").forEach(lien => {
                lien.addEventListener("click", async (event) => {
                    event.preventDefault();
                    const id = event.currentTarget.id;
                    await fetch("../php/majSession.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: "cle=filAriane&valeur="+encodeURIComponent(beforeAriane + id + "."),
                        keepalive: true
                    });
                    window.location.href = lien.href;
                });
            });
        });
    </script>
    <script src="../js/convertUnderScore.js"></script>
    <script src="../js/index_like_dislike.js"></script>
    <script src="../js/full_receipt.js"></script>
    <script src="../js/rechercheValidator.js"></script>
</head>
<body>
    <!--menu de haut de page-->
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
                        <form action="profil.php">
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
    <div class="main">
        <!--afficher le fil d'ariane si il existe-->
        <nav>
            <?php 
                if ($fil != "") {
                    ?><div id="filAriane"><?php 
                        foreach($filArray as $fils) {
                            ?>
                                <a href="index.php?selection=<?php echo convert_to_underscore($fils);?>"><?php echo $fils;?></a>
                            <?php
                        }
                    ?></div><?php
                }
            ?>
            
            <!--Séction contenant la navigation-->
            <?php
                foreach($Hierarchie as $element => $fils) { 
                    if($element === $select){ //recherche de l'element
                        foreach($fils as $cats => $liste){
                            if($cats === "sous-categorie") {
                                ?><div id="selection">
                                    <ul><?php
                                    foreach($liste as $elem) { //affichage de tout les fils de l'element trouvé
                                    ?> 
                                        <li><a id ="<?php echo convert_to_underscore($elem)?>" href="index.php?selection=<?php echo convert_to_underscore($elem)?>" class="elemClickable"><?php echo $elem?></a></li>                                                        
                                    <?php
                                    }
                                    ?></ul>
                                </div><?php
                            }
                        }
                    }
                }
            ?>
        </nav>

        
        <!--séction contenant les recettes synthétiques-->
        <div id="recettes">
            <?php
                $ingredients = trouverToutDescendant($select, $Hierarchie);
                $recettes = array();
                $recetteDejaAffiche = array();
                foreach($ingredients as $ingredient) {
                    $recettes = array_merge($recettes, trouverRecettes($ingredient, $Recettes));
                }
                foreach($recettes as $recette) {
                    if(array_search($recette, $recetteDejaAffiche) === false) {
                        $recetteDejaAffiche[] = $recette;?>
                        
                        <div style="border: solid;">
                            <div class="cocktail" id ="<?php echo convert_to_underscore($recette);?>">
                                <h3><?php echo $recette;?></h3> 
                            <?php
                            //afficher la photo si elle existe
                            $textPhoto = "../Photos/".str_replace(' ', '_', $recette).".jpg";
                            if(file_exists($textPhoto)){?>
                                <img src="<?php echo $textPhoto?>" alt="<?php echo $textPhoto?>" height="200"/>
                                <?php
                            } else {
                                ?><img src="../Photos/default.jpg" alt="default for <?php echo $textPhoto?>" height="200"/><?php
                            }
                            foreach($Recettes as $recInfos) {
                                if($recInfos['titre'] === $recette) {
                                    ?><ul><?php
                                    foreach($recInfos['index'] as $ingr) {
                                        echo "<li>".$ingr."</li>";
                                    }
                                    break;
                                    
                                }
                            }
                            ?></ul></div>
                                
                                <div id ="<?php echo convert_to_underscore($recette) . "_like";?>">
                                <?php

                                //si l'utilisateur est connecté passer par le fichier json pour savoir si le cocktail est liké ou pas
                                $arrayLiked;
                                if(isset($_SESSION["login"])) {
                                    $json = file_get_contents("../user.json");
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
                                if($arrayLiked !== null) {
                                    if(array_search($recette, $arrayLiked) === false) {
                                        ?><img src="../Photos/heartLess.png" alt="coeur vide" height="20" class="heartLess" id="<?php echo convert_to_underscore($recette)."_";?>"><?php
                                    } else {
                                        ?><img src="../Photos/heartFull.png" alt="coeur rouge" height="20" class="heartFull" id="<?php echo convert_to_underscore($recette)."_";?>"><?php 
                                    }
                                } else {
                                    ?><img src="../Photos/heartLess.png" alt="coeur vide" height="20" class="heartLess" id="<?php echo convert_to_underscore($recette)."_";?>"><?php
                                }
                            ?></div>
                        </div>
                    <?php
                    }
                }
            ?>
        </div>
    </div>
</body>
</html>