function postToPage(url, data = {}) {
    const form = document.createElement("form");
    form.method = "POST";
    form.action = url;

    // ajouter les champs
    for (const key in data) {
        const input = document.createElement("input");
        input.type = "hidden";
        input.name = key;
        input.value = data[key];
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
}

window.addEventListener("DOMContentLoaded", () => { 
    document.querySelectorAll(".cocktail").forEach(cocktail => {
        cocktail.addEventListener("click", (e) => {
            postToPage("fullRecette.php", {
                elem: e.currentTarget.id
            });
        });
    });
});