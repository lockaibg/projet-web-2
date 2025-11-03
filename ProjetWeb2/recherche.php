<?php
if (isset($_POST['texte'])) {
    $texte = $_POST['texte'];
    $mots = preg_split('/\s+/', trim($texte));
    header('Content-Type: application/json');
    echo json_encode($mots);
}
?>