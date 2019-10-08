<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Validators\ValidatesProductsRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    use ValidatesProductsRequests;

    /**
     * Show all products.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $products = Product::all();

        return $this->respondSuccess(
            '',
            'SUCCESS_OK',
            $products
        );
    }

    /**
     * View product
     * Multiple can be requested with comma's.
     * e.g. /products/1,2.
     *
     * @param string $id
     *
     * @return JsonResponse
     */
    public function show($id)
    {
        $ids = array_map('intval', explode(',', $id));
        $products = Product::findOrFail($ids);

        return $this->respondSuccess(
            '',
            'SUCCESS_OK',
            $products
        );
    }

    /**
     * Create product.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        $this->validateCreate($request);

        $product = Product::create([
            'name' => $request->get('name'),
            'description' => $request->get('description'),
            'img_url' => $request->get('img_url'),
            'price' => $request->get('price'),
            'subject' => $request->get('subject'),
        ]);

        return $this->respondSuccess(
            '',
            'SUCCESS_OK',
            $product
        );
    }

    /**
     * Update product.
     *
     * @param Request $request
     * @param $id
     *
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $this->validateUpdate($request, $product);

        $product->update([
            'name' => $request->get('name', $product->name),
            'description' => $request->get('description', $product->description),
            'img_url' => $request->get('img_url', $product->img_url),
            'price' => $request->get('price', $product->price),
            'subject' => $request->get('subject', $product->subject),
        ]);

        $product->save();

        return $this->respondSuccess(
            '',
            'SUCCESS_OK',
            $product
        );
    }

    /**
     * Delete product.
     *
     * @param $id
     *
     * @return JsonResponse
     */
    public function delete($id)
    {
        Product::findOrFail($id)->delete();

        return $this->respondSuccess(
            'product deleted',
            'SUCCESS_OK'
        );
    }
}
