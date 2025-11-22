<?php

include "Donnees.inc.php";

if (isset($_POST['rechercheText'])) {
    $texte = $_POST['rechercheText'];

    echo "Tu as recherché : " . htmlspecialchars($texte); echo "<br/>";

    $elementsRecherche = [];
    $elementsPasRecherche = [];
    $elementsPasCompris = [];


    function ingredientExiste($ingredient) {
        global $Recettes, $Hierarchie;
        // --- Vérifie dans $Recettes (dans les index)
        foreach ($Recettes as $recette) {
            foreach ($recette['index'] as $motCle) {
                if (strcasecmp($motCle, $ingredient) === 0) {
                    return true;
                }
            }
        }

        // --- Vérifie dans $Hierarchie (clés + sous/super-catégories)
        foreach ($Hierarchie as $categorie => $infos) {
            // clé principale
            if (strcasecmp($categorie, $ingredient) === 0) {
                return true;
            }
            // sous-catégories
            if (isset($infos['sous-categorie'])) {
                foreach ($infos['sous-categorie'] as $sous) {
                    if (strcasecmp($sous, $ingredient) === 0) {
                        return true;
                    }
                }
            }
            // super-catégories
            if (isset($infos['super-categorie'])) {
                foreach ($infos['super-categorie'] as $super) {
                    if (strcasecmp($super, $ingredient) === 0) {
                        return true;
                    }
                }
            }
        }
        
        // Vérifie si l'ingrédient apparaît partiellement dans les mots-clés
        foreach ($Recettes as $recette) {
            foreach ($recette['index'] as $motCle) {
                if (stripos($motCle, $ingredient) !== false) {
                    return true;
                }
            }
        }
        foreach ($Hierarchie as $categorie => $infos) {
            if (stripos($categorie, $ingredient) !== false) return true;

            if (isset($infos['sous-categorie'])) {
                foreach ($infos['sous-categorie'] as $sous) {
                    if (stripos($sous, $ingredient) !== false) return true;
                }
            }
        }
        // Rien trouvé
        return false;
    }

    //fonction permettant de trouver une recette a partir d'un ingrédient passer en parametre
    function trouverRecettes($ingredient, $recettes) {
        $retour = [];
        foreach ($recettes as $recette) {
            foreach ($recette['index'] as $ing) {
                if (strcasecmp($ing, $ingredient) === 0) {
                    $retour[] = $recette['titre'];
                    break;
                }
                if (stripos($ing, $ingredient) !== false) {
                    $retour[] = $recette['titre'];
                    break;
                }
            }
        }
        return $retour;
    }

    //fonction pour trouver toutes les feuilles de l'algorythme a partir de la clef sur laquel on est actuellement
    function trouverToutDescendant($node, $hierarchie) {
        if (empty($hierarchie[$node]['sous-categorie'])) {
            return [$node];
        }
        $out = [$node];
        foreach ($hierarchie[$node]["sous-categorie"] as $child) {
            $out = array_merge($out, trouverToutDescendant($child, $hierarchie));
        }
        return $out;
    }

    $plus = [];
    $plusAffichage = [];
    $moins = [];
    $moinsAffichage = [];
    $sansSigne = [];
    $nonReconnu = [];
    $recettesFinales = [];
    $texte = str_replace(['“','”','«','»'], '"', $texte);
    if (preg_match('/^"([^"]+)"\s*(.*)$/', $texte, $match)) {
        $premierMot = $match[1];
        $texte = trim($match[2]);
        $ingredientsMotExact = trouverToutDescendant($premierMot, $Hierarchie);

        $resultats = [];
        foreach ($ingredientsMotExact as $ing) {
            $resultats = array_merge($resultats, trouverRecettes($ing, $Recettes));
        }

        if (empty($resultats)) {
            $nonReconnu[] = $premierMot;
        } else {
            $plus[] = $premierMot;
        }
        
    } else {
        echo "Erreur de syntaxe : il manque des guillemets. <br/>";
    }

    // Sépare en utilisant les espaces
    $mots = explode(' ', $texte);
    $mots = preg_split('/\s+/', trim($texte));

    foreach ($mots as $mot) {
        if ($mot[0] === '+') {
            $plus[] = substr($mot, 1);
        } elseif ($mot[0] === '-') {
            $moins[] = substr($mot, 1);
        } else {
            $sansSigne[] = $mot;
        }
    }
    if (empty($resultats)) {
        foreach ($Recettes as $recette) {
            $resultats[] = $recette['titre'];
        }
    }

    foreach ($plus as $oblig) {

        if (!ingredientExiste($oblig)) {
            $nonReconnu[] = $oblig;
            continue;
        }
        $plusAffichage[] = $oblig;
        $desc = trouverToutDescendant($oblig, $Hierarchie);

        $tous = [];
        foreach ($desc as $d) {
            $tous = array_merge($tous, trouverRecettes($d, $Recettes));
        }

        $resultats = array_intersect($resultats, $tous);
    }

    foreach ($moins as $interdit) {
        if (!ingredientExiste($interdit)) {
            $nonReconnu[] = $interdit;
            continue;
        }
        $moinsAffichage[] = $interdit;
        $desc = trouverToutDescendant($interdit, $Hierarchie);

        $aExclure = [];
        foreach ($desc as $d) {
            $aExclure = array_merge($aExclure, trouverRecettes($d, $Recettes));
        }

        $resultats = array_diff($resultats, $aExclure);
    }

    foreach ($sansSigne as $mot) {
        if (ingredientExiste($mot)) {
            $plusAffichage[] = $mot;
            $desc = trouverToutDescendant($mot, $Hierarchie);
            $tous = [];
            foreach ($desc as $d) {
                $tous = array_merge($tous, trouverRecettes($d, $Recettes));
            }
            $resultats = array_intersect($resultats, $tous);
        } else {
            $nonReconnu[] = $mot;
        }
    }

    $resultats = array_unique($resultats);
    $recettesFinales = $resultats;
    
    echo "Liste éléments souhaités : "; 
    foreach ($plusAffichage as $affichage) {
        echo htmlspecialchars($affichage). ", ";
    } 
    echo "</br>";   
    echo "Liste éléments non souhaités : ";
    foreach ($moinsAffichage as $affichage) {
        echo htmlspecialchars($affichage). ", ";
    } 
    echo "</br>";
    echo "Liste éléments non reconnus : ";
    foreach ($nonReconnu as $affichage) {
        echo htmlspecialchars($affichage). ", ";
    } 
    echo "</br>";
}

?>
<!DOCTYPE html>
<html>
    <head>

    </head>
    <body>
         <!--séction contenant les recettes synthétiques-->
    <div id="recettes">
        <?php
            $recettes = isset($recettesFinales) ? $recettesFinales : [];
            
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
                //si l'utilisateur est connecté passer par le fichier json pour savoir si le cocktail est liké ou pas
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
                if(array_search($recette, $arrayLiked) === false) {
                    ?><img src="Photos/heartLess.png" alt="coeur vide" height="20" class="heartLess" id="<?php echo $recette;?>"><?php
                } else {
                    ?><img src="Photos/heartFull.png" alt="coeur rouge" height="20" class="heartFull" id="<?php echo $recette;?>"><?php 
                }
            ?></div><?php
            }
        ?>
    </div>
    </body>
</html>