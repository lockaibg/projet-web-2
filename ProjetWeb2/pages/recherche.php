<?php
    include "../php/convertUnderScore.php";
    include "../php/Donnees.inc.php";
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

    $rechercheEffectuee = false;
    $texteRecherche = "";
    $plusAffichage = [];
    $moinsAffichage = [];
    $nonReconnu = [];
    $recettesFinales = [];
    $recettesFinalesAvecScores = [];

    if (isset($_POST['rechercheText'])) {
    $rechercheEffectuee = true;
    $texte = $_POST['rechercheText'];
    $texteRecherche = $texte; //Sauvegarde pour l'affichage HTML

    $plus = [];
    $moins = [];
    $sansSigne = [];
    $premierMot = null;
    
    //Normalisation des guillemets
    $texte = str_replace(['“','”','«','»'], '"', $texte);
    
    //Base de recherche (restreinte si guillemets, sinon toutes)
    $baseRecettesTitres = []; 

    //Gestion des guillemets (Recherche Exacte)
    if (preg_match('/^"([^"]+)"\s*(.*)$/', $texte, $match)) {
        $premierMot = $match[1];
        $texte = trim($match[2]);
        $ingredientsMotExact = trouverToutDescendant($premierMot, $Hierarchie);

        $resultatsProv = [];
        foreach ($ingredientsMotExact as $ing) {
            $resultatsProv = array_merge($resultatsProv, trouverRecettes($ing, $Recettes));
        }

        if (empty($resultatsProv)) {
            $nonReconnu[] = $premierMot;
        } else {
            $plus[] = $premierMot;
            $baseRecettesTitres = $resultatsProv;
        }
    }

    //Séparation des mots restants
    $mots = preg_split('/\s+/', trim($texte), -1, PREG_SPLIT_NO_EMPTY);

    foreach ($mots as $mot) {
        if (substr($mot, 0, 1) === '+') {
            $plus[] = substr($mot, 1);
        } elseif (substr($mot, 0, 1) === '-') {
            $moins[] = substr($mot, 1);
        } else {
            $sansSigne[] = $mot;
        }
    }

    //Configuration des poids
    $POIDS_PRINCIPAL = 10;
    $POIDS_PLUS = 3;
    $POIDS_MOINS = -10;

    $termesSouhaites = array_unique(array_merge($plus, $sansSigne));
    $recettesScores = [];

    //Si pas de mot clé principal (guillemets), la base est toutes les recettes
    if (empty($baseRecettesTitres) && $premierMot === null) {
        foreach ($Recettes as $recette) {
            $baseRecettesTitres[] = $recette['titre'];
        }
    }

    //Initialisation des scores à 0
    foreach ($Recettes as $recette) {
        if (in_array($recette['titre'], $baseRecettesTitres)) {
            $recettesScores[$recette['titre']] = [
                'titre' => $recette['titre'],
                'score' => 0,
                'ingredients' => $recette['index']
            ];
        }
    }

    //Calcul Bonus
    foreach ($termesSouhaites as $oblig) {
        if (!ingredientExiste($oblig)) {
            $nonReconnu[] = $oblig;
            continue;
        }
        $plusAffichage[] = $oblig;
        $descendants = trouverToutDescendant($oblig, $Hierarchie);
        
        $poids = ($oblig === $premierMot) ? $POIDS_PRINCIPAL : $POIDS_PLUS;

        foreach ($recettesScores as $titre => $data) {
            foreach ($data['ingredients'] as $ingrRecette) {
                if (in_array($ingrRecette, $descendants)) {
                    $recettesScores[$titre]['score'] += $poids;
                    break;
                }
            }
        }
    }

    //Calcul Malus
    foreach ($moins as $interdit) {
        if (!ingredientExiste($interdit)) {
            $nonReconnu[] = $interdit;
            continue;
        }
        $moinsAffichage[] = $interdit;
        $descendants = trouverToutDescendant($interdit, $Hierarchie);

        foreach ($recettesScores as $titre => $data) {
            foreach ($data['ingredients'] as $ingrRecette) {
                if (in_array($ingrRecette, $descendants)) {
                    $recettesScores[$titre]['score'] += $POIDS_MOINS;
                    break;
                }
            }
        }
    }

    //Tri
    $recettesFiltrees = $recettesScores;
    //On vérifie qu'il y a des recettes à trier
    if (!empty($recettesFiltrees)) {
        $scores = array_column($recettesFiltrees, 'score');
        array_multisort($scores, SORT_DESC, $recettesFiltrees);
    }

    //Filtrage final (Affichage uniquement si score positif ou mot exact présent)
    foreach ($recettesFiltrees as $data) {
        if ($data['score'] > 0 || ($premierMot !== null && $data['score'] > -5)) {
            $recettesFinalesAvecScores[$data['titre']] = $data['score'];
        }
    }

    $recettesFinales = array_keys($recettesFinalesAvecScores);
                
    }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/> 
    <title>Recherche</title>
    <link rel="stylesheet" href="../styles.css">
    <script src="../js/convertUnderScore.js"></script>
    <script src="../js/index_like_dislike.js"></script>
    <script src="../js/full_receipt.js"></script>
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
    <?php if ($rechercheEffectuee) { ?>
        <div id="infos">
            <p>Tu as recherché : <?php echo htmlspecialchars($texteRecherche); ?></p>
                <?php if (!empty($plusAffichage)) { ?>
                <p>Éléments souhaités :
                    <?php foreach (array_unique($plusAffichage) as $aff) echo htmlspecialchars($aff). ", "; ?>
                </p>
                <?php } ?>
                
                <?php if (!empty($moinsAffichage)) { ?>
                <p>Éléments non souhaités :
                    <?php foreach (array_unique($moinsAffichage) as $aff) echo htmlspecialchars($aff). ", "; ?>
                </p>
                <?php } ?>

                <?php if (!empty($nonReconnu)) { ?>
                <p>Éléments non reconnus :
                    <?php foreach ($nonReconnu as $aff) echo htmlspecialchars($aff). ", "; ?>
                </p>
                <?php } ?>
        </div>
    <?php } ?>

    <!--séction contenant les recettes synthétiques-->
    <?php echo "<p>Nombre de recettes trouvés : " . htmlspecialchars(count($recettesFinales)) . "</p>"; ?>
    <div id="recettes">
        <?php
            $recettes = isset($recettesFinales) ? $recettesFinales : [];

            if (empty($recettes) && $rechercheEffectuee) {
                echo "<p>Aucune recette trouvée avec ces critères.</p>";
            }
            
            foreach($recettes as $recette) {?>
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
                        if(array_search($recette, $arrayLiked) === false) {
                            ?><img src="../Photos/heartLess.png" alt="coeur vide" height="20" class="heartLess" id="<?php echo convert_to_underscore($recette)."_";?>"><?php
                        } else {
                            ?><img src="../Photos/heartFull.png" alt="coeur rouge" height="20" class="heartFull" id="<?php echo convert_to_underscore($recette)."_";?>"><?php 
                        }
                    ?></div>
                </div>
                <?php
            }
        ?>
    </div>
</body>
</html>