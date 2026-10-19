{{-- Nó recursivo da árvore de permissões: pastas (subníveis) + folhas (permissões) --}}
@php
    $folders = $node['folders'] ?? [];
    $perms   = $node['perms'] ?? [];
    ksort($folders);
@endphp

@foreach ($folders as $folder => $child)
    @php
        $subId = $prefix . '_' . preg_replace('/[^a-z0-9]/', '_', strtolower($folder));
        // conta checked recursivamente nas subpastas
        $contarChecked = function ($n) use (&$contarChecked, $oldPermissions) {
            $c = collect($n['perms'])->filter(fn ($p) => is_array($oldPermissions)
                ? in_array($p->id, $oldPermissions)
                : !empty($p->checked))->count();
            foreach ($n['folders'] as $f) { $c += $contarChecked($f); }
            return $c;
        };
        $childChecked = $contarChecked($child);
        $labelSub = $featureLabel[$folder] ?? ucfirst(str_replace(['_', '-'], ' ', $folder));
    @endphp
    <div class="re-sub">
        <div class="re-sub__head collapsed" data-toggle="collapse" data-target="#{{ $subId }}" aria-expanded="false">
            <i class="fas fa-chevron-down re-sub__chev"></i>
            <i class="fas fa-folder re-sub__icon"></i>
            <span class="re-sub__label">{{ $labelSub }}</span>
            <span class="re-sub__count">{{ $child['count'] }}</span>
            <span class="re-sub__checked" id="subbadge-{{ $subId }}" style="{{ $childChecked > 0 ? '' : 'display:none' }}">{{ $childChecked }}</span>
            <span class="re-sub__actions">
                <button type="button" class="re-btn-group-all" data-sub="{{ $subId }}" title="Selecionar todas de {{ $labelSub }}"><i class="fas fa-check-double"></i></button>
                <button type="button" class="re-btn-group-none" data-sub="{{ $subId }}" title="Desmarcar todas de {{ $labelSub }}"><i class="fas fa-times"></i></button>
            </span>
        </div>
        <div id="{{ $subId }}" class="collapse {{ $childChecked > 0 ? 'show' : '' }}">
            <div class="re-sub__body">
                @include('regra._perm-node', [
                    'node'           => $child,
                    'prefix'         => $subId,
                    'featureLabel'   => $featureLabel,
                    'acaoClasses'    => $acaoClasses,
                    'oldPermissions' => $oldPermissions,
                ])
            </div>
        </div>
    </div>
@endforeach

@if (count($perms))
    <div class="re-perm-grid">
        @foreach ($perms as $permissao)
            @php
                $checked = is_array($oldPermissions)
                    ? in_array($permissao->id, $oldPermissions)
                    : !empty($permissao->checked);
                $acao = str_contains($permissao->name, '.') ? substr($permissao->name, strrpos($permissao->name, '.') + 1) : $permissao->name;
                $acaoLabel = [
                    'proprio' => 'Apenas dele',
                    'equipe'  => 'Da equipe',
                    'todos'   => 'De todos',
                ][$acao] ?? $acao;
                $badgeCls = $acaoClasses[$acao] ?? 'badge-secondary';
            @endphp
            <label class="re-perm-item re-perm-item--compact {{ $checked ? 're-perm-item--checked' : '' }}"
                   for="perm-{{ $permissao->id }}"
                   title="{{ $permissao->name }}{{ $permissao->descricao ? ' — ' . $permissao->descricao : '' }}"
                   data-name="{{ strtolower($permissao->name) }}"
                   data-desc="{{ strtolower($permissao->descricao ?? '') }}">
                <input class="re-perm-item__chk" type="checkbox"
                    name="permissions[]"
                    value="{{ $permissao->id }}"
                    id="perm-{{ $permissao->id }}"
                    {{ $checked ? 'checked' : '' }}>
                <span class="badge {{ $badgeCls }} badge-sm re-perm-item__badge">{{ $acaoLabel }}</span>
            </label>
        @endforeach
    </div>
@endif
