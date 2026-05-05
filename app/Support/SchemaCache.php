<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SchemaCache
{
    public static function hasTable(string $table): bool
    {
        return Cache::rememberForever("schema_table_{$table}", function () use ($table) {
            return Schema::hasTable($table);
        });
    }

    public static function hasColumn(string $table, string $column): bool
    {
        $key = "schema_column_{$table}_{$column}";
        return Cache::rememberForever($key, function () use ($table, $column) {
            return Schema::hasColumn($table, $column);
        });
    }

    public static function clear(): void
    {
        // Não fazemos ideia de quais chaves existem, então quem precisar
        // limpar o cache de schema deve rodar: php artisan cache:clear
    }
}
