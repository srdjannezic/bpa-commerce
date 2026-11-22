<?php

namespace App\Http\Controllers;

use App\Http\Requests\CatalogRequest;
use App\Services\CategoryService;
use App\Services\ProductService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class CatalogController extends Controller
{
    /**
     * Set up catalog queries and category options.
     */
    public function __construct(private ProductService $products, private CategoryService $categories) {}

    /**
     * Render the filtered catalog and honor conditional browser requests.
     */
    public function index(CatalogRequest $request): Response
    {
        $page = filter_var($request->query('page', 1), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 1000000]]) ?: 1;
        $filters = array_filter($request->validated(), /** Keep validated filters with a supplied value. */ fn (?string $value) => $value !== null);
        $products = $this->products->catalog($page, $filters);
        abort_if($page > $products->lastPage(), 404);

        $categories = $this->categories->options();
        $response = response()->view('catalog.index', compact('products', 'filters', 'categories'));
        $response->setCache(['private' => true, 'no_cache' => true, 'must_revalidate' => true]);
        $response->setEtag(hash('sha256', $response->getContent().$products->getCollection()->pluck('revision')->implode(',')));
        $response->isNotModified($request);

        return $response;
    }

    /**
     * Show an active product and related products from its category.
     */
    public function show(int $product): View
    {
        $item = $this->products->available($product);

        return view('catalog.show', ['product' => $item, 'related' => $this->products->related($item)]);
    }
}
