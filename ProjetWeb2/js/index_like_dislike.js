window.addEventListener("DOMContentLoaded", () => { 
    //script pour ajouter un cocktail liké
    document.querySelectorAll(".heartLess").forEach(heart => {
        
        heart.addEventListener("click", (event) => {
            heart.stopPropagation();
            const id = event.currentTarget.id;
            event.currentTarget.src = "Photos/heartFull.png";
            event.currentTarget.class = "heartFull";
            event.currentTarget.alt = "coeur rouge";
            fetch(`ajoutLike.php?cocktail=${encodeURIComponent(id)}`, {
            method: "GET",
            cache: "no-store"
            })
            .then(response => response.text())
            .then(result => {
                console.log("Réponse du serveur :", result);
            })
            .catch(error => console.error("Erreur :", error));
        });
    });
    //script pour retirer le like
    document.querySelectorAll(".heartFull").forEach(heart => {
        heart.addEventListener("click", (event) => {
            heart.stopPropagation();
            const id = event.currentTarget.id;
            event.currentTarget.src = "Photos/heartLess.png";
            event.currentTarget.class = "heartLess";
            event.currentTarget.alt = "coeur vide";
            fetch(`retirerLike.php?cocktail=${encodeURIComponent(id)}`, {
            method: "GET",
            cache: "no-store"
            })
            .then(response => response.text())
            .then(result => {
                console.log("Réponse du serveur :", result);
            })
            .catch(error => console.error("Erreur :", error));
        });
    });
});