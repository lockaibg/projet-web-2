<?php

include "Donnees.inc.php";

if (isset($_POST['rechercheText'])) {
    $texte = $_POST['rechercheText'];

    echo "Tu as recherché : " . htmlspecialchars($texte); echo "<br/>";

    $elementsRecherche = [];
    $elementsPasRecherche = [];
    $elementsPasCompris = [];


    function ingredientExiste($ingredient) {

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

        // Rien trouvé
        return false;
    }


    if (preg_match('/^"([^"]+)"(.*)$/', $texte, $matches)) {
        $premierMot = $matches[1];
        $reste = $matches[2];

        echo "prmeier mot: $premierMot<br/>";
        echo "reste: $reste<br/>";


    } else {
        echo "Problème de syntaxe dans votre requête : nombre impair de double-quotes";
    }
}
?>