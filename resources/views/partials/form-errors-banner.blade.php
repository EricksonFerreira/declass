@if ($errors->any())
    <div class="fx-errors-banner" role="alert" style="margin:0 auto 1.25rem;max-width:960px;padding:.85rem 1rem;border-radius:12px;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;font-size:.9rem;">
        <strong style="display:block;margin-bottom:.35rem;">Não foi possível salvar. Verifique os campos:</strong>
        <ul style="margin:0;padding-left:1.15rem;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
