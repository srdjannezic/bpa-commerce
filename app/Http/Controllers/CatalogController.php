<?php

namespace App\Http\Controllers;

use App\Http\Requests\CatalogRequest;
use App\Services\CategoryService;
use App\Services\ProductService;
use Illuminate\Contracts\View\View;

class CatalogController extends Controller
{
    public function __construct(private ProductService $products, private CategoryService $categories) {}

    public function index(CatalogRequest $request): View
    {
        $page = filter_var($request->query('page', 1), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 1000000]]) ?: 1;
        $filters = array_filter($request->validated(), fn (?string $value) => $value !== null);
        $products = $this->products->catalog($page, $filters);
        abort_if($page > $products->lastPage(), 404);

        $categories = $this->categories->options();

        return view('catalog.index', compact('products', 'filters', 'categories'));
    }

    public function show(int $product): View
    {
        $item = $this->products->available($product);

        return view('catalog.show', ['product' => $item, 'related' => $this->products->related($item)]);
    }
}
