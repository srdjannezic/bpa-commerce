<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class CategoryService
{
    public const CACHE_KEY = 'categories';

    public function options(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => Category::query()->orderBy('name')->pluck('name', 'id')->all());
    }

    public function create(string $name): void
    {
        try {
            Category::query()->create(['name' => $name]);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['name' => 'Kategorija sa tim nazivom već postoji.']);
        }

        Cache::forget(self::CACHE_KEY);
    }
}
