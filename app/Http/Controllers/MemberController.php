<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Member;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $members = Member::all();
        return response()->json(['message' => 'Member retrieved successfully', 'data' => $members], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $member = Member::create($validated);
            return response()->json(['message' => 'Member created successfully', 'data' => $member], Response::HTTP_OK);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        try {
            $member = Member::findOrFail($id);
            if (!$member) {
                return response()->json([
                    'message' => 'Member not found'
                ], Response::HTTP_NOT_FOUND);
            }
            return response()->json(["message" => "Member retrieved successfully", 'data' => $member], Response::HTTP_OK);
        }catch (ModelNotFoundException $e){
            return response()->json(['message' => 'Member not found', 'errors' => $e->getMessage()],Response::HTTP_NOT_FOUND);
        }catch (\Exception $e){
            return response()->json(['message' => $e->getMessage()],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $member = Member::findOrFail($id);
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
            ]);

            $member->update($validated);
            return response()->json($member, Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Member not found'], Response::HTTP_NOT_FOUND);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation failed','errors' => $e->errors()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $member = Member::findOrFail($id);
            $member->delete();
            return response()->json(['message' => 'Member deleted successfully'], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Member not found', 'errors' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }
}
