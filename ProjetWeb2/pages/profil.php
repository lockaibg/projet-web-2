<?php
    session_start();
    if(!isset($_SESSION["login"])) exit (-1);
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
            $json = file_get_contents("../user.json");
            $data = json_decode($json, true);
            foreach($data as $user) {
                if($user["login"] === $_POST["login"] && $_POST["login"] !== $_SESSION["login"]) {
                    $loginErreur = true;
                }
            }
            if(!$loginErreur) {
                $fichier = "../user.json";
                $changes = $_POST;
                $tab = json_decode(file_get_contents($fichier), true);
                $changes["liked"] = $tab["liked"];
                foreach($tab as $indice => $users) {
                    if($users["login"] === $_SESSION["login"]) 
                        $tab[$indice] = $changes;
                }
                file_put_contents($fichier, json_encode($tab, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $_SESSION["login"] = $_POST["login"];
                header("Location: index.php");
                exit();
            }
        }
    }
    $json = file_get_contents("../user.json");
    $data = json_decode($json, true);
    $donnees = array();
    foreach($data as $indice => $user) {
        if($user["login"] === $_SESSION["login"]) {
            foreach($user as $key => $value) {
                $donnees[$key] = $value;
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/> 
    <title>Profil</title>
    <link rel="stylesheet" href="../styles.css">    
</head>
<body>
    <header>
        <ul>
            <li><a href="index.php">Navigation</a></li>
            <li><a href="liked.php">Recettes</a><img src="../Photos/heartFull.png" alt="coeur rouge" height="20"></li>
            <li><!--recherche via syntaxe-->
                <form id="recherche" action="recherche.php" method="POST" onsubmit="return validerRecherche();">
                    <label>Recherche</label>
                    <input type="text" id="rechercheText" name="rechercheText" />
                    <input type="submit" value="Valider">
                </form>
            </li>
            <li><ul><?php
                if(isset($_SESSION["login"])) { 
                    ?><li><?php
                    echo $_SESSION["login"];
                    ?>
                    </li>
                    <li>
                        <form action="profil.php">
                            <input type="submit" value="Profil">
                        </form>
                    </li>
                    <li>
                        <form action="../php/deconnexion.php">
                            <input type="submit" value="Se déconnecter">
                        </form>
                    </li>
                    <?php
                } else {
                    ?>
                    <li>
                        <form method="POST" action="../php/connexion.php?page=index">
                            <label for="login">Login</label>
                            <input type="text" id="login" name="login" required><br><br>

                            <label for="mdp">Mot de passe :</label>
                            <input type="password" id="mdp" name="mdp" required><br><br>

                            <input type="submit" value="Connexion">
                        </form>
                    </li>
                    <?php
                        if(isset($_GET["err"])) {
                            if($_GET["err"] == "psw") {
                                ?>
                                    <li style="color: red;">mot de passe incorrecte</li>
                                <?php
                            } else if($_GET["err"] == "login") {
                                ?>
                                    <li style="color: red;">login introuvable</li>
                                <?php
                            }
                        }
                    ?>
                    <li>
                        <form action="register.php">
                            <input type="submit" value="S'inscrire">
                        </form>
                    </li>
                    <?php
                }
            ?></ul></li>
        </ul>
    </header>
    <?php
        if($loginErreur) {
            ?><p style="color: red;">login déjà utilisé</p> <?php
        }
    ?>
    <h1>Modifier le profil</h1>
    <br/>
    <form method="POST" action="#">

        
        
        <label for="login">Login</label>
        <input type="text" id="login" name="login" value="<?php echo $donnees["login"];?>" required>
        <?php if(isset($error["login"])) { ?><p style="color: red;">Login : Uniquement chiffres et lettres</p><?php } else { ?> <br /> <br /><?php } ?>
        
        <label for="mdp">Mot de passe :</label>
        <input type="password" id="mdp" name="mdp" value="<?php echo $donnees["mdp"];?>" required><br><br>

        Vous êtes :  
        <input type="radio" name="sexe" value="f" <?php if(isset($donnees["sexe"])) if($donnees["sexe"] == "f") echo "checked"; ?>/> une femme     
        <input type="radio" name="sexe" value="h" <?php if(isset($donnees["sexe"])) if($donnees["sexe"] == "h") echo "checked"; ?>/> un homme
        <br /> <br/>

        <label for="name">Nom</label>
        <input type="text" id="name" name="name" value="<?php if(isset($donnees["name"])) echo $donnees["name"];?>">        
        <?php if(isset($error["name"])) { ?><p style="color: red;">Nom : Uniquement Lettres {-} ou {'}</p><?php } else { ?> <br /> <br /><?php } ?>

        <label for="prenom">Prenom</label>
        <input type="text" id="prenom" name="prenom" value="<?php if(isset($donnees["prenom"])) echo $donnees["prenom"];?>">
        <?php if(isset($error["name"])) { ?><p style="color: red;">Prenom : Uniquement Lettres {-} ou {'}</p><?php } else { ?> <br /> <br /><?php } ?>

        <label for="naissance">Date de naissance : </label>
        <input type="date" id ="naissance" name="naissance" value="<?php if(isset($donnees["naissance"])) echo $donnees["naissance"];?>" /><br />

        <input type="submit" value="modifier les données">
    </form>
</body>
</html>