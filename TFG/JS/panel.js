// SIDEBAR
document.addEventListener("DOMContentLoaded", () => {
    const menuBtn = document.getElementById("menu-btn");
    const sidebar = document.getElementById("sidebar");

    if (menuBtn && sidebar) {
        menuBtn.onclick = () => sidebar.classList.toggle("sidebar-open");

        document.addEventListener("click", (e) => {
            if (!sidebar.contains(e.target) && !menuBtn.contains(e.target)) {
                sidebar.classList.remove("sidebar-open");
            }
        });
    }

    // PERFIL
    const editUserBox = document.getElementById("editUserBox");
    if (editUserBox) {
        editUserBox.addEventListener("click", function(e) {
            const tag = e.target.tagName.toLowerCase();
            if (!["input", "button", "label"].includes(tag)) {
                window.location.href = "usuario.php";
            }
        });
    }
});
