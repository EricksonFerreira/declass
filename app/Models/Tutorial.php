<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tutorial extends Model
{
    protected $table = 'tutoriais';

    public const VISIBILIDADE_TODOS = 'todos';

    public const VISIBILIDADE_SUPERADMIN = 'superadmin';

    protected $fillable = [
        'tutorial_categoria_id',
        'titulo',
        'descricao',
        'video_path',
        'video_url',
        'capa_path',
        'ordem',
        'visibilidade',
        'principal',
        'ativo',
    ];

    protected $casts = [
        'principal' => 'boolean',
        'ativo' => 'boolean',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(TutorialCategoria::class, 'tutorial_categoria_id');
    }

    public function getCapaUrlAttribute(): ?string
    {
        return $this->capa_path ? asset('storage/'.$this->capa_path) : null;
    }

    /**
     * URL pronta para exibicao: video enviado, embed de YouTube/Vimeo
     * ou o link original quando nao for plataforma conhecida.
     */
    public function getVideoExibicaoAttribute(): ?array
    {
        if ($this->video_path) {
            return [
                'tipo' => 'arquivo',
                'url' => asset('storage/'.$this->video_path),
            ];
        }

        if (! $this->video_url) {
            return null;
        }

        $embed = $this->resolverEmbed($this->video_url);

        return [
            'tipo' => $embed ? 'embed' : 'link',
            'url' => $embed ?? $this->video_url,
        ];
    }

    private function resolverEmbed(string $url): ?string
    {
        if (preg_match('~(?:youtube\.com/(?:watch\?v=|shorts/|embed/)|youtu\.be/)([\w-]{6,})~i', $url, $m)) {
            return 'https://www.youtube.com/embed/'.$m[1];
        }

        if (preg_match('~vimeo\.com/(\d+)~i', $url, $m)) {
            return 'https://player.vimeo.com/video/'.$m[1];
        }

        return null;
    }
}
