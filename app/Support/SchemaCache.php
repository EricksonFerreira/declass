<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SchemaCache
{
    private const MAP_KEY = 'schema_map_v1';

    private static ?array $map = null;

    /**
     * Mapa completo de tabelas e colunas, cacheado em uma unica chave.
     * Evita uma query no store de cache por cada verificacao de schema.
     */
    private static function map(): array
    {
        if (self::$map !== null) {
            return self::$map;
        }

        return self::$map = Cache::rememberForever(self::MAP_KEY, function () {
            $map = [];

            foreach (Schema::getTables() as $table) {
                $map[$table['name']] = array_fill_keys(Schema::getColumnListing($table['name']), true);
            }

            return $map;
        });
    }

    public static function hasTable(string $table): bool
    {
        return isset(self::map()[$table]);
    }

    public static function hasColumn(string $table, string $column): bool
    {
        return isset(self::map()[$table][$column]);
    }

    public static function clear(): void
    {
        self::$map = null;
        Cache::forget(self::MAP_KEY);
    }
}
