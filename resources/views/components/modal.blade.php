@props([
    'id' => 'formModal',
    'title' => '',
    'formId' => 'modalForm',
    'action' => '',
    'method' => 'POST',
    'icon' => 'fas fa-pen',
    'enctype' => null,
])

{{-- Modal Backdrop --}}
<div id="{{ $id }}Backdrop" class="ux-modal-backdrop"></div>

{{-- Modal centralizado --}}
<div id="{{ $id }}" class="ux-modal" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}Title">
    <div class="ux-modal-card">
        <div class="ux-modal-header">
            <span class="ux-modal-icon" aria-hidden="true"><i class="{{ $icon }}"></i></span>
            <h5 id="{{ $id }}Title" class="ux-modal-title">{{ $title }}</h5>
            <button type="button" class="ux-modal-close" id="close{{ $id }}" aria-label="Fechar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <form id="{{ $formId }}" action="{{ $action }}" method="POST" @if ($enctype) enctype="{{ $enctype }}" @endif class="d-flex flex-column flex-grow-1 mb-0" style="min-height:0;">
            @csrf
            <input type="hidden" name="_method" id="{{ $formId }}Method" value="{{ $method }}">

            <div class="ux-modal-body">
                {{ $slot }}
            </div>

            <div class="ux-modal-footer">
                <button type="button" class="btn btn-light" id="cancel{{ $id }}">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>

@once
@push('css')
    <style>
        .ux-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            z-index: 1040;
            opacity: 0;
            visibility: hidden;
            transition: opacity .22s ease, visibility .22s ease;
        }
        .ux-modal-backdrop.show {
            opacity: 1;
            visibility: visible;
        }

        .ux-modal {
            position: fixed;
            top: 50%;
            left: 50%;
            width: min(560px, calc(100vw - 32px));
            max-height: 90vh;
            max-height: 90dvh;
            z-index: 1050;
            display: flex;
            opacity: 0;
            visibility: hidden;
            transform: translate(-50%, -46%) scale(.96);
            transition: transform .28s cubic-bezier(0.16, 1, 0.3, 1), opacity .2s ease, visibility .2s ease;
        }
        .ux-modal.open {
            opacity: 1;
            visibility: visible;
            transform: translate(-50%, -50%) scale(1);
        }

        .ux-modal-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 24px 64px rgba(15, 23, 42, .28);
            display: flex;
            flex-direction: column;
            width: 100%;
            max-height: 90vh;
            max-height: 90dvh;
            overflow: hidden;
        }

        .ux-modal-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 20px;
            border-bottom: 1px solid #eef2f7;
            flex-shrink: 0;
        }
        .ux-modal-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: var(--empresa-cor-primaria, #0f172a);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
        }
        .ux-modal-title {
            margin: 0;
            font-size: 1.02rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.3;
        }
        .ux-modal-close {
            margin-left: auto;
            width: 32px;
            height: 32px;
            border: none;
            border-radius: 8px;
            background: #f1f5f9;
            color: #64748b;
            font-size: 1.3rem;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background .15s ease, color .15s ease;
            flex-shrink: 0;
        }
        .ux-modal-close:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        .ux-modal-body {
            padding: 20px;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            flex: 1 1 auto;
            min-height: 0;
        }
        .ux-modal .form-group > label {
            font-weight: 600;
            font-size: .85rem;
            color: #334155;
        }

        .ux-modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            padding: 14px 20px;
            border-top: 1px solid #eef2f7;
            background: #f8fafc;
            flex-shrink: 0;
        }
        .ux-modal-footer .btn {
            min-width: 96px;
            font-weight: 600;
        }

        body.ux-modal-locked { overflow: hidden; }

        @media (max-width: 575.98px) {
            .ux-modal {
                width: calc(100vw - 24px);
                max-height: 92vh;
                max-height: 92dvh;
            }
            .ux-modal-body { padding: 16px; }
            /* Inputs >= 16px evitam auto-zoom no iOS */
            .ux-modal .form-control,
            .ux-modal .custom-select,
            .ux-modal .select2-container .select2-selection {
                font-size: 16px;
                min-height: 44px;
            }
            .ux-modal-footer .btn {
                flex: 1;
                min-height: 46px;
            }
        }
    </style>
@endpush
@endonce

@push('js')
    <script>
        $(function() {
            var modal = document.getElementById('{{ $id }}');
            var backdrop = document.getElementById('{{ $id }}Backdrop');

            function closeModal() {
                modal.classList.remove('open');
                backdrop.classList.remove('show');
            }

            $('#close{{ $id }}, #cancel{{ $id }}, #{{ $id }}Backdrop').on('click', closeModal);

            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && modal.classList.contains('open')) {
                    closeModal();
                }
            });

            new MutationObserver(function() {
                document.body.classList.toggle('ux-modal-locked', modal.classList.contains('open'));
            }).observe(modal, { attributes: true, attributeFilter: ['class'] });
        });
    </script>
@endpush
