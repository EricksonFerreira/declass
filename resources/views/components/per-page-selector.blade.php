@php
    $rotaNome = \Illuminate\Support\Facades\Route::currentRouteName();
    $rotaBase = $rotaNome ? \Illuminate\Support\Str::beforeLast($rotaNome, '.') : null;
    $perPageAction = ($rotaBase && \Illuminate\Support\Facades\Route::has($rotaBase . '.setPerPage'))
        ? route($rotaBase . '.setPerPage')
        : null;
@endphp

@if ($perPageAction)
    <form method="POST" action="{{ $perPageAction }}" class="d-flex flex-row align-items-end mr-2 mb-0">
        @csrf
@else
    <div class="d-flex flex-row align-items-end mr-2">
@endif
        <label for="perPage" class="text-xs font-weight-normal mr-2" style="margin:0px;color:#6c757d">Itens Página</label>
        <select name="perPage" id="perPage" class="form-control form-control-sm p-0" onchange="this.form.submit()"
            style="
    padding:0px;height:1.3rem;width:3rem">
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
