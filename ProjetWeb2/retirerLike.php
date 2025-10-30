<?php
    //si l'utilisateur est connecter : enregistrer les like dans user.json sinon passer via une varriable de session "liked"
    session_start();
    if(isset($_SESSION["login"])) {
        $json = file_get_contents("user.json");
        $data = json_decode($json, true);
        $userdata;
        $userIndice;
        foreach($data as $indice => $user) {
            if($user["login"] === $_SESSION["login"]) {
                $userdata = $user;
                $userIndice = $indice;
                break;
            }
        }
        if(array_search($_GET["cocktail"], $userdata["liked"]) !== false) { 
            unset($userdata["liked"][array_search($_GET["cocktail"], $userdata["liked"])]);
            $fichier = "user.json";
            $tab = json_decode(file_get_contents($fichier), true);
            $tab[$indice] = $userdata;
            file_put_contents($fichier, json_encode($tab, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
        
    } else {
        unset($_SESSION["liked"][array_search($_GET["cocktail"], $_SESSION["liked"])]);
    }
?>