<?php
if (isset($_POST['texte'])) {
    $texte = $_POST['texte'];
    // Exemple de traitement
    $mots = preg_split('/\s+/', trim($texte));
    // Afficher ou enregistrer le résultat
    print_r($mots);
}
?>