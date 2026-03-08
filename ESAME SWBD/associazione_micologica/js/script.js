function validaRegistrazione(event) {
    let password = document.getElementById("password").value;
    let conferma = document.getElementById("conferma_password").value;
    
    let boxErrore = document.getElementById("errore_js");

    boxErrore.innerHTML = "";

    if (password !== conferma) {
        boxErrore.innerHTML = "Attenzione: Le password non coincidono!";
        event.preventDefault(); 
        return false;
    }
    
    return true;
}