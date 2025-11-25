<?php
session_start();

if (isset($_POST['cle']) && isset($_POST['valeur'])) {
    $_SESSION[$_POST['cle']] = $_POST['valeur'];
    echo "Session mise à jour : " . $_POST['cle'] . " = " . $_POST['valeur'];
} else {
    echo "Données manquantes";
}
?>