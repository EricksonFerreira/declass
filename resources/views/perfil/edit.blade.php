@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    @php
        $roleLabel = $user->is_super_admin
            ? 'Superusuário'
            : (optional($user->tipoUsuario)->name ?? 'Sem perfil');

        $infos = [
            ['icone' => 'fas fa-building', 'cor' => '#2563eb', 'label' => 'Empresa', 'valor' => optional($user->empresa)->nome ?? 'Não informada'],
            ['icone' => 'fas fa-user-shield', 'cor' => '#7c3aed', 'label' => 'Perfil', 'valor' => $roleLabel],
            ['icone' => 'fas fa-phone-alt', 'cor' => '#0891b2', 'label' => 'Telefone', 'valor' => $user->telefone ?? 'Não informado'],
            ['icone' => 'fas fa-mobile-alt', 'cor' => '#0d9488', 'label' => 'Celular', 'valor' => $user->celular ?? 'Não informado'],
            ['icone' => 'fas fa-map-marker-alt', 'cor' => '#d97706', 'label' => 'Cidade', 'valor' => $user->cidade ?? 'Não informada'],
            ['icone' => 'fas fa-calendar-check', 'cor' => '#db2777', 'label' => 'Cadastro', 'valor' => $user->created_at ? $user->created_at->format('d/m/Y') : 'Não informado'],
        ];
    @endphp

    {{-- Header estilo app --}}
    <div class="pf-header">
        <form action="{{ route('profile.foto.update') }}" method="POST" enctype="multipart/form-data" id="form-foto-perfil">
            @csrf
            @method('PUT')
            <div class="pf-avatar">
                <img src="{{ $user->foto_perfil_url }}" alt="Foto de perfil de {{ $user->nome }}" id="profile-avatar-preview">
                <label for="foto" class="pf-avatar-edit" title="Alterar foto de perfil"><i class="fas fa-camera"></i></label>
            </div>
            <input type="file" name="foto" id="foto" accept="image/png,image/jpeg,image/webp" class="d-none">
            @error('foto')
                <span class="text-danger text-xs d-block text-center mt-1">{{ $message }}</span>
            @enderror
            <button type="submit" class="btn btn-light btn-sm font-weight-bold d-none mt-2" id="btn-salvar-foto">
                <i class="fas fa-save mr-1"></i> Salvar foto
            </button>
        </form>
        <div class="pf-identidade">
            <b>{{ $user->nome }}</b>
            <small>{{ $user->email }}</small>
            <div class="pf-meta">
                <span class="pf-tag"><i class="fas fa-user-shield mr-1"></i>{{ $roleLabel }}</span>
                <span class="pf-tag">ID #{{ $user->id }}</span>
                <span class="pf-tag"><i class="fas fa-pen mr-1"></i>{{ $apenasFoto ? 'Apenas foto editável' : 'Dados de contato editáveis' }}</span>
            </div>
        </div>
    </div>

    {{-- Informacoes: lista estilo app --}}
    <div class="pf-sec"><i class="fa fa-id-card mr-1"></i> Minhas informações</div>
    <div class="pf-lista">
        @foreach($infos as $info)
            <div class="pf-item" style="--app:{{ $info['cor'] }}">
                <span class="pf-item-icone"><i class="{{ $info['icone'] }}"></i></span>
                <span class="pf-item-info">
                    <small>{{ $info['label'] }}</small>
                    <b>{{ $info['valor'] }}</b>
                </span>
            </div>
        @endforeach
    </div>

    @unless ($apenasFoto)
        {{-- Dados de contato editaveis --}}
        <div class="pf-sec"><i class="fa fa-pen mr-1"></i> Dados de contato</div>
        <div class="pf-card">
            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-4 form-group mb-md-0">
                        <label class="text-xs text-muted font-weight-bold">Telefone</label>
                        <input type="text" name="telefone" class="form-control @error('telefone') is-invalid @enderror"
                            value="{{ old('telefone', $user->telefone) }}" placeholder="(00) 0000-0000">
                        @error('telefone') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 form-group mb-md-0">
                        <label class="text-xs text-muted font-weight-bold">Celular</label>
                        <input type="text" name="celular" class="form-control @error('celular') is-invalid @enderror"
                            value="{{ old('celular', $user->celular) }}" placeholder="(00) 00000-0000">
                        @error('celular') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 form-group mb-0">
                        <label class="text-xs text-muted font-weight-bold">Cidade</label>
                        <input type="text" name="cidade" class="form-control @error('cidade') is-invalid @enderror"
                            value="{{ old('cidade', $user->cidade) }}" placeholder="Sua cidade">
                        @error('cidade') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
                <button type="submit" class="btn btn-primary font-weight-bold mt-3">
                    <i class="fas fa-save mr-1"></i> Salvar
                </button>
            </form>
        </div>
    @endunless

<style>
    .pf-header { display:flex; align-items:center; gap:1rem; padding:1.1rem 1.3rem; border-radius:18px; margin-bottom:1rem; color:#fff; background:#007bff; box-shadow:0 6px 20px rgba(0,123,255,.28); flex-wrap:wrap; }
    .pf-avatar { position:relative; width:84px; height:84px; flex-shrink:0; }
    .pf-avatar img { width:100%; height:100%; border-radius:50%; object-fit:cover; border:3px solid rgba(255,255,255,.45); display:block; background:#dbeafe; }
    .pf-avatar-edit { position:absolute; right:-2px; bottom:-2px; width:30px; height:30px; border-radius:50%; background:#fff; color:#007bff; border:2px solid #fff; box-shadow:0 3px 8px rgba(15,23,42,.25); display:flex; align-items:center; justify-content:center; cursor:pointer; margin:0; font-size:.75rem; }
    .pf-avatar-edit:hover { background:#f1f5f9; }
    .pf-identidade { display:flex; flex-direction:column; min-width:0; }
    .pf-identidade > b { font-size:1.2rem; font-weight:800; word-break:break-word; }
    .pf-identidade > small { font-size:.78rem; opacity:.85; word-break:break-word; }
    .pf-meta { display:flex; flex-wrap:wrap; gap:.4rem; margin-top:.55rem; }
    .pf-tag { display:inline-flex; align-items:center; padding:.28rem .65rem; border-radius:999px; background:rgba(255,255,255,.18); color:#fff; font-size:.68rem; font-weight:700; }
    .pf-sec { display:flex; align-items:center; gap:.4rem; font-size:.78rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#2563eb; margin:1.1rem 0 .6rem; }
    .pf-lista { background:#fff; border:1px solid #eef1f6; border-radius:14px; overflow:hidden; }
    .pf-item { display:flex; align-items:center; gap:.8rem; padding:.7rem .95rem; border-bottom:1px solid #f1f5f9; }
    .pf-item:last-child { border-bottom:0; }
    .pf-item-icone { width:40px; height:40px; border-radius:12px; background:var(--app); color:#fff; display:flex; align-items:center; justify-content:center; font-size:1rem; flex-shrink:0; }
    .pf-item-info { display:flex; flex-direction:column; min-width:0; }
    .pf-item-info small { font-size:.66rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#9ca3af; }
    .pf-item-info b { font-size:.88rem; color:#1f2937; word-break:break-word; }
    .pf-card { background:#fff; border:1px solid #eef1f6; border-radius:14px; padding:1.1rem 1.2rem; }
    @media (max-width: 576px) {
        .pf-header { flex-direction:column; text-align:center; }
        .pf-identidade { align-items:center; }
        .pf-meta { justify-content:center; }
    }
</style>
@stop

@section('js')
    <script>
        $(function () {
            $('#foto').on('change', function () {
                var file = this.files && this.files[0];
                if (! file) {
                    $('#btn-salvar-foto').addClass('d-none');
                    return;
                }

                var reader = new FileReader();
                reader.onload = function (e) {
                    $('#profile-avatar-preview').attr('src', e.target.result);
                };
                reader.readAsDataURL(file);

                $('#btn-salvar-foto').removeClass('d-none');
            });
        });
    </script>
@stop
