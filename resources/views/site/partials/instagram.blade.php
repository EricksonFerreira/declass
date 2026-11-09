{{-- Convite para o Instagram da escola. Espera $links, $insta --}}
@if($links['instagram'])
<section class="s-instagram" aria-label="Instagram">
    <div class="wrap s-instagram-in">
        <p><i class="fab fa-instagram" aria-hidden="true"></i> Acompanhe o dia a dia da escola no Instagram</p>
        <a href="{{ $links['instagram'] }}" class="s-instagram-link" target="_blank" rel="noopener">{{ '@'.$insta }}<span class="sr"> (abre em nova aba)</span></a>
    </div>
</section>
@endif
