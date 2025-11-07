function mostrarCurpBeneficiario() {
    var select = document.getElementById('tipo-select');
    var curpDiv = document.getElementById('curp-beneficiario-group');
    var curpInput = document.getElementById('curp-beneficiario');

    if (select.value === 'Beneficiario') {
        curpDiv.style.display = '';
        curpInput.required = true;
    } else {
        curpDiv.style.display = 'none';
        curpInput.required = false;
        curpInput.value = ''; // Limpia el campo si se oculta
    }
}