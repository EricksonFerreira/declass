@php
    $rotaNome = \Illuminate\Support\Facades\Route::currentRouteName();
    $rotaBase = $rotaNome ? \Illuminate\Support\Str::beforeLast($rotaNome, '.') : null;
    $perPageAction = ($rotaBase && \Illuminate\Support\Facades\Route::has($rotaBase . '.setPerPage'))
        ? route($rotaBase . '.setPerPage')
        : null;
@endphp

@if ($perPageAction)
    <form method="POST" action="{{ $perPageAction }}" class="ux-per-page mb-0">
        @csrf
@else
    <div class="ux-per-page">
@endif
        <label for="perPage" class="ux-per-page__label" title="Itens por página">
            <i class="fas fa-list-ol"></i><span class="d-none d-sm-inline">Itens</span>
        </label>
        <select name="perPage" id="perPage" class="ux-per-page__select" onchange="this.form.submit()"
            title="Itens por página" aria-label="Itens por página">
            @foreach ($perPages as $perPage)
                <option value="{{ $perPage }}" {{ $perPage == $selectedPerPage ? 'selected' : '' }}>{{ $perPage }}
                </option>
            @endforeach
        </select>
@if ($perPageAction)
    </form>
@else
    </div>
@endif
