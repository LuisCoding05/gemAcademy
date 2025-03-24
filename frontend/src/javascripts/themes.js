// Obtener elementos
const botonModoOscuro = document.getElementById('darkModeToggle');
const aside = document.getElementById('aside');
const acordeon = document.getElementById('accordionExample');
const dropDowns = document.getElementsByClassName("dropdown-menu");
const sol = document.getElementById("sol");
const luna = document.getElementById("luna");
// Función para cambiar al modo oscuro
if (botonModoOscuro) {
    botonModoOscuro.addEventListener('click', function () {
        // Cambiar el estado del modo oscuro en el cuerpo
        const esModoOscuro = document.body.classList.toggle('dark-mode');
        // Cambiar el tema del aside
        aside.classList.toggle('bg-dark', esModoOscuro);
        aside.classList.toggle('text-white', esModoOscuro);
        
    
        // Cambiar el color e icono del botón
        if (esModoOscuro) {
            botonModoOscuro.classList.add('btn-outline-dark');
            botonModoOscuro.classList.remove('btn-outline-light');
            luna.style.display = "none";
            sol.style.display = "inline";
            //Cambiar el tema de color de los dropdown
            Array.from(dropDowns).forEach(dropDown => {
                dropDown.setAttribute("data-bs-theme", "dark");
            });
        } else {
            botonModoOscuro.classList.add('btn-outline-light');
            botonModoOscuro.classList.remove('btn-outline-dark');
            luna.style.display = "inline";
            sol.style.display = "none";
            //Cambiar el tema de color de los dropdown
            Array.from(dropDowns).forEach(dropDown => {
                dropDown.setAttribute("data-bs-theme", "light");
            });
        }
    
        // Agregar animación al botón
        botonModoOscuro.classList.add('pulse');
        setTimeout(() => botonModoOscuro.classList.remove('pulse'), 1000);
    });
}

     