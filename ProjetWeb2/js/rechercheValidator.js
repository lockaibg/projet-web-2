function recupererRecherche() {
    let texte = document.getElementById("rechercheText").value;
    fetch("recherche.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded"},
        body: "texte=" + encodeURIComponent(texte)
    })
    .then(res => res.json())
    .then(data => {
        console.log("Traitement de recherche :", data)
        data.forEach(mot => console.log(mot));
    })
    .catch(error => console.error("Erreur :", error));
}

function validerRecherche() {
    const texte = document.getElementById('rechercheText').value;
    const matches = texte.match(/"/g);
    const nombreDeQuotes = matches ? matches.length : 0;
    if (nombreDeQuotes % 2 !== 0) {
        alert("Problème de syntaxe dans votre requête : nombre impair de double-quotes");
        return false;
    }
    return true;
}