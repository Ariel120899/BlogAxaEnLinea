document.addEventListener("DOMContentLoaded", () => {
    try {
        const data = JSON.parse(localStorage.getItem("DataBase") || "{}");
        const nameEl = document.getElementById("nombredes");
        if (nameEl && data.nombre) {
            nameEl.textContent = data.nombre;
        }
    } catch (error) {
        console.warn("No se pudo leer el nombre para thanks:", error);
    }
});
