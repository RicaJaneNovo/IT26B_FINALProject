function toggleLoginPassword() {
    var pwd = document.getElementById("loginPassword");

    if (pwd.type === "password") {
        pwd.type = "text";
    } else {
        pwd.type = "password";
    }
}