<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BorrowRecord;
use App\Services\BorrowService;
use Illuminate\Http\Request;

class BorrowController extends Controller
{
    public function __construct(private BorrowService $borrowService) {}

    public function index(Request $request)
    {
        $borrows = $this->borrowService->getAllBorrows($request->only([
            'status', 'item_id', 'overdue'
        ]));
        return response()->json($borrows);
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_id'           => 'required|exists:inventory_items,id',
            'borrower_name'     => 'required|string|max:255',
            'contact'           => 'required|string|max:255',
            'quantity_borrowed' => 'required|integer|min:1',
            'borrow_date'       => 'required|date',
            'expected_return'   => 'required|date|after:borrow_date',
            'notes'             => 'nullable|string',
        ]);

        try {
            $borrow = $this->borrowService->createBorrow($request->all(), auth()->id());
            return response()->json(['message' => 'Borrow record created', 'borrow' => $borrow], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    public function show(string $id)
    {
        $borrow = BorrowRecord::with(['item', 'creator'])->findOrFail($id);
        return response()->json($borrow);
    }

    public function processReturn(Request $request, string $id)
    {
        $request->validate([
            'quantity_returned' => 'required|integer|min:1',
        ]);

        $borrow = BorrowRecord::findOrFail($id);

        try {
            $updated = $this->borrowService->processReturn($borrow, $request->quantity_returned);
            return response()->json(['message' => 'Return processed', 'borrow' => $updated]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }
}