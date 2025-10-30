<?php
    //gère la conenxion, gestion des erreures via le get de index
    session_start();
    if(!isset($_POST["login"])) {
        header("Location: ".$_GET["page"].".php");
        exit();
    } else {
        $json = file_get_contents("user.json");
        $data = json_decode($json, true);
        foreach($data as $user) {
            if($user["login"] === $_POST["login"]) {
                if($user["mdp"] === $_POST["mdp"]) {
                    $_SESSION["login"] = $_POST["login"];
                    header("Location: ".$_GET["page"].".php");
                    exit();
                } else {
                    header("Location: ".$_GET["page"].".php?err=psw");
                    exit();
                }
            }
        }
        header("Location: ".$_GET["page"].".php?err=login");
        exit();
    }   
?>