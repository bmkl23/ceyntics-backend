<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class InventoryItemController extends Controller
{
    public function __construct(private InventoryService $inventoryService) {}

    public function index(Request $request)
    {
        $items = $this->inventoryService->getAllItems($request->only([
            'status', 'place_id', 'category', 'search'
        ]));
        return response()->json($items);
    }

  public function store(Request $request)
{

    // Debug — see what's coming in
    \Log::info('Request data:', $request->all());
    \Log::info('Request method:', [$request->method()]);
    \Log::info('Content type:', [$request->header('Content-Type')]);
    
    $request->validate([
        'name'          => 'required|string|max:255',
        'code'          => 'required|string|unique:inventory_items,code',
        'quantity'      => 'required|integer|min:0',
        'place_id'      => 'required|exists:places,id',
        'serial_number' => 'nullable|string',
        'description'   => 'nullable|string',
        'category'      => 'nullable|string',
        'min_quantity'  => 'nullable|integer|min:0',
        'image'         => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    try {
        $data = $request->all();
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image');
        }
        $item = $this->inventoryService->createItem($data, auth()->id());
        return response()->json(['message' => 'Item created', 'item' => $item], 201);
    } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 400);
    }
}

    public function show(string $id)
    {
        $item = InventoryItem::with(['place.cupboard', 'creator', 'borrowRecords'])
            ->findOrFail($id);
        return response()->json($item);
    }

    public function update(Request $request, string $id)
    {
        $item = InventoryItem::findOrFail($id);
        $request->validate([
            'name'          => 'sometimes|string|max:255',
            'code'          => 'sometimes|string|unique:inventory_items,code,' . $id,
            'place_id'      => 'sometimes|exists:places,id',
            'description'   => 'nullable|string',
            'category'      => 'nullable|string',
            'serial_number' => 'nullable|string',
            'min_quantity'  => 'nullable|integer|min:0',
            'image'         => 'nullable|image|max:2048',
        ]);

        try {
            $data = $request->all();
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image');
            }
            $updated = $this->inventoryService->updateItem($item, $data);
            return response()->json(['message' => 'Item updated', 'item' => $updated]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function updateQuantity(Request $request, string $id)
    {
        $request->validate([
            'action' => 'required|in:increment,decrement',
            'amount' => 'required|integer|min:1',
        ]);

        $item = InventoryItem::findOrFail($id);

        try {
            $updated = $this->inventoryService->updateQuantity(
                $item,
                $request->action,
                $request->amount
            );
            return response()->json(['message' => 'Quantity updated', 'item' => $updated]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    public function updateStatus(Request $request, string $id)
    {
        $request->validate([
            'status' => 'required|in:in_store,borrowed,damaged,missing',
        ]);

        $item = InventoryItem::findOrFail($id);

        try {
            $updated = $this->inventoryService->updateStatus($item, $request->status);
            return response()->json(['message' => 'Status updated', 'item' => $updated]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function destroy(string $id)
    {
        $item = InventoryItem::findOrFail($id);
        $this->inventoryService->deleteItem($item);
        return response()->json(['message' => 'Item deleted']);
    }
}