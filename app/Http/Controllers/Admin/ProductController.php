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
    /**
     * Set up inventory access and category options for product forms.
     */
    public function __construct(private ProductService $products, private CategoryService $categories) {}

    /**
     * Show the paginated inventory.
     */
    public function index(): View
    {
        return view('admin.products.index', ['products' => $this->products->inventory()]);
    }

    /**
     * Show the form for a new product.
     */
    public function create(): View
    {
        return view('admin.products.form', ['product' => null, 'categories' => $this->categories->options()]);
    }

    /**
     * Create a product from the validated form data.
     */
    public function store(ProductRequest $request): RedirectResponse
    {
        $this->products->save($request->validated());

        return to_route('admin.products.index')->with('status', 'Proizvod je dodat.');
    }

    /**
     * Show the form populated with the selected product.
     */
    public function edit(int $product): View
    {
        return view('admin.products.form', ['product' => $this->products->find($product), 'categories' => $this->categories->options()]);
    }

    /**
     * Save changes to the selected product using its submitted revision.
     */
    public function update(ProductRequest $request, int $product): RedirectResponse
    {
        $this->products->save($request->validated(), $product);

        return to_route('admin.products.index')->with('status', 'Proizvod je izmenjen.');
    }

    /**
     * Archive the selected product.
     */
    public function destroy(int $product): RedirectResponse
    {
        $this->products->archive($product);

        return to_route('admin.products.index')->with('status', 'Proizvod je arhiviran.');
    }
}
