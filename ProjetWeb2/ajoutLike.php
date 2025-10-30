<?php
    //si l'utilisateur est connecter : enregistrer les like dans user.json sinon passer via une varriable de session "liked"
    session_start();
    if(isset($_SESSION["login"])) {
        $json = file_get_contents("user.json");
        $data = json_decode($json, true);
        $userdata;
        $userIndice;
        print_r($data);
        foreach($data as $indice => $user) {
            if($user["login"] === $_SESSION["login"]) {
                $userdata = $user;
                $userIndice = $indice;
                break;
            }
        }
        if(array_search($_GET["cocktail"], $userdata["liked"]) === false) { 
            array_push($userdata["liked"], $_GET["cocktail"]);
            $fichier = "user.json";
            $tab = json_decode(file_get_contents($fichier), true);
            $tab[$indice] = $userdata;
            file_put_contents($fichier, json_encode($tab, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    } else {
        $doable = true;
        foreach($_SESSION["liked"] as $cocktail) {
            if($cocktail === $_GET["cocktail"]) {
                $doable = false;
            }
        }
        if($doable) $_SESSION["liked"][] = $_GET["cocktail"];
    }
?>