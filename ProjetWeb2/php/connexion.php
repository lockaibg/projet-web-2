<?php
    //gère la conenxion, gestion des erreures via le get de index
    session_start();
    if(!isset($_POST["login"])) {
        header("Location: ../pages/".$_GET["page"].".php");
        exit();
    } else {
        if(isset($_SESSION["liked"])) {//si y'a eu des like alors que l'utilisateur est déconnecter transporter ces likes
            
            $json = file_get_contents("../user.json");
            $data = json_decode($json, true);
            $userdata;
            $userIndice;
            print_r($data);
            foreach($data as $indice => $user) {
                if($user["login"] === $_POST["login"]) {
                    $userdata = $user;
                    $userIndice = $indice;
                    break;
                }
            }
            foreach($_SESSION["liked"] as $alreadyLiked) {
                if(array_search($alreadyLiked, $userdata["liked"]) === false) { 
                    array_push($userdata["liked"], $alreadyLiked);
                    $fichier = "../user.json";
                    $tab = json_decode(file_get_contents($fichier), true);
                    $tab[$indice] = $userdata;
                    file_put_contents($fichier, json_encode($tab, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                }
            }
            $_SESSION["liked"] = [];
        }
        $json = file_get_contents("../user.json");
        $data = json_decode($json, true);
        foreach($data as $user) {
            if($user["login"] === $_POST["login"]) {
                if($user["mdp"] === $_POST["mdp"]) {
                    $_SESSION["login"] = $_POST["login"];
                    header("Location: ../pages/".$_GET["page"].".php");
                    exit();
                } else {
                    header("Location: ../pages/".$_GET["page"].".php?err=psw");
                    exit();
                }
            }
        }
        header("Location: ../pages/".$_GET["page"].".php?err=login");
        exit();
    }   
?>