// public/js/alertConfirmPequena.js
function alertConfirmPequena(title, text, icon) {
    Swal.fire({
        title: title,
        text: text,
        icon: icon,
        showConfirmButton: false,
        position: 'top-end',
        iconHtml: `<span style="font-size: 12px;">${icon}</span>`, // Ícone pequeno
        customClass: {
            icon: 'small-icon' // Classe personalizada para o ícone
        }
    })
}
