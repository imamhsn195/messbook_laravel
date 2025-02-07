<?php

namespace App\Http\Controllers;

use App\Models\MessGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Response;

class MessGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $messGroups = MessGroup::all();
            return response()->json($messGroups, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve mess groups', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validate the incoming request
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'fixed_cost' => 'required|numeric|min:0',
            ]);

            // Calculate total_days if end_date is provided
            if ($request->has('start_date') && $request->has('end_date')) {
                $startDate = \Carbon\Carbon::parse($validated['start_date']);
                $endDate = \Carbon\Carbon::parse($validated['end_date']);
                $validated['total_days'] = (int) $startDate->diffInDays($endDate) + 1;
            }

            // If no end_date is provided, set it to the last day of the start_date's month
            if (empty($validated['total_days'])) {
                $startDate = \Carbon\Carbon::parse($validated['start_date']);
                $endDate = (clone $startDate)->endOfMonth();
                $validated['end_date'] = $endDate->toDateString();
                $validated['total_days'] = (int) $startDate->diffInDays($endDate) + 1;
            }

            // Create the MessGroup
            $messGroup = MessGroup::create($validated);

            return response()->json(['message' => 'Mess group created', 'data' => $messGroup], Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create mess group', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        try {
            $messGroup = MessGroup::findOrFail($id);
            return response()->json($messGroup, Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Mess group not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching mess group', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $messGroup = MessGroup::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|nullable|date|after_or_equal:start_date',
                'fixed_cost' => 'sometimes|numeric|min:0',
            ]);

            if ($request->has('start_date')) {
                $startDate = \Carbon\Carbon::parse($validated['start_date']);
                $messGroup->start_date = $startDate->toDateString();
            } else {
                $startDate = \Carbon\Carbon::parse($messGroup->start_date);
            }

            if ($request->has('end_date')) {
                $endDate = \Carbon\Carbon::parse($validated['end_date']);
                $messGroup->end_date = $endDate->toDateString();
            } else {
                $endDate = \Carbon\Carbon::parse($messGroup->end_date);
            }

            // Calculate `total_days` only if both `start_date` and `end_date` exist
            if ($messGroup->start_date && $messGroup->end_date) {
                $messGroup->total_days = $startDate->diffInDays($endDate) + 1; // Including the start date
            }

            if ($request->has('name')) {
                $messGroup->name = $validated['name'];
            }

            if ($request->has('fixed_cost')) {
                $messGroup->fixed_cost = $validated['fixed_cost'];
            }

            $messGroup->save();

            return response()->json(['message' => 'Mess group updated', 'data' => $messGroup], Response::HTTP_OK);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], Response::HTTP_BAD_REQUEST);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Mess group not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update mess group', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {
            MessGroup::findOrFail($id)->delete();
            return response()->json(['message' => 'Mess group deleted'], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Mess group not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete mess group', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getMembers($id): JsonResponse
    {
        try {
            $messGroup = MessGroup::with('members')->findOrFail($id);
            return response()->json(['members' => $messGroup->members], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Mess group not found.'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve members', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function addMember(Request $request, $id): JsonResponse
    {
        try {
            $request->validate([
                'member_id' => 'required|exists:members,id',
            ]);

            $messGroup = MessGroup::findOrFail($id);
            $messGroup->members()->attach($request->member_id);

            return response()->json(['message' => 'Member added successfully'], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Mess group not found.'], Response::HTTP_NOT_FOUND);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to add member', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function removeMember(Request $request, $messGroupId, $memberId)
    {
        $messGroup = MessGroup::findOrFail($messGroupId);

        // Detach member
        $messGroup->members()->detach($memberId);

        // Dispatch event to recalculate balances
        event(new MessGroupUpdated($messGroupId, 'member removed'));

        return response()->json(['message' => 'Member removed successfully']);
    }
    /**
     * Calculate balances including fixed cost.
     */
    public function calculateBalances($id): JsonResponse
    {
        try {
            $messGroup = MessGroup::findOrFail($id);
            $members = $messGroup->members;
            $memberCount = $members->count();
            $totalDays = $messGroup->total_days;
            $fixedCost = $messGroup->fixed_cost; // Per-member cooking cost

            // Validate input
            if ($totalDays === 0 || $memberCount === 0) {
                return response()->json(
                    ['message' => 'Invalid data: total days or member count is zero.'],
                    Response::HTTP_BAD_REQUEST
                );
            }

            // Calculate variable share (shared expenses split equally)
            $totalVariableExpenses = $messGroup->expenses()->sum('amount');
            $variableSharePerMember = $totalVariableExpenses / $memberCount;

            // Total owed per member (variable + fixed cooking cost)
            $totalOwedPerMember = $variableSharePerMember + $fixedCost;

            // Get all expenses
            $expenses = $messGroup->expenses;

            foreach ($members as $member) {
                // Total variable expenses paid by this member
                $paidAmount = $expenses->where('member_id', $member->id)->sum('amount');

                // Deposits (pre-payments to offset total owed)
                $deposits = $member->pivot->deposits;

                // Calculate balance
                $balance = ($paidAmount + $deposits) - $totalOwedPerMember;

                // Update the member's balance
                $messGroup->members()->updateExistingPivot($member->id, [
                    'balance' => round($balance, 2)
                ]);
                $messGroup->refresh();
            }

            return response()->json(
                ['message' => 'Balances updated successfully', 'data' => $messGroup],
                Response::HTTP_OK
            );
        } catch (ModelNotFoundException $e) {
            return response()->json(
                ['message' => 'Mess group not found.'],
                Response::HTTP_NOT_FOUND
            );
        } catch (\Exception $e) {
            return response()->json(
                ['message' => 'Failed to calculate balances', 'error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
