function convert_to_underscore(text) {
    texte_modif = text.replaceAll(" ", "_");
    return text_modif;
}
function convert_tospace(text) {
    let text_modif = text.replaceAll("_", " ");

    if (text_modif.endsWith(" ")) {
        text_modif = text_modif.slice(0, -1);
    }

    return text_modif;
}
