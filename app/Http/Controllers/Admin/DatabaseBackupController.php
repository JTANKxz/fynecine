<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatabaseBackupController extends Controller
{
    private const GROUPS = [
        'movies' => [
            'label' => 'Filmes',
            'description' => 'Filmes, gêneros, elenco, palavras-chave, links e categorias relacionadas.',
            'tables' => ['movies', 'genres', 'genre_movie', 'casts', 'castables', 'tmdb_keywords', 'keywordables', 'movie_play_links', 'movie_download_links', 'content_categories', 'sliders', 'profile_lists'],
        ],
        'series' => [
            'label' => 'Séries',
            'description' => 'Cadastro das séries, gêneros, elenco, palavras-chave e relações necessárias.',
            'tables' => ['series', 'genres', 'genre_series', 'casts', 'castables', 'tmdb_keywords', 'keywordables', 'content_categories', 'sliders'],
        ],
        'seasons' => [
            'label' => 'Temporadas',
            'description' => 'Temporadas e séries às quais pertencem, preservando as chaves estrangeiras.',
            'tables' => ['seasons', 'series'],
        ],
        'episodes' => [
            'label' => 'Episódios',
            'description' => 'Episódios, temporadas, séries, links de reprodução e downloads.',
            'tables' => ['episodes', 'seasons', 'series', 'episode_links', 'episode_download_links'],
        ],
        'users' => [
            'label' => 'Usuários, perfis e assinaturas',
            'description' => 'Contas, perfis, planos, pagamentos, cupons, tokens e relações necessárias.',
            'tables' => ['users', 'profiles', 'subscription_plans', 'pix_payments', 'cakto_campaign_purchases', 'personal_access_tokens', 'coupons', 'user_coupons', 'reward_claims', 'notifications', 'notification_user', 'profile_lists', 'watch_progress', 'playlists', 'playlist_items'],
        ],
    ];

    public function index()
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
        $driver = DB::connection()->getDriverName();
        $groups = self::GROUPS;
        return view('admin.backups.index', compact('groups', 'driver'));
    }

    public function download(string $type): StreamedResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
        abort_unless($type === 'full' || isset(self::GROUPS[$type]), 404);

        $connection = DB::connection();
        abort_unless(in_array($connection->getDriverName(), ['mysql', 'mariadb'], true), 422, 'A exportação SQL está disponível para MySQL/MariaDB.');

        $allTables = collect(DB::select('SHOW FULL TABLES'))
            ->filter(fn ($row) => strtoupper((string) (array_values((array) $row)[1] ?? '')) === 'BASE TABLE')
            ->map(fn ($row) => array_values((array) $row)[0])
            ->filter(fn ($name) => is_string($name) && preg_match('/^[A-Za-z0-9_]+$/', $name))
            ->values()->all();

        $tables = $type === 'full' ? $allTables : $this->resolveTables(self::GROUPS[$type]['tables'], $allTables);
        abort_if(empty($tables), 422, 'Nenhuma tabela disponível para esta seleção.');

        $filename = 'fynecine-' . $type . '-' . now()->format('Y-m-d_His') . '.sql';

        return response()->streamDownload(function () use ($connection, $tables, $type) {
            $out = fopen('php://output', 'wb');
            fwrite($out, "-- FYNECINE DATABASE BACKUP\n-- Gerado em: " . now()->toDateTimeString() . "\n-- Tipo: {$type}\n\n");
            fwrite($out, "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\nSET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n");

            foreach ($tables as $table) {
                $q = chr(96) . str_replace(chr(96), chr(96).chr(96), $table) . chr(96);
                $create = $connection->selectOne("SHOW CREATE TABLE {$q}");
                $createValues = $create ? array_values((array) $create) : [];
                $createSql = $createValues[1] ?? null;
                if (!$createSql) continue;

                fwrite($out, "\n-- Estrutura: {$table}\nDROP TABLE IF EXISTS {$q};\n{$createSql};\n\n");
                $columns = collect($connection->select("SHOW COLUMNS FROM {$q}"))->pluck('Field')->all();
                if (!$columns) continue;

                $columnSql = implode(', ', array_map(fn ($column) => chr(96) . str_replace(chr(96), chr(96).chr(96), $column) . chr(96), $columns));
                foreach ($connection->table($table)->cursor() as $row) {
                    $values = [];
                    foreach ($columns as $column) {
                        $value = $row->{$column};
                        if ($value === null) $values[] = 'NULL';
                        elseif (is_bool($value)) $values[] = $value ? '1' : '0';
                        else $values[] = $connection->getPdo()->quote((string) $value);
                    }
                    fwrite($out, "INSERT INTO {$q} ({$columnSql}) VALUES (" . implode(', ', $values) . ");\n");
                }
                fwrite($out, "\n");
            }
            fwrite($out, "\nSET FOREIGN_KEY_CHECKS=1;\n");
            fclose($out);
        }, $filename, ['Content-Type' => 'application/sql; charset=UTF-8', 'Cache-Control' => 'no-store, private']);
    }

    private function resolveTables(array $requested, array $allTables): array
    {
        $available = array_fill_keys($allTables, true);
        $selected = [];
        foreach ($requested as $table) if (isset($available[$table])) $selected[$table] = true;

        $pending = array_keys($selected);
        while ($pending) {
            $table = array_pop($pending);
            $parents = DB::select(
                'SELECT DISTINCT REFERENCED_TABLE_NAME AS parent_table
                 FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL',
                [$table]
            );
            foreach ($parents as $parent) {
                $name = $parent->parent_table ?? null;
                if ($name && isset($available[$name]) && !isset($selected[$name])) {
                    $selected[$name] = true;
                    $pending[] = $name;
                }
            }
        }
        return array_values(array_filter($allTables, fn ($table) => isset($selected[$table])));
    }
}
