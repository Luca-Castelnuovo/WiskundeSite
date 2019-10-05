<?php

namespace App\Http\Controllers;

use App\Helpers\HttpStatusCodes;
use App\Models\Product;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubjectsController extends Controller
{
    use ValidatesSubjectsRequests;

    /**
     * Show all subjects.
     *
     * @return JsonResponse
     */
    public function index()
    {
        return response()->json(
            Subject::all(),
            HttpStatusCodes::SUCCESS_OK
        );
    }

    /**
     * View subject (multiple can be requested with comma's).
     *
     * @param string
     * @param mixed $id
     *
     * @return JsonResponse
     */
    public function show($id)
    {
        $ids = array_map('intval', explode(',', $id));

        return response()->json(
            Subject::findOrFail($ids),
            HttpStatusCodes::SUCCESS_OK
        );
    }

    /**
     * View products with the requested subject.
     *
     * @param string
     * @param mixed $id
     *
     * @return JsonResponse
     */
    public function showProducts($id)
    {
        return response()->json(
            Product::where('subject', $id)->get(),
            HttpStatusCodes::SUCCESS_OK
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
            'color' => $request->get('color', $request->get('color')),
        ]);

        return response()->json(
            $subject,
            HttpStatusCodes::SUCCESS_CREATED
        );
    }

    /**
     * Update subject.
     *
     * @param Request $request
     * @param $id
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

        return response()->json(
            $subject,
            HttpStatusCodes::SUCCESS_OK
        );
    }

    /**
     * Delete subject.
     *
     * @param $id
     *
     * @return JsonResponse
     */
    public function delete($id)
    {
        Subject::findOrFail($id)->delete();

        return response()->json(
            null,
            HttpStatusCodes::SUCCESS_NO_CONTENT
        );
    }
}
