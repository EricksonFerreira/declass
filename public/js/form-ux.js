/**
 * form-ux.js — padronização global de formulários:
 * 1) Botões de submit ganham cor success + ícone (texto "Salvar" quando aplicável)
 * 2) Ao submeter, o botão exibe "Processando..." e fica desabilitado
 */
(function () {
    'use strict';

    var SAVE_VERBS = /salvar|cadastrar|gravar|atualizar|confirmar|incluir|adicionar|alterar|aplicar|finalizar|conclu|registrar|enviar/i;

    // Spinner inline (não depende de Bootstrap nem FontAwesome)
    var style = document.createElement('style');
    style.textContent = '.fx-spinner{display:inline-block;width:.85rem;height:.85rem;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;margin-right:.35rem;vertical-align:-.1rem;animation:fxSpin .7s linear infinite}@keyframes fxSpin{to{transform:rotate(360deg)}}'
        + '.fx-overlay{position:fixed;inset:0;z-index:99999;background:rgba(15,23,42,.45);display:flex;align-items:center;justify-content:center;backdrop-filter:blur(2px)}'
        + '.fx-overlay-box{background:#fff;border-radius:12px;padding:1.1rem 1.6rem;display:flex;align-items:center;gap:.7rem;box-shadow:0 10px 40px rgba(0,0,0,.25);font-weight:600;color:#1f2937;font-size:.95rem}'
        + '.fx-overlay-spin{width:1.4rem;height:1.4rem;border:3px solid #bfdbfe;border-top-color:#2563eb;border-radius:50%;animation:fxSpin .7s linear infinite}';
    document.head.appendChild(style);

    var overlayEl = null;

    function showOverlay() {
        if (overlayEl) { return; }
        overlayEl = document.createElement('div');
        overlayEl.className = 'fx-overlay';
        overlayEl.innerHTML = '<div class="fx-overlay-box"><span class="fx-overlay-spin" aria-hidden="true"></span><span>Processando, aguarde...</span></div>';
        document.body.appendChild(overlayEl);
    }

    function hideOverlay() {
        if (overlayEl) { overlayEl.remove(); overlayEl = null; }
    }

    function buttonText(btn) {
        return (btn.tagName === 'INPUT' ? btn.value : btn.textContent || '').trim();
    }

    function isDeleteButton(btn) {
        // Form com _method=DELETE é ação destrutiva; botão com classe danger também.
        var form = btn.closest('form');
        var methodInput = form ? form.querySelector('input[name="_method"]') : null;
        if (methodInput && /delete/i.test(methodInput.value || '')) {
            return true;
        }
        return /(^|\s)btn-(outline-)?danger(\s|$)/.test(btn.className || '');
    }

    function isSaveButton(btn) {
        if (isDeleteButton(btn)) {
            return false;
        }
        var text = buttonText(btn);
        // Botão só de ícone (sem texto visível) não deve virar "Salvar".
        if (text === '') {
            return !btn.querySelector('i, svg');
        }
        return SAVE_VERBS.test(text);
    }

    function normalizeSubmitButtons(root) {
        var buttons = (root || document).querySelectorAll('form button[type="submit"], form input[type="submit"]');

        buttons.forEach(function (btn) {
            if (btn.dataset.fxNormalized || btn.closest('[data-no-submit-style]')) {
                return;
            }
            btn.dataset.fxNormalized = '1';

            // Cor: submits primários/neutros viram success; danger/warning/light
            // (ações destrutivas/cancelar) ficam como estão.
            if (btn.classList.contains('btn')) {
                ['btn-primary', 'btn-outline-primary', 'btn-info', 'btn-secondary', 'btn-outline-secondary'].forEach(function (c) {
                    btn.classList.remove(c);
                });
                if (!/(^|\s)btn-(success|danger|warning|dark|light|outline-danger|outline-warning|outline-dark|outline-light)(\s|$)/.test(btn.className)) {
                    btn.classList.add('btn-success');
                }
            }

            if (btn.tagName === 'INPUT') {
                if (isSaveButton(btn)) {
                    btn.value = 'Salvar';
                }
                return;
            }

            // Ícones inline (SVG): funcionam mesmo sem FontAwesome/Bootstrap.
            var SAVE_ICON = '<svg width="13" height="13" viewBox="0 0 448 512" fill="currentColor" style="margin-right:.35rem;vertical-align:-.1rem" aria-hidden="true"><path d="M433.1 129.1l-83.9-83.9C342.3 38.32 327.4 32 311.6 32H64C28.65 32 0 60.65 0 96v320c0 35.35 28.65 64 64 64h320c35.35 0 64-28.65 64-64V136.4c0-15.74-6.328-30.67-17.16-41.54zM224 416c-35.34 0-64-28.66-64-64s28.66-64 64-64s64 28.66 64 64s-28.66 64-64 64zM96 228V96c0-6.627 5.373-12 12-12h164c6.627 0 12 5.373 12 12v132c0 6.627-5.373 12-12 12H108c-6.627 0-12-5.373-12-12z"/></svg>';
            var CHECK_ICON = '<svg width="12" height="12" viewBox="0 0 16 16" fill="currentColor" style="margin-right:.35rem;vertical-align:-.05rem" aria-hidden="true"><path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/></svg>';

            if (isSaveButton(btn)) {
                btn.innerHTML = SAVE_ICON + 'Salvar';
            } else if (!btn.querySelector('i, svg')) {
                btn.innerHTML = CHECK_ICON + btn.innerHTML;
            }
        });
    }

    function markProcessing(form, submitter) {
        var btn = submitter || form.querySelector('[type="submit"]:not([disabled])');
        if (!btn || btn.disabled) {
            return;
        }

        if (btn.tagName === 'INPUT') {
            btn.dataset.originalValue = btn.value;
            btn.value = 'Processando...';
        } else {
            btn.dataset.originalHtml = btn.innerHTML;
            btn.innerHTML = '<span class="fx-spinner" aria-hidden="true"></span> Processando...';
        }
        btn.disabled = true;
        showOverlay();

        // Submit via AJAX (preventDefault) não navega: restaura o botão após um tempo.
        setTimeout(function () {
            hideOverlay();
            if (!document.body.contains(btn)) {
                return;
            }
            if (btn.dataset.originalHtml !== undefined) {
                btn.innerHTML = btn.dataset.originalHtml;
            }
            if (btn.dataset.originalValue !== undefined) {
                btn.value = btn.dataset.originalValue;
            }
            btn.disabled = false;
        }, 10000);
    }

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!(form instanceof HTMLFormElement) || form.hasAttribute('data-no-processing')) {
            return;
        }
        setTimeout(function () {
            markProcessing(form, e.submitter);
        }, 0);
    }, true);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            normalizeSubmitButtons(document);
        });
    } else {
        normalizeSubmitButtons(document);
    }
})();
