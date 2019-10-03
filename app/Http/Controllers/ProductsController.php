<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Helpers\HttpStatusCodes;
use App\Validators\ValidatesProductsRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductsController extends Controller {
    use ValidatesProductsRequests;

    /**
     * Show all products
     *
     * @return JsonResponse
     */
    public function index()
    {
        $products = Product::all();
        return response()->json($products, HttpStatusCodes::SUCCESS_OK);
    }

    /**
     * View product (multiple can be requested with comma's)
     *
     * @param string
     *
     * @return JsonResponse
     */
    public function show($id) {
        $ids = array_map('intval', explode(',', $id));

        return response()->json(
            Product::findOrFail($ids),
            HttpStatusCodes::SUCCESS_OK
        );
    }

    /**
     * Create product
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function create(Request $request) {
        $this->validateCreate($request);

        $product = Product::create([
            'name' => $request->get('name'),
            'description' => $request->get('description'),
            'img_url' => $request->get('img_url'),
            'price' => $request->get('price'),
            'tags' => $request->get('tags'),
            'recommended_addons' => $request->get('recommended_addons'),
        ]);

        return response()->json(
            $product,
            HttpStatusCodes::SUCCESS_CREATED
        );
    }

    /**
     * Update product
     *
     * @param Request $request
     * @param $id
     *
     * @return JsonResponse
     */
    public function update(Request $request, $id) {
        $product = Product::findOrFail($id);

        $this->validateUpdate($request, $product);

        $product->update([
            'name' => $request->get('name', $product->name),
            'description' => $request->get('description', $product->description),
            'img_url' => $request->get('img_url', $product->img_url),
            'price' => $request->get('price', $product->price),
            'tags' => $request->get('tags', $product->tags),
            'recommended_addons' => $request->get('recommended_addons', $product->recommended_addons),
        ]);

        $product->save();

        return response()->json(
            $product,
            HttpStatusCodes::SUCCESS_OK
        );
    }

    /**
     * Delete product
     *
     * @param $id
     *
     * @return JsonResponse
     */
    public function delete($id) {
        Product::findOrFail($id)->delete();

        return response()->json(
            null,
            HttpStatusCodes::SUCCESS_NO_CONTENT
        );
    }
}
