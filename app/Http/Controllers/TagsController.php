<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Tag;
use App\Helpers\HttpStatusCodes;
use App\Validators\ValidatesTagsRequests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TagsController extends Controller {
    use ValidatesTagsRequests;

    /**
     * Show all tags
     *
     * @return JsonResponse
     */
    public function index()
    {
        return response()->json(
            Tag::all(),
            HttpStatusCodes::SUCCESS_OK
        );
    }

    /**
     * View tag (multiple can be requested with comma's)
     *
     * @param string
     *
     * @return JsonResponse
     */
    public function show($id) {
        $ids = array_map('intval', explode(',', $id));

        return response()->json(
            Tag::findOrFail($ids),
            HttpStatusCodes::SUCCESS_OK
        );
    }

    /**
     * View products with the requested tag
     *
     * @param string
     *
     * @return JsonResponse
     */
    public function showProducts($id) {
        $ids = array_map('intval', explode(',', $id));

        return response()->json(
            Product::whereJsonContains('tags', $ids)->get(),
            HttpStatusCodes::SUCCESS_OK
        );
    }

    /**
     * Create tag
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function create(Request $request) {
        $this->validateCreate($request);

        $tag = Tag::create([
            'name' => $request->get('name', $tag->name),
            'color' => $request->get('color', $tag->color)
        ]);

        return response()->json(
            $tag,
            HttpStatusCodes::SUCCESS_CREATED
        );
    }

    /**
     * Update tag
     *
     * @param Request $request
     * @param $id
     *
     * @return JsonResponse
     */
    public function update(Request $request, $id) {
        $tag = Tag::findOrFail($id);

        $this->validateUpdate($request, $tag);

        $tag->update([
            'name' => $request->get('name', $tag->name),
            'color' => $request->get('color', $tag->color)
        ]);

        $tag->save();

        return response()->json(
            $tag,
            HttpStatusCodes::SUCCESS_OK
        );
    }

    /**
     * Delete tag
     *
     * @param $id
     *
     * @return JsonResponse
     */
    public function delete($id) {
        Tag::findOrFail($id)->delete();

        return response()->json(
            null,
            HttpStatusCodes::SUCCESS_NO_CONTENT
        );
    }
}
