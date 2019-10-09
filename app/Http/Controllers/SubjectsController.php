<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubjectsController extends Controller
{
    use ValidatesSubjectsRequests;

    /**
     * Show subjects.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $subjects = Subject::all();

        return $this->respondSuccess(
            '',
            'SUCCESS_OK',
            $subjects
        );
    }

    /**
     * View subject (multiple can be requested with comma's).
     *
     * @param mixed $id
     *
     * @return JsonResponse
     */
    public function show($id)
    {
        $ids = array_map('intval', explode(',', $id));
        $subjects = Subject::findOrFail($ids);

        return $this->respondSuccess(
            '',
            'SUCCESS_OK',
            $subjects
        );
    }

    /**
     * View products with the subject.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function showProducts($id)
    {
        $products_with_subject = Product::whereSubject($id)->get();

        return $this->respondSuccess(
            '',
            'SUCCESS_OK',
            $products_with_subject
        );
    }

    /**
     * Create subject.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        $this->validateCreate($request);

        $subject = Subject::create([
            'name' => $request->get('name', $request->get('name')),
        ]);

        return $this->respondSuccess(
            '',
            'SUCCESS_CREATED',
            $subject
        );
    }

    /**
     * Update subject.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $subject = Subject::findOrFail($id);

        $this->validateUpdate($request, $subject);

        $subject->update([
            'name' => $request->get('name', $subject->name),
            'color' => $request->get('color', $subject->color),
        ]);

        $subject->save();

        return $this->respondSuccess(
            '',
            'SUCCESS_OK',
            $subject
        );
    }

    /**
     * Delete subject.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function delete($id)
    {
        Subject::findOrFail($id)->delete();

        return $this->respondSuccess(
            'subject deleted',
            'SUCCESS_OK'
        );
    }
}
