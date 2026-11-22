<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Services\CategoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CategoryController extends Controller
{
    /**
     * Set up the category service for the admin area.
     */
    public function __construct(private CategoryService $categories) {}

    /**
     * Show the existing categories and the category form.
     */
    public function index(): View
    {
        return view('admin.categories.index', ['categories' => $this->categories->options()]);
    }

    /**
     * Create a category from the validated name.
     */
    public function store(CategoryRequest $request): RedirectResponse
    {
        $this->categories->create($request->validated('name'));

        return to_route('admin.categories.index')->with('status', 'Kategorija je dodata.');
    }
}
