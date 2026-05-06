<div class="doc-header">
    <div class="doc-header__logo">
        @if(!empty($get('documento.empresa.logo_url', null)))
            <img src="{{ $get('documento.empresa.logo_url') }}" alt="Logo da empresa">
        @else
            <img class="sistema-logo" src="{{ asset('assets/imgs/logo.png') }}" alt="DEcars">
        @endif
    </div>
    <div class="doc-header__info">
        <div class="doc-header__title">Contrato de Aluguel</div>
        <div class="doc-header__subtitle">
            {{ $get('documento.empresa.nome', 'Locadora') }}
            @if($get('documento.empresa.cnpj', null))
                &bull; CNPJ: {{ $get('documento.empresa.cnpj') }}
            @endif
            &bull; Condições Particulares
        </div>
        <div class="doc-header__company">
            @if($get('documento.empresa.endereco', null))
                {{ $get('documento.empresa.endereco') }}
            @endif
            @if($get('documento.empresa.telefone', null))
                &bull; {{ $get('documento.empresa.telefone') }}
            @endif
            @if($get('documento.empresa.email', null))
                &bull; {{ $get('documento.empresa.email') }}
            @endif
        </div>
        <div class="doc-header__num">
            {{ $get('contrato.numero', '#00000') }}
        </div>
    </div>
</div>
