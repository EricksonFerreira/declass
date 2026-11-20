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

/**
 * Tabelas ordenaveis: th.th-sort ordena as linhas do tbody pela
 * coluna. A chave vem do data-sort do td (fallback: texto da celula);
 * numeros comparam como numero. Clique alterna asc/desc e a seta do
 * cabecalho mostra a direcao ativa. Linhas de estado vazio (colspan)
 * ficam fora da ordenacao.
 */
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('th.th-sort').forEach(function (th) {
        th.addEventListener('click', function () {
            var table = th.closest('table');
            var tbody = table && table.tBodies[0];
            if (!tbody) return;

            var idx = Array.prototype.indexOf.call(th.parentNode.children, th);
            var asc = th.dataset.dir !== 'asc';

            var rows = Array.prototype.slice.call(tbody.rows).filter(function (r) {
                return r.cells.length > 1;
            });

            var key = function (row) {
                var td = row.cells[idx];
                if (!td) return '';
                var v = td.dataset.sort !== undefined ? td.dataset.sort : td.textContent;
                return String(v).trim();
            };

            rows.sort(function (a, b) {
                var va = key(a);
                var vb = key(b);
                var na = parseFloat(va.replace(/\./g, '').replace(',', '.'));
                var nb = parseFloat(vb.replace(/\./g, '').replace(',', '.'));
                var numeric = va !== '' && vb !== '' && !isNaN(na) && !isNaN(nb) && /^[-\d.,%]+$/.test(va) && /^[-\d.,%]+$/.test(vb);
                var cmp = numeric
                    ? na - nb
                    : va.localeCompare(vb, 'pt-BR', { sensitivity: 'base', numeric: true });
                return asc ? cmp : -cmp;
            });

            rows.forEach(function (r) { tbody.appendChild(r); });

            table.querySelectorAll('th.th-sort').forEach(function (h) {
                h.classList.remove('th-sort-asc', 'th-sort-desc');
                delete h.dataset.dir;
            });
            th.dataset.dir = asc ? 'asc' : 'desc';
            th.classList.add(asc ? 'th-sort-asc' : 'th-sort-desc');
        });
    });
});
