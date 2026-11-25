<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Services\CategoryService;
use App\Services\ProductService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ProductController extends Controller
{
    public function __construct(private ProductService $products, private CategoryService $categories) {}

    public function index(): View
    {
        return view('admin.products.index', ['products' => $this->products->inventory()]);
    }

    public function create(): View
    {
        return view('admin.products.form', ['product' => null, 'categories' => $this->categories->options()]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $this->products->save($request->validated());

        return to_route('admin.products.index')->with('status', 'Proizvod je dodat.');
    }

    public function edit(int $product): View
    {
        return view('admin.products.form', ['product' => $this->products->find($product), 'categories' => $this->categories->options()]);
    }

    public function update(ProductRequest $request, int $product): RedirectResponse
    {
        $this->products->save($request->validated(), $product);

        return to_route('admin.products.index')->with('status', 'Proizvod je izmenjen.');
    }

    public function destroy(int $product): RedirectResponse
    {
        $this->products->archive($product);

        return to_route('admin.products.index')->with('status', 'Proizvod je arhiviran.');
    }
}
