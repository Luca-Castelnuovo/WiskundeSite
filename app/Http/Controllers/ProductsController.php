<?php

namespace App\Http\Controllers;

use App\Helpers\CloudConvertHelper;
use App\Helpers\UtilsHelper;
use App\Mail\ProductStateUpdateMail;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Validators\ValidatesProductsRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    use ValidatesProductsRequests;

    /**
     * Define authorization.
     */
    public function __construct()
    {
        $this->middleware('authorization:teacher.admin', ['only' => [
            'create',
            'update',
            'delete',
        ]]);
    }

    /**
     * Show all products.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function all(Request $request)
    {
        $products = Product::all();

        switch ($request->role) {
            case 'student':
                $products = $products->filter(function ($product) {
                    return 'accepted' === $product->state;
                });

                break;
            case 'teacher':
                $products = $products->filter(function ($product) use ($request) {
                    return 'accepted' === $product->state || $request->user_id === $product->user_id;
                });

                break;
        }

        return $this->respondSuccess(
            '',
            'SUCCESS_OK',
            ['products' => $products]
        );
    }

    /**
     * Show product info.
     *
     * @param Request $request
     * @param string  $id
     *
     * @return JsonResponse
     */
    public function show(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        if ('admin' !== $request->role && $product->user_id !== $request->user_id) {
            if ('accepted' !== $product->state) {
                return $this->respondError(
                    'product can\'t be accessed while '.$product->state,
                    'CLIENT_ERROR_FORBIDDEN'
                );
            }
        }

        $order = Order::whereUserId($request->user_id)
            ->whereState('paid')
            ->whereJsonContains('products', [(int) $id])
            ->first()
        ;

        $product->owned_by_user = (bool) $order;

        return $this->respondSuccess(
            '',
            'SUCCESS_OK',
            $product->toArray()
        );
    }

    /**
     * Open product pdf.
     *
     * @param Request $request
     * @param string  $id
     *
     * @return JsonResponse
     */
    public function open(Request $request, $id)
    {
        $id = (int) $id;
        $product = Product::findOrFail($id);

        if ('admin' !== $request->role) {
            if ('denied' === $product->state) {
                return $this->respondError(
                    'product can\'t be accessed while denied',
                    'CLIENT_ERROR_FORBIDDEN'
                );
            }

            if ($product->user_id !== $request->user_id) {
                if ('under_review' === $product->state) {
                    return $this->respondError(
                        'product can\'t be accessed while under review',
                        'CLIENT_ERROR_FORBIDDEN'
                    );
                }

                $order = Order::whereUserId($request->user_id)
                    ->whereState('paid')
                    ->whereJsonContains('products', [$id])
                    ->first()
                ;

                if (!$order) {
                    return $this->respondError(
                        'product not purchased',
                        'CLIENT_ERROR_FORBIDDEN'
                    );
                }
            }
        }

        $s3_client = app('aws')->createClient('s3');
        $s3_cmd = $s3_client->getCommand('GetObject', [
            'Bucket' => config('services.s3.bucket'),
            'Key' => $product->fileKey,
        ]);

        $expires = time() + config('services.s3.url_ttl');
        $s3_request = $s3_client->createPresignedRequest($s3_cmd, $expires);
        $s3_signed_url = (string) $s3_request->getUri();

        return $this->respondSuccess(
            '',
            'SUCCESS_OK',
            ['url' => $s3_signed_url]
        );
    }

    /**
     * Create product.
     *
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        $this->validateCreate($request);

        $s3_client = app('aws')->createClient('s3');
        $file_key = UtilsHelper::generateRandomToken().'.pdf';

        $file = $request->get('file');
        $file_content = base64_decode($file);

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->buffer($file_content);

        if ('application/pdf' !== $mime_type) {
            $file_content = CloudConvertHelper::fileToPDF($file_content, $mime_type);
        }

        if (!$file_content) {
            return $this->respondError(
                'invalid file type',
                'CLIENT_ERROR_BAD_REQUEST'
            );
        }

        // TODO: $file_content = CloudConvertHelper::encryptPDF($file_content);
        dd($file_content);

        $s3_client->putObject([
            'Bucket' => config('services.s3.bucket'),
            'Key' => $file_key,
            'Body' => $file_content,
            'ContentType' => 'application/pdf',
        ]);

        $product = Product::create([
            'user_id' => $request->user_id,
            'name' => $request->get('name'),
            'price' => $request->get('price'),
            'subject' => $request->get('subject'),
            'class' => $request->get('class'),
            'method' => $request->get('method'),
            'fileKey' => $file_key,
            'state' => 'under_review',
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

        if ('admin' !== $request->role) {
            if ($product->user_id !== $request->user_id) {
                return $this->respondError(
                    'product is not owned by you',
                    'CLIENT_ERROR_FORBIDDEN'
                );
            }

            if ('denied' === $product->state) {
                return $this->respondError(
                    'product can\'t be accessed while denied',
                    'CLIENT_ERROR_FORBIDDEN'
                );
            }

            if ($request->get('state')) {
                return $this->respondError(
                    'product state can\'t be updated by teachers',
                    'CLIENT_ERROR_FORBIDDEN'
                );
            }
        }

        $product->update([
            'name' => $request->get('name', $product->name),
            'img_url' => $request->get('img_url', $product->img_url),
            'price' => $request->get('price', $product->price),
            'subject' => $request->get('subject', $product->subject),
            'class' => $request->get('class', $product->class),
            'method' => $request->get('method', $product->method),
            'state' => $request->get('state', $product->state),
        ]);

        if ($request->get('state')) {
            $product_owner = User::findOrFail($product->user_id);

            Mail::to($product_owner->email)->send(new ProductStateUpdateMail(
                $product_owner,
                $product->state,
                $request->get('reason')
            ));
        }

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
     * @param Request $request
     * @param int     $id
     *
     * @return JsonResponse
     */
    public function delete(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        if ('admin' !== $request->role) {
            if ($product->user_id !== $request->user_id) {
                return $this->respondError(
                    'product is not owned by you',
                    'CLIENT_ERROR_FORBIDDEN'
                );
            }
        }

        $s3_client = app('aws')->createClient('s3');
        $s3_client->deleteObject([
            'Bucket' => config('services.s3.bucket'),
            'Key' => $product->fileKey,
        ]);

        $product->delete();

        return $this->respondSuccess(
            'product deleted',
            'SUCCESS_OK'
        );
    }
}
