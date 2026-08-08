@props([
    'id' => 'sideDrawer',
    'title' => '',
    'formId' => 'drawerForm',
    'action' => '',
    'method' => 'POST'
])

{{-- Side Drawer Backdrop --}}
<div id="{{ $id }}Backdrop" class="side-drawer-backdrop"></div>

{{-- Side Drawer --}}
<div id="{{ $id }}" class="side-drawer">
    <div class="side-drawer-header d-flex justify-content-between align-items-center bg-primary text-white">
        <h5 id="{{ $id }}Title" class="mb-0 font-weight-bold">{{ $title }}</h5>
        <button type="button" class="close text-white" id="close{{ $id }}" aria-label="Close" style="outline: none;">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    
    <form id="{{ $formId }}" action="{{ $action }}" method="POST" class="flex-grow-1 d-flex flex-column h-100 mb-0">
        @csrf
        <input type="hidden" name="_method" id="{{ $formId }}Method" value="{{ $method }}">
        
        <div class="side-drawer-body">
            {{ $slot }}
        </div>

        <div class="side-drawer-footer text-right">
            <button type="button" class="btn btn-secondary mr-2" id="cancel{{ $id }}">Cancelar</button>
            <button type="submit" class="btn btn-primary">Salvar</button>
        </div>
    </form>
</div>

@once
@push('css')
    <style>
        .side-drawer {
            position: fixed;
            top: 0;
            right: -480px;
            width: 480px;
            height: 100vh;
            background: #ffffff;
            box-shadow: -5px 0 25px rgba(0,0,0,0.15);
            z-index: 1050;
            transition: right 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
        }
        .side-drawer.open {
            right: 0;
        }
        .side-drawer-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(15, 23, 42, 0.3);
            backdrop-filter: blur(2px);
            z-index: 1040;
            display: none;
        }
        .side-drawer-backdrop.show {
            display: block;
        }
        .side-drawer-header {
            padding: 1.25rem;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        .side-drawer-body {
            flex-grow: 1;
            padding: 1.5rem;
            overflow-y: auto;
        }
        .side-drawer-footer {
            padding: 1.25rem;
            border-top: 1px solid rgba(0,0,0,0.05);
            background: #f8fafc;
        }
    </style>
@endpush
@endonce

@push('js')
    <script>
        $(function() {
            // Setup events for this specific drawer
            $('#close{{ $id }}, #cancel{{ $id }}, #{{ $id }}Backdrop').on('click', function() {
                $('#{{ $id }}').removeClass('open');
                $('#{{ $id }}Backdrop').removeClass('show');
            });
        });
    </script>
@endpush
