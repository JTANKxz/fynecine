<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WatchProgress extends Model
{
    protected $table = 'watch_progress';
    
    protected $fillable = [
        'user_id',
        'guest_id',
        'content_id',
        'content_type',
        'progress',
        'duration',
        'season_id',
        'episode_id',
    ];

    protected $casts = [
        'progress' => 'integer',
        'duration' => 'integer',
    ];

    /**
     * Relação com usuário
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relação com filme (se content_type = movie)
     */
    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class, 'content_id');
    }

    /**
     * Relação com episódio (se content_type = episode)
     */
    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class, 'content_id');
    }

    /**
     * Retorna metadados do conteúdo para o Watch Progress
     */
    public function getContentMetadata(): ?array
    {
        if ($this->content_type === 'movie') {
            $movie = $this->movie;
            if (!$movie) return null;
            return [
                'title' => $movie->title,
                'backdrop_path' => $movie->backdrop_path,
                'poster_path' => $movie->poster_path,
                'type' => 'movie',
                'label' => null,
                'year' => $movie->release_year,
            ];
        } elseif ($this->content_type === 'episode') {
            $episode = $this->episode;
            if (!$episode) return null;
            
            $episode->loadMissing(['series', 'season']);
            
            return [
                'title' => $episode->series?->name ?? 'Série',
                'sub_title' => $episode->name,
                'backdrop_path' => $episode->still_path ?: $episode->series?->backdrop_path,
                'poster_path' => $episode->series?->poster_path,
                'type' => 'series',
                'label' => "T" . ($episode->season?->season_number ?? 1) . ":E" . ($episode->episode_number),
                'year' => $episode->series?->first_air_year,
            ];
        }
        return null;
    }
    /**
     * Salva ou atualiza progresso
     * 
     * @param string $contentId
     * @param int $progress em segundos
     * @param int $duration em segundos
     * @param string $contentType 'movie' ou 'episode'
     * @param int|null $userId
     * @param string|null $guestId
     * @param int|null $seasonId
     * @param int|null $episodeId
     */
    public static function saveProgress(
        string $contentId,
        int $progress,
        int $duration,
        string $contentType = 'movie',
        ?int $userId = null,
        ?string $guestId = null,
        ?int $seasonId = null,
        ?int $episodeId = null
    ): ?self {
        // Não salva se progresso < 30 segundos
        if ($progress < 30) {
            // Se existia, deleta
            self::where('content_id', $contentId)
                ->where('content_type', $contentType)
                ->when($userId, fn($q) => $q->where('user_id', $userId))
                ->when($guestId, fn($q) => $q->where('guest_id', $guestId))
                ->delete();
            return null;
        }

        // Remove se progress >= 90% da duração
        if ($duration > 0 && $progress >= ($duration * 0.9)) {
            self::where('content_id', $contentId)
                ->where('content_type', $contentType)
                ->when($userId, fn($q) => $q->where('user_id', $userId))
                ->when($guestId, fn($q) => $q->where('guest_id', $guestId))
                ->delete();
            return null;
        }

        $record = self::updateOrCreate(
            [
                'user_id' => $userId,
                'guest_id' => $guestId,
                'content_id' => $contentId,
                'content_type' => $contentType,
            ],
            [
                'progress' => $progress,
                'duration' => $duration,
                'season_id' => $seasonId,
                'episode_id' => $episodeId,
                'updated_at' => now(),
            ]
        );

        return $record;
    }

    /**
     * Obtém progresso de um conteúdo
     */
    public static function getProgress(
        string $contentId,
        string $contentType = 'movie',
        ?int $userId = null,
        ?string $guestId = null
    ): ?self {
        return self::where('content_id', $contentId)
            ->where('content_type', $contentType)
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->when($guestId, fn($q) => $q->where('guest_id', $guestId))
            ->first();
    }

    /**
     * Lista conteúdos em andamento
     */
    public static function inProgress(
        ?int $userId = null,
        ?string $guestId = null,
        int $limit = 20
    ): \Illuminate\Database\Eloquent\Collection {
        return self::when($userId, fn($q) => $q->where('user_id', $userId))
            ->when($guestId, fn($q) => $q->where('guest_id', $guestId))
            ->latest('updated_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Remove progresso de um conteúdo
     */
    public static function removeProgress(
        string $contentId,
        string $contentType = 'movie',
        ?int $userId = null,
        ?string $guestId = null
    ): bool {
        return (bool) self::where('content_id', $contentId)
            ->where('content_type', $contentType)
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->when($guestId, fn($q) => $q->where('guest_id', $guestId))
            ->delete();
    }

    /**
     * Limpa todos os progressos
     */
    public static function clearAll(
        ?int $userId = null,
        ?string $guestId = null
    ): int {
        return self::when($userId, fn($q) => $q->where('user_id', $userId))
            ->when($guestId, fn($q) => $q->where('guest_id', $guestId))
            ->delete();
    }

    /**
     * Migra progresso de guest para user
     */
    public static function migrateFromGuest(string $guestId, int $userId): void
    {
        // Busca todos os progressos do guest
        $guestProgress = self::where('guest_id', $guestId)->get();

        foreach ($guestProgress as $progress) {
            // Verifica se user já tem progresso deste conteúdo
            $userProgress = self::where('user_id', $userId)
                ->where('content_id', $progress->content_id)
                ->where('content_type', $progress->content_type)
                ->first();

            if ($userProgress) {
                // Mantém o mais recente
                if ($progress->updated_at > $userProgress->updated_at) {
                    $userProgress->update([
                        'progress' => $progress->progress,
                        'duration' => $progress->duration,
                    ]);
                }
                $progress->delete();
            } else {
                // Simplesmente associa ao user
                $progress->update([
                    'user_id' => $userId,
                    'guest_id' => null,
                ]);
            }
        }
    }
}
