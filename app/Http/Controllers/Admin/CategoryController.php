<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Services\CategoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CategoryController extends Controller
{
    public function __construct(private CategoryService $categories) {}

    public function index(): View
    {
        return view('admin.categories.index', ['categories' => $this->categories->options()]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $this->categories->create($request->validated('name'));

        return to_route('admin.categories.index')->with('status', 'Kategorija je dodata.');
    }
}
