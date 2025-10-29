<?php
    include "Donnees.inc.php";
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
    
    if(isset($_GET["selection"])) $select = $_GET["selection"]; //initialisation de la clef de recherche
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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/> 
    <title><?php echo $select?></title>
    <link rel="stylesheet" href="../styles.css">    
    <script>
        //script utilisé pour update la varriable de session visant a garder en mémoire le fil d'ariane
        const beforeAriane = "<?php echo addslashes($fil); ?>";
        window.onload = function () {
            document.querySelectorAll(".elemClickable").forEach(lien => {
                lien.addEventListener("click", async (event) => {
                    event.preventDefault();
                    const id = event.currentTarget.id;
                    await fetch("majSession.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: "cle=filAriane&valeur="+encodeURIComponent(beforeAriane + id + "."),
                        keepalive: true
                    });
                    console.log(id);
                    window.location.href = lien.href;
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
            <li><a href="liked.php">Recettes</a><img src="Photos/heartFull.png" alt="coeur rouge"></li>
            <li><form><!--à faire--></form></li>
            <li><ul><li><?php
            
                if(isset($_SESSION["login"])) { 
                    echo $_SESSION["login"];
                    ?>
                    </li>
                    <li>
                        <form action="profil.php">
                            <input type="submit" value="Profil">
                        </form>
                    </li>
                    <li>
                        <form action="connexion.php">
                            <input type="submit" value="Se connecter">
                        </form>
                    </li>
                    <?php
                }
            ?></ul></li>
        </ul>
    </header>
    <!--afficher le fil d'ariane si il existe-->
    <?php 
        if ($fil != "") {
            ?><div id="filAriane"><?php echo $fil?></div><?php
        }
    ?>
    <!--Séction contenant la navigation-->
    <div id="selection" style="border: solid;">
        <ul>
        <?php
            foreach($Hierarchie as $element => $fils) { 
                if($element === $select){ //recherche de l'element
                    foreach($fils as $cats => $liste){
                        if($cats === "sous-categorie") {
                            foreach($liste as $elem) { //affichage de tout les fils de l'element trouvé
                            ?> 
                                <li><a id ="<?php echo $elem?>" href="index.php?selection=<?php echo $elem?>"class="elemClickable"><?php echo $elem?></a></li>                                                        
                            <?php
                            }
                        }
                    }
                }
            }
        ?>
        </ul>

    </div>
    <!--séction contenant les recettes synthétiques-->
    <div id="recettes">
        <?php
            $ingredients = trouverToutDescendant($select, $Hierarchie);
            $recettes = array();
            foreach($ingredients as $ingredient) {
                $recettes = array_merge($recettes, trouverRecettes($ingredient, $Recettes));
            }
            foreach($recettes as $recette) {?>
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
                ?></div><?php
            }
        ?>
    </div>
</body>
</html>