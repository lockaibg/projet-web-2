<?php
    session_start();
    $loginErreur = false;
    if(isset($_POST["login"])) {
        $error = array();
        if (!preg_match("/^([A-Za-zÀ-ÿ\ ]+([\-\'][A-Za-zÀ-ÿ\ ]+)*)*$/", $_POST["name"])) {
            $error["name"] = true;
        } 
        if (!preg_match("/^([A-Za-zÀ-ÿ\ ]+([\-\'][A-Za-zÀ-ÿ\ ]+)*)*$/", $_POST["prenom"])) {
            $error["prenom"] = true;
        } 
        if (!preg_match("/^[A-Za-z0-9]+$/", $_POST["login"])){
            $error["login"] = true;
        } else if (preg_match("/^([A-Za-zÀ-ÿ\ ]+([\-\'][A-Za-zÀ-ÿ\ ]+)*)*$/", $_POST["name"]) && preg_match("/^([A-Za-zÀ-ÿ\ ]+([\-\'][A-Za-zÀ-ÿ\ ]+)*)*$/", $_POST["prenom"])){
            $json = file_get_contents("user.json");
            $data = json_decode($json, true);
            foreach($data as $user) {
                if($user["login"] === $_POST["login"]) {
                    $loginErreur = true;
                }
            }
            if(!$loginErreur) {
                $fichier = "user.json";
                $nouveau = $_POST;
                $nouveau['liked'] = array();
                $tab = json_decode(file_get_contents($fichier), true);
                $tab[] = $nouveau;
                file_put_contents($fichier, json_encode($tab, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $_SESSION["login"] = $_POST["login"];
                header("Location: index.php");
                exit();
            }
        }
    }
    
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/> 
    <title>Enregistrement</title>
    <link rel="stylesheet" href="../styles.css">    
</head>
<body>
    <?php
        if($loginErreur) {
            ?><p style="color: red;">login déjà utilisé</p> <?php
        }
    ?>
    <form method="POST" action="#">

        <legend>Créer un compte</legend>
        
        <label for="longin">Login</label>
        <input type="text" id="login" name="login" required>
        <?php if(isset($error["login"])) { ?><p style="color: red;">Login : Uniquement chiffres et lettres</p><?php } else { ?> <br /> <br /><?php } ?>
        
        <label for="mdp">Mot de passe :</label>
        <input type="password" id="mdp" name="mdp" required><br><br>

        Vous êtes :  
        <input type="radio" name="sexe" value="f"/> une femme     
        <input type="radio" name="sexe" value="h"/> un homme
        <br />

        <label for="Nom">Nom</label>
        <input type="text" id="name" name="name">        
        <?php if(isset($error["name"])) { ?><p style="color: red;">Nom : Uniquement Lettres {-} ou {'}</p><?php } else { ?> <br /> <br /><?php } ?>

        <label for="Prenom">Prenom</label>
        <input type="text" id="prenom" name="prenom">
        <?php if(isset($error["name"])) { ?><p style="color: red;">Prenom : Uniquement Lettres {-} ou {'}</p><?php } else { ?> <br /> <br /><?php } ?>

        <label for="Date">Date de naissance : </label>
        <input type="date" id ="naissance" name="naissance" /><br />

        <input type="submit" value="Envoyer">
    </form>
</body>
</html>