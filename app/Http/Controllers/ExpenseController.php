<?php

namespace App\Http\Controllers;

use App\Imports\ExpensesImport;
use App\Models\Expense;
use App\Models\MessGroup;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $expenses = Expense::with('messGroup', 'member')->get();
        return response()->json(['message' => 'Expenses retrieved', 'data' => $expenses], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'mess_group_id' => 'required|exists:mess_groups,id',
                'member_id' => 'required|exists:members,id',
                'date' => 'required|date',
                'description' => 'required|string',
                'amount' => 'required|numeric|min:0',
            ]);

            $expense = Expense::create($validated);

            return response()->json(['message' => 'Expense created', 'data' => $expense], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'MessGroup or Member not found', 'errors' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred', 'errors' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $expense = Expense::with('messGroup', 'member')->findOrFail($id);
            return response()->json(['message' => 'Expense retrieved', 'data' => $expense], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Expense not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred', 'errors' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $expense = Expense::findOrFail($id);

            $validated = $request->validate([
                'mess_group_id' => 'sometimes|exists:mess_groups,id',
                'member_id' => 'sometimes|exists:members,id',
                'date' => 'sometimes|date',
                'description' => 'sometimes|string',
                'amount' => 'sometimes|numeric|min:0',
            ]);

            $expense->update($validated);

            return response()->json(['message' => 'Expense updated', 'data' => $expense], Response::HTTP_OK);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Expense not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred', 'errors' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $expense = Expense::findOrFail($id);
            $expense->delete();

            return response()->json(['message' => 'Expense deleted'], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Expense not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred', 'errors' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Import CSV data.
     */
    public function import(Request $request, MessGroup $messGroup): JsonResponse
    {
        try {
            $request->validate(['file' => 'required|mimes:csv']);
            $import = new ExpensesImport($messGroup->id);
            Excel::import($import, $request->file('file'));

            return response()->json(['message' => 'CSV imported successfully'], Response::HTTP_OK);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred', 'errors' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
