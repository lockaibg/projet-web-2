<?php
    session_start();
    $loginErreur = false;
    if(isset($_POST["login"])) {
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
            $tab = json_decode(file_get_contents($fichier), true);
            $tab[] = $nouveau;
            file_put_contents($fichier, json_encode($tab, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $_SESSION["login"] = $_POST["login"];
            header("Location: index.php");
            exit();
        }
    }
    
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/> 
    <title>enregistrement</title>
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
        <input type="text" id="login" name="login" required><br><br>

        <label for="mdp">Mot de passe :</label>
        <input type="password" id="mdp" name="mdp" required><br><br>

        Vous êtes :  
        <input type="radio" name="sexe" value="f"/> une femme     
        <input type="radio" name="sexe" value="h"/> un homme
        <br />

        <label for="Nom">Nom</label>
        <input type="text" id="name" name="name"><br><br>

        <label for="Prenom">Prenom</label>
        <input type="text" id="prenom" name="prenom"><br><br>

        <label for="Date">Date de naissance : </label>
        <input type="date" id ="naissance" name="naissance" /><br />

        <input type="submit" value="Envoyer">
    </form>
</body>
</html>