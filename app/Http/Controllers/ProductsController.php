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
            ['products' => $products]
        );
    }

    /**
     * View product.
     *
     * @param string $id
     *
     * @return JsonResponse
     */
    public function show($id)
    {
        $product = Product::findOrFail($id);

        return $this->respondSuccess(
            '',
            'SUCCESS_OK',
            $product->toArray()
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
            'img_url' => $request->get('img_url'),
            'price' => $request->get('price'),
            'subject' => $request->get('subject'),
            'class' => $request->get('class'),
            'method' => $request->get('method'),
        ]);

        return $this->respondSuccess(
            'product created',
            'SUCCESS_OK',
            $product->toArray()
        );
    }

    /**
     * Update product.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $this->validateUpdate($request, $product);

        $product->update([
            'name' => $request->get('name', $product->name),
            'img_url' => $request->get('img_url', $product->img_url),
            'price' => $request->get('price', $product->price),
            'subject' => $request->get('subject', $product->subject),
            'class' => $request->get('class', $product->class),
            'method' => $request->get('method', $product->method),
        ]);

        $product->save();

        return $this->respondSuccess(
            'product updated',
            'SUCCESS_OK',
            $product->toArray()
        );
    }

    /**
     * Delete product.
     *
     * @param int $id
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
