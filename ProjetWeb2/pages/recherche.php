<?php
    session_start();
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

    // Vérifie si un ingrédient ou un de ses enfants est présent dans la recette
    function recetteContientIngredient($recetteIndex, $ingredientCible) {
        global $Hierarchie;
        $variantes = trouverToutDescendant($ingredientCible, $Hierarchie);
        
        foreach ($recetteIndex as $ingrRecette) {
            foreach ($variantes as $variante) {
                if (strcasecmp($ingrRecette, $variante) === 0) return true;
                if (stripos($ingrRecette, $variante) !== false) return true; 
            }
        }
        return false;
    }

    $rechercheEffectuee = false;
    $erreurSyntaxe = "";
    $messageResultat = "";
    
    $listeSouhaites = [];
    $listeNonSouhaites = [];
    $listeNonReconnus = [];
    
    $recettesFinales = [];

    if (isset($_POST['rechercheText'])) {
    $rechercheEffectuee = true;
    $texte = $_POST['rechercheText'];
    $texteAffichage = $texte; //Sauvegarde pour l'affichage HTML

    $plus = [];
    $moins = [];
    $sansSigne = [];
    $premierMot = null;
    
    //Normalisation des guillemets
    $texte = str_replace(['“','”','«','»'], '"', $texte);
    
    //Base de recherche (restreinte si guillemets, sinon toutes)
    $baseRecettesTitres = []; 

    //Gestion des guillemets (Recherche Exacte)
    preg_match_all('/([+-]?)\s*(?:"([^"]+)"|([^\s]+))/', $texte, $matches, PREG_SET_ORDER);
    foreach ($matches as $match) {
        $signe = $match[1];
        $mot = !empty($match[2]) ? $match[2] : $match[3]; 

        if (empty($signe) && !empty($mot) && (substr($mot, 0, 1) === '+' || substr($mot, 0, 1) === '-')) {
            $signe = substr($mot, 0, 1); // On récupère le signe
            $mot = substr($mot, 1);      // On l'enlève du mot
        }

        if (!ingredientExiste($mot)) {
            $listeNonReconnus[] = $mot; 
        } else {
            if ($signe === '-') {
                $listeNonSouhaites[] = $mot;
            } else {
                $listeSouhaites[] = $mot;
            }
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
    $totalCriteres = count($listeSouhaites) + count($listeNonSouhaites);

    if ($totalCriteres === 0 && empty($listeNonReconnus)) {
            $messageResultat = "Problème dans votre requête : recherche impossible";
    } elseif ($totalCriteres > 0) {
        
        foreach ($Recettes as $recette) {
            $satisfactionCount = 0;
            
            // Critères "Souhaités" : La recette DOIT contenir l'ingrédient
            foreach ($listeSouhaites as $ing) {
                if (recetteContientIngredient($recette['index'], $ing)) {
                    $satisfactionCount++;
                }
            }

            // Critères "Non Souhaités" : La recette NE DOIT PAS contenir l'ingrédient
            foreach ($listeNonSouhaites as $ing) {
                if (!recetteContientIngredient($recette['index'], $ing)) {
                    // Si elle ne le contient pas, le critère est satisfait !
                    $satisfactionCount++;
                }
            }

            // Calcul du score en %
            $score = ($satisfactionCount / $totalCriteres) * 100;

            // Filtrage selon le mode (Exact vs Approximatif)
            if ($totalCriteres === 1) {
                // Recherche Exacte : Il faut 100% (le seul critère doit être valide)
                if ($score == 100) {
                    $recettesFinales[] = ['recette' => $recette, 'score' => 100];
                }
            } else {
                // Recherche Approximative : On garde si score > 0
                if ($score > 0) {
                    $recettesFinales[] = ['recette' => $recette, 'score' => $score];
                }
            }
        }

        //Tri par pertinence (Score décroissant)
        usort($recettesFinales, function($a, $b) {
            if ($a['score'] == $b['score']) {
                return 0;
            }
            return ($a['score'] > $b['score']) ? -1 : 1;
        });
    }
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
    <script src="../js/rechercheValidator.js"></script>
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
            <?php if ($erreurSyntaxe): ?>
                <?php echo "<p>" . $erreurSyntaxe . "</p>"; ?>
            <?php elseif ($messageResultat): ?>
                <?php echo "<p>" . $messageResultat . "</p>"; ?>
            <?php else: ?>
                <?php if (!empty($listeSouhaites)): ?>
                    <p>Liste des aliments souhaités : <?php echo implode(', ', $listeSouhaites); ?></p>
                <?php endif; ?>

                <?php if (!empty($listeNonSouhaites)): ?>
                    <p>Liste des aliments non souhaités : <?php echo implode(', ', $listeNonSouhaites); ?></p>
                <?php endif; ?>

                <?php if (!empty($listeNonReconnus)): ?>
                    <p>Éléments non reconnus dans la requête : <span><?php echo implode(', ', $listeNonReconnus); ?></span></p>
                <?php endif; ?>

            <?php endif; ?>
        </div>
    <?php } ?>

    <!--séction contenant les recettes synthétiques-->
    <?php echo "<p>Nombre de recettes trouvés : " . htmlspecialchars(count($recettesFinales)) . "</p>"; ?>
    <div id="recettes">
        <?php
            $listeRecettes = isset($recettesFinales) ? $recettesFinales : [];

            if (empty($listeRecettes) && $rechercheEffectuee) {
                echo "<p>Aucune recette trouvée avec ces critères.</p>";
            }

            foreach($listeRecettes as $item) {
                $recetteData = $item['recette']; // Les infos de la recette (titre, index, etc.)
                $score = $item['score'];         // Le score de pertinence
                $titreRecette = $recetteData['titre']; // Le titre (ex: "Mojito")
                
                // Calcul du nom de la photo
                $nomFichierPhoto = str_replace(' ', '_', $titreRecette);
                $cheminPhoto = "../Photos/" . $nomFichierPhoto . ".jpg";
                if (!file_exists($cheminPhoto)) {
                    $cheminPhoto = "../Photos/default.jpg";
                }
                ?>
                <div style="border: solid;">
                    <div class="cocktail" id ="<?php echo convert_to_underscore($titreRecette);?>">
                        <h3><?php echo $titreRecette;?></h3> 
                        <span style="font-weight:bold; color: <?php echo ($score == 100 ? 'green' : '#d35400'); ?>">
                                <?php echo number_format($score, 0); ?>%
                        </span>

                        <img src="<?php echo $cheminPhoto; ?>" alt="<?php echo $titreRecette; ?>" height="200"/>
                        <h4>Ingrédients :</h4>
                        <ul>
                            <?php
                            // On utilise directement $recetteData['index'] qui contient les ingrédients
                            foreach($recetteData['index'] as $ingredient) {
                                echo "<li>" . $ingredient . "</li>";
                            }
                            ?>
                        </ul></div>
                        
                        <div id ="<?php echo convert_to_underscore($titreRecette) . "_like";?>">
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
                            if(array_search($titreRecette, $arrayLiked) === false) {
                                ?><img src="../Photos/heartLess.png" alt="coeur vide" height="20" class="heartLess" id="<?php echo convert_to_underscore($titreRecette)."_";?>"><?php
                            } else {
                                ?><img src="../Photos/heartFull.png" alt="coeur rouge" height="20" class="heartFull" id="<?php echo convert_to_underscore($titreRecette)."_";?>"><?php 
                            }
                        } else {
                            ?><img src="../Photos/heartLess.png" alt="coeur vide" height="20" class="heartLess" id="<?php echo convert_to_underscore($titreRecette)."_";?>"><?php
                        }
                    ?></div>
                </div>
                <?php
            }
        ?>
    </div>
</body>
</html>