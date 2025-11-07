// Selecciona todos los elementos que tengan la clase "only-one" y los guarda en una NodeList.
const checkboxes = document.querySelectorAll('.only-one');

// Para cada checkbox en la lista, se añade un listener para el evento "click".
checkboxes.forEach(chk => {
  chk.addEventListener('click', function() {
    
    // Verifica si el checkbox que se acaba de hacer clic está actualmente marcado.
    if (this.checked) {
      // Si ya estaba marcado, se recorre toda la lista de checkboxes y se desmarca cada uno,
      // lo que deja la opción sin seleccionar.
      checkboxes.forEach(c => c.checked = false);
    } else {
      // Si el checkbox no estaba marcado, se recorre la lista de checkboxes.
      // Para cada checkbox, si no es el que se acaba de hacer clic, se desmarca.
      checkboxes.forEach(c => {
        if (c !== this) {
          c.checked = false;
        }
      });
      // Finalmente, se marca el checkbox en el que se hizo clic.
      this.checked = true;
    }
  });
});
