{{-- Modal reutilizavel de selecao de entidade com busca + filtros.
     Uso: @include('partials.entity-picker') UMA vez por pagina, depois
     EntityPicker.registrar('chave', {
         titulo: 'Buscar aluno',
         dados: [...],                       // itens: {id, rotulo, detalhe?, busca?}
         filtros: [{campo:'escola_id', campoRotulo:'escola', label:'Escola'}],
         onSelect: function(item){ ... }
     });
     EntityPicker.abrir('chave') abre o modal. --}}
<div class="modal fade" id="modalEntityPicker" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content ep-content">
            <div class="modal-header ep-header">
                <h5 class="modal-title" id="ep-titulo">Buscar</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span>&times;</span></button>
            </div>

            <div class="ep-filtros">
                <div class="row align-items-end mb-0">
                    <div class="col-md-5">
                        <label class="ep-lbl" for="ep-busca">Buscar</label>
                        <input type="text" id="ep-busca" class="form-control form-control-sm" placeholder="Digite o nome..." autocomplete="off">
                    </div>
                    <div class="col-md-3" data-ep-filtro="0">
                        <label class="ep-lbl" id="ep-lbl-0" for="ep-sel-0">Filtro</label>
                        <select id="ep-sel-0" class="form-control form-control-sm"></select>
                    </div>
                    <div class="col-md-3" data-ep-filtro="1">
                        <label class="ep-lbl" id="ep-lbl-1" for="ep-sel-1">Filtro</label>
                        <select id="ep-sel-1" class="form-control form-control-sm"></select>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-sm btn-primary btn-block" id="ep-btn-buscar" title="Buscar">
                            <i class="fa fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="ep-results" id="ep-scroll">
                <table class="ep-table">
                    <thead>
                        <tr>
                            <th class="ep-th-sort ativo" data-sort="rotulo">Nome <i class="ep-sort-ic fa fa-sort-up"></i></th>
                            <th class="ep-th-sort" data-sort="detalhe">Detalhes <i class="ep-sort-ic fa fa-sort"></i></th>
                            <th class="ep-th-sort" data-sort="extra" id="ep-th-extra" style="display:none;width:70px;"><span id="ep-lbl-extra">Extra</span> <i class="ep-sort-ic fa fa-sort"></i></th>
                            <th style="width:96px;"></th>
                        </tr>
                    </thead>
                    <tbody id="ep-resultados"></tbody>
                </table>
                <div class="ep-loading" id="ep-loading"><i class="fa fa-circle-notch fa-spin"></i> Carregando...</div>
                <div class="ep-vazio" id="ep-vazio" style="display:none;"><i class="fa fa-search mb-1"></i><br>Nenhum resultado — ajuste os filtros.</div>
            </div>

            <div class="modal-footer ep-footer">
                <span id="ep-count" class="mr-auto small text-muted"></span>
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<style>
    .ep-content { border-radius:12px; overflow:hidden; }
    .ep-header { padding:.55rem 1rem; background:linear-gradient(135deg,#1e3a8a,#2563eb); color:#fff; }
    .ep-header .modal-title { font-size:.95rem; }
    .ep-header .close { color:#fff; opacity:.85; text-shadow:none; }
    .ep-filtros { padding:.6rem .9rem; background:#f8fafc; border-bottom:1px solid #e5e7eb; }
    .ep-lbl { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#6b7280; margin-bottom:2px; display:block; }
    .ep-results { height:46vh; overflow-y:auto; }
    .ep-table { width:100%; border-collapse:collapse; }
    .ep-table thead th { position:sticky; top:0; z-index:2; background:#fff; border-bottom:2px solid #e5e7eb; font-size:.7rem; text-transform:uppercase; letter-spacing:.05em; color:#6b7280; padding:.45rem .7rem; }
    .ep-th-sort { cursor:pointer; user-select:none; }
    .ep-th-sort:hover { color:#2563eb; }
    .ep-sort-ic { opacity:.4; margin-left:2px; }
    .ep-th-sort.ativo .ep-sort-ic { opacity:1; color:#2563eb; }
    .ep-row { border-bottom:1px solid #f0f3f8; cursor:pointer; transition:background .1s; }
    .ep-row:hover { background:#f0f6ff; }
    .ep-row.selecionada { background:#eff6ff; }
    .ep-avatar { width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,#dbeafe,#bfdbfe); color:#1d4ed8; display:inline-flex; align-items:center; justify-content:center; font-size:.72rem; font-weight:700; flex-shrink:0; }
    .ep-nome { font-weight:600; font-size:.85rem; color:#1f2937; }
    .ep-detalhe { font-size:.75rem; color:#6b7280; }
    .ep-loading { text-align:center; padding:.6rem; color:#2563eb; font-size:.8rem; }
    .ep-vazio { text-align:center; padding:2rem; color:#9ca3af; }
    .ep-footer { padding:.45rem .9rem; background:#f8fafc; }
</style>

<script>
window.EntityPicker = (function () {
    const configs = {};
    const CHUNK = 30;
    let atual = null;
    let filtrados = [];
    let mostrados = 0;
    let sortKey = 'rotulo';
    let sortAsc = true;

    function norm(s) {
        return (s || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
    }
    function esc(s) {
        const d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }
    function opcao(v, l) {
        const o = document.createElement('option');
        o.value = v; o.textContent = l;
        return o;
    }
    function iniciais(rotulo) {
        const parts = String(rotulo || '?').trim().split(/\s+/);
        return ((parts[0] || '?').charAt(0) + (parts.length > 1 ? parts[parts.length - 1].charAt(0) : '')).toUpperCase();
    }

    function prepararFiltros(cfg) {
        [0, 1].forEach(function (i) {
            const f = cfg.filtros[i];
            const wrap = document.querySelector('[data-ep-filtro="' + i + '"]');
            const sel = document.getElementById('ep-sel-' + i);
            if (!f) { wrap.style.display = 'none'; sel.innerHTML = ''; return; }
            wrap.style.display = '';
            document.getElementById('ep-lbl-' + i).textContent = f.label;
            const opts = new Map();
            cfg.dados.forEach(function (d) {
                const v = f.porRotulo ? d[f.campoRotulo] : d[f.campo];
                if (v != null) { opts.set(String(v), String(d[f.campoRotulo] ?? d[f.campo])); }
            });
            sel.innerHTML = '';
            sel.appendChild(opcao('', 'Todos'));
            Array.from(opts.entries()).sort(function (a, b) { return a[1].localeCompare(b[1], 'pt-BR'); })
                .forEach(function (p) { sel.appendChild(opcao(p[0], p[1])); });
            sel.value = f.padrao != null ? String(f.padrao) : '';
        });
    }

    function filtrar() {
        const cfg = configs[atual];
        const busca = norm(document.getElementById('ep-busca').value);
        const vals = [0, 1].map(function (i) { return document.getElementById('ep-sel-' + i).value; });
        filtrados = cfg.dados.filter(function (d) {
            const txt = norm(d.busca != null ? d.busca : d.rotulo + ' ' + (d.detalhe || ''));
            if (busca && txt.indexOf(busca) === -1) { return false; }
            return cfg.filtros.every(function (f, i) {
                return !f || !vals[i] || String(f.porRotulo ? d[f.campoRotulo] : d[f.campo]) === vals[i];
            });
        });
        filtrados.sort(function (a, b) {
            const campo = sortKey === 'extra' && configs[atual].coluna3 ? configs[atual].coluna3.campo : sortKey;
            const va = a[campo] ?? '';
            const vb = b[campo] ?? '';
            const cmp = (typeof va === 'number' || typeof vb === 'number')
                ? va - vb
                : String(va).localeCompare(String(vb), 'pt-BR');
            return sortAsc ? cmp : -cmp;
        });
        mostrados = 0;
        document.getElementById('ep-resultados').innerHTML = '';
        document.getElementById('ep-vazio').style.display = filtrados.length ? 'none' : '';
        document.getElementById('ep-loading').style.display = filtrados.length ? '' : 'none';
        carregarMais();
        document.getElementById('ep-scroll').scrollTop = 0;
    }

    function carregarMais() {
        if (mostrados >= filtrados.length) {
            document.getElementById('ep-loading').style.display = 'none';
            atualizarCount();
            return;
        }
        const fim = Math.min(mostrados + CHUNK, filtrados.length);
        const frag = document.createDocumentFragment();
        for (let i = mostrados; i < fim; i++) {
            frag.appendChild(linha(filtrados[i]));
        }
        document.getElementById('ep-resultados').appendChild(frag);
        mostrados = fim;
        document.getElementById('ep-loading').style.display = mostrados < filtrados.length ? '' : 'none';
        atualizarCount();
    }

    function linha(d) {
        const tr = document.createElement('tr');
        tr.className = 'ep-row';
        tr.innerHTML =
            '<td><div style="display:flex;align-items:center;gap:.55rem;">' +
            '<span class="ep-avatar">' + esc(iniciais(d.rotulo)) + '</span>' +
            '<span class="ep-nome">' + esc(d.rotulo) + '</span></div></td>' +
            '<td><span class="ep-detalhe">' + esc(d.detalhe || '') + '</span>' + (d.extraHtml || '') + '</td>' +
            (configs[atual].coluna3
                ? '<td><span class="badge badge-info">' + esc(d[configs[atual].coluna3.campo] ?? 0) + '</span></td>'
                : '') +
            '<td style="text-align:right;"><button type="button" class="btn btn-xs btn-primary">Selecionar</button></td>';
        tr.querySelector('button').addEventListener('click', function () { escolher(d); });
        tr.addEventListener('dblclick', function () { escolher(d); });
        return tr;
    }

    function escolher(d) {
        configs[atual].onSelect(d);
        $('#modalEntityPicker').modal('hide');
    }

    function atualizarCount() {
        document.getElementById('ep-count').textContent =
            filtrados.length + (filtrados.length === 1 ? ' resultado' : ' resultados') +
            (mostrados < filtrados.length ? ' — mostrando ' + mostrados : '');
    }

    function aplicarSort(key) {
        if (sortKey === key) { sortAsc = !sortAsc; } else { sortKey = key; sortAsc = true; }
        document.querySelectorAll('.ep-th-sort').forEach(function (th) {
            const ativo = th.dataset.sort === sortKey;
            th.classList.toggle('ativo', ativo);
            th.querySelector('.ep-sort-ic').className = 'ep-sort-ic fa ' +
                (ativo ? (sortAsc ? 'fa-sort-up' : 'fa-sort-down') : 'fa-sort');
        });
        filtrar();
    }

    function abrir(key) {
        if (!configs[key]) { return; }
        atual = key;
        document.getElementById('ep-titulo').textContent = configs[key].titulo;
        document.getElementById('ep-busca').value = '';
        const thExtra = document.getElementById('ep-th-extra');
        if (configs[key].coluna3) {
            document.getElementById('ep-lbl-extra').textContent = configs[key].coluna3.label;
            thExtra.style.display = '';
        } else {
            thExtra.style.display = 'none';
        }
        prepararFiltros(configs[key]);
        filtrar();
        $('#modalEntityPicker').modal('show');
        $('#modalEntityPicker').one('shown.bs.modal', function () {
            document.getElementById('ep-busca').focus();
        });
    }

    function registrar(key, cfg) {
        cfg.filtros = cfg.filtros || [];
        configs[key] = cfg;
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('ep-btn-buscar').addEventListener('click', filtrar);
        document.getElementById('ep-busca').addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); filtrar(); }
        });
        [0, 1].forEach(function (i) {
            document.getElementById('ep-sel-' + i).addEventListener('change', filtrar);
        });
        document.querySelectorAll('.ep-th-sort').forEach(function (th) {
            th.addEventListener('click', function () { aplicarSort(th.dataset.sort); });
        });
        // Scroll infinito: carrega o proximo bloco perto do fim.
        document.getElementById('ep-scroll').addEventListener('scroll', function () {
            if (this.scrollTop + this.clientHeight >= this.scrollHeight - 60) {
                carregarMais();
            }
        });
    });

    return { registrar: registrar, abrir: abrir };
})();
</script>
