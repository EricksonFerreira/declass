<div class="section-title" style="margin-bottom:10px !important">Estado da Viatura na Devolução</div>
@php
    $checkoutFotos = $data['checkout_fotos'] ?? [];
    $fotosBase = [
        'Direita' => asset('assets/imgs/carro/direita.jpg'),
        'Esquerda' => asset('assets/imgs/carro/esquerda.jpg'),
        'Frente' => asset('assets/imgs/carro/frente.jpg'),
        'Trás' => asset('assets/imgs/carro/atras.jpg'),
        'Velocímetro' => asset('assets/imgs/carro/cima.jpg'),
        'Combustível' => asset('assets/imgs/carro/frente.jpg'),
    ];
    $resolverUrlFoto = function ($path) {
        if (empty($path)) {
            return null;
        }

        $cleanPath = ltrim((string) $path, '/');

        if (str_contains($cleanPath, 'http://') || str_contains($cleanPath, 'https://')) {
            return $cleanPath;
        }

        if (str_starts_with($cleanPath, 'storage/')) {
            return asset($cleanPath);
        }

        if (str_starts_with($cleanPath, 'public/')) {
            return asset('storage/' . ltrim(substr($cleanPath, 7), '/'));
        }

        return asset('storage/' . $cleanPath);
    };
@endphp
<div class="viatura-images">
    @foreach($fotosBase as $label => $fallback)
        @php
            $fotoUrl = $resolverUrlFoto($checkoutFotos[$label] ?? null) ?? $fallback;
        @endphp
        <img src="{{ $fotoUrl }}" alt="{{ $label }} do check-out">
    @endforeach
</div>
@if(is_countable($estadoViatura) && count($estadoViatura) > 0)
    <div class="dano-box">
        <table>
            @foreach($estadoViatura as $item)
                <tr>
                    <td><strong>{{ $item['local'] ?? '-' }}</strong></td>
                    <td>
                        <div>
                            {{ $item['localizacao'] ?? '' }}
                            -
                            {{ $item['observacoes'] ?? '' }}
                            {{ $formatarTipoDano($item['tipo_dano'] ?? null) }}
                        </div>
                        @if(!empty($item['correcao']))
                            <div style="margin-top:3px;font-size:8px;color:#047857;">
                                <strong>Correção:</strong> {{ $item['correcao'] }}
                            </div>
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
@else
    <div class="muted" style="font-style:italic;margin-top:4px">
        Sem danos registrados no check-out.
    </div>
@endif
