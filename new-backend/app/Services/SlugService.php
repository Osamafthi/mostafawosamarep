<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SlugService
{
    public function uniqueFor(string $table, string $text, ?int $ignoreId = null, string $column = 'slug'): string
    {
        $base = Str::slug($text) ?: 'n-a';
        $slug = $base;
        $suffix = 2;

        while ($this->exists($table, $column, $slug, $ignoreId)) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    protected function exists(string $table, string $column, string $value, ?int $ignoreId): bool
    {
        $query = DB::table($table)->where($column, $value);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }
}
