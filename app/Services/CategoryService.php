<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Validation\ValidationException;

class CategoryService
{
    /**
     * Return category IDs and names ordered by name.
     *
     * @return Collection<int, Category>
     */
    public function options(): Collection
    {
        return Category::query()->orderBy('name')->get(['id', 'name']);
    }

    /**
     * Create a category and report a duplicate name as a validation error.
     *
     * @throws ValidationException
     */
    public function create(string $name): void
    {
        try {
            Category::query()->create(['name' => $name]);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['name' => 'Kategorija sa tim nazivom već postoji.']);
        }
    }
}
