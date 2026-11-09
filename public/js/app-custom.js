/**
 * Customizacoes globais do painel (carregado em todas as telas admin).
 *
 * iCheck Bootstrap: aplica a classe icheck-primary em todo container
 * .form-check que tenha checkbox + label (markup padrao Bootstrap).
 * Assim todos os checkboxes do sistema ganham o visual novo sem
 * precisar alterar cada formulario. Checkboxes sem label sao
 * ignorados (o icheck esconderia o input nativo).
 */
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.form-check').forEach(function (el) {
        var checkbox = el.querySelector(':scope > input[type="checkbox"], :scope > .form-check-input[type="checkbox"]');
        var label = el.querySelector(':scope > label');
        if (checkbox && label && !el.classList.contains('icheck-primary')) {
            el.classList.add('icheck-primary');
        }
    });
});
