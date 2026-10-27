//* choices category input
var productCategoryInput = new Choices('#choices-category-input', {
    searchEnabled: false,
    shouldSort: false,
});

var forms = document.querySelectorAll('.needs-validation');
var isSubmitting = false;

Array.prototype.slice.call(forms).forEach(function (form) {
    // Remover listeners previos para evitar duplicados
    form.removeEventListener('submit', handleFormSubmit);
    form.addEventListener('submit', handleFormSubmit, false);
});

function handleFormSubmit(event) {
    var form = event.currentTarget;

    // Prevenir doble envío
    if (isSubmitting) {
        event.preventDefault();
        return false;
    }

    // Validar el formulario
    if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
        form.classList.add('was-validated');
        return false;
    }

    var productCategoryValue = productCategoryInput.getValue(true);
    var formAction = document.getElementById("formAction").value;

    // Si es edición y la categoría está vacía
    if (formAction === "edit" && (!productCategoryValue || productCategoryValue === "")) {
        event.preventDefault();
        $('.error-msg').show();
        $('#choices-category-input').addClass('is-invalid');
        return false;
    }

    // Solo para edición, permitir el envío
    if (formAction === "edit" && productCategoryValue !== "") {
        isSubmitting = true;

        // Mostrar loading en el botón
        var submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Procesando...';
        }

        // El formulario se enviará normalmente
        return true;
    }

    // Si no es edición o no cumple condiciones
    event.preventDefault();
    console.log('Form Action Not Found or Invalid.');
    return false;
}

// Resetear estado en caso de error
$(document).ajaxComplete(function() {
    if ($('.alert-danger').length > 0 || $('.invalid-feedback').length > 0) {
        isSubmitting = false;
        var submitBtn = document.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Modificar';
        }
    }
});
