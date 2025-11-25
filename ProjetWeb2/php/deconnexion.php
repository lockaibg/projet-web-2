<?php
    //gère la déconenxion
    session_start();
    if(isset($_SESSION["login"])) {
        unset($_SESSION["login"]);
        header("Location: ../pages/index.php");
        exit();
    } else {
        print_r("déconnexion impossible");
    }
?>