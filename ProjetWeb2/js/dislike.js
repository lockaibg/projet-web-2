
window.addEventListener("DOMContentLoaded", () => { 
    document.querySelectorAll(".heartFull").forEach(heart => {
        heart.addEventListener("click", (event) => {
            const id = convert_tospace(event.currentTarget.id);
            event.currentTarget.src = "../Photos/heartLess.png";
            event.currentTarget.class = "heartLess";
            event.currentTarget.alt = "coeur vide";
            fetch(`../php/retirerLike.php?cocktail=${encodeURIComponent(id)}`, {
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