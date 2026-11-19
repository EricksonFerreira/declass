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
        if (!checkbox || !label) return;

        // iCheck exige o checkbox como primeiro filho: inputs hidden
        // (padrao "envia 0 quando desmarcado") saem para fora do
        // .form-check, mantendo a ordem de envio do formulario.
        el.querySelectorAll(':scope > input[type="hidden"]').forEach(function (hidden) {
            el.parentNode.insertBefore(hidden, el);
        });

        if (!el.classList.contains('icheck-primary')) {
            el.classList.add('icheck-primary');
        }
    });
});
