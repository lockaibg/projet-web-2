<?php
    include "Donnees.inc.php";
    session_start();
    if(isset($_SESSION["filAriane"])) { 
        $fil = $_SESSION["filAriane"];
        $filArray = explode(".", $fil);
        unset($filArray[key(array_slice($filArray, -1, 1, true))]);
    } else {
        $fil = "Aliment.";
        $filArray = array ( 0 => "Aliment");
    }
    
    if(isset($_GET["selection"])) $select = $_GET["selection"]; //initialisation de la clef de recherche
    else $select = "Aliment";

    //permet de gérer le fait que l'utilisateur revienne en arrière
    if(isset($filArray)){
        foreach($filArray as $index => $value){
            
            if($value === $select) { 
                if(key(array_slice($filArray, -1, 1, true)) != $index){
                    $filArray = array_slice($filArray, 0, $index+1);
                    $fil = "";
                    break;
                }
            }
        }
        if($fil === "") {
            foreach($filArray as $index => $value) {
                $fil = $fil.$value.".";
            }
        }
    }
    
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/> 
    <title><?php echo $select?></title>
    <link rel="stylesheet" href="../styles.css">
</head>
<script>
    //script utilisé pour update la varriable de session visant a garder en mémoire le fil d'ariane
    const beforeAriane = "<?php echo addslashes($fil); ?>";
    window.onload = function () {
        document.querySelectorAll(".elemClickable").forEach(lien => {
            lien.addEventListener("click", async (event) => {
                event.preventDefault();
                const id = event.currentTarget.id;
                await fetch("majSession.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "cle=filAriane&valeur="+encodeURIComponent(beforeAriane + id + "."),
                    keepalive: true
                });
                console.log(id);
                window.location.href = lien.href;
            });
        });
    }
</script>
<body>
    <?php 
        
        if ($fil != "") {
        //afficher le fil d'ariane si il existe
    ?>
        <div id="filAriane"><?php echo $fil?></div>
    <?php
        }
    ?>
    <div id="selection" style="border: solid;">
        <ul>
        <?php
            foreach($Hierarchie as $element => $fils) { 
                if($element === $select){ //recherche de l'element
                    foreach($fils as $cats => $liste){
                        if($cats === "sous-categorie") {
                            foreach($liste as $elem) { //affichage de tout les fils de l'element trouvé
                            ?> 
                                <li><a id ="<?php echo $elem?>" href="index.php?selection=<?php echo $elem?>"class="elemClickable"><?php echo $elem?></a></li>                                                        
                            <?php
                            }
                        }
                    }
                }
            }
        ?>
        </ul>

    </div>
</body>
