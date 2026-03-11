<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cupboard;
use App\Services\CupboardService;
use Illuminate\Http\Request;

class CupboardController extends Controller
{
    public function __construct(private CupboardService $cupboardService) {}

    public function index()
    {
        return response()->json($this->cupboardService->getAllCupboards());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'location'    => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $cupboard = $this->cupboardService->createCupboard($request->all(), auth()->id());
        return response()->json(['message' => 'Cupboard created', 'cupboard' => $cupboard], 201);
    }

    public function show(string $id)
    {
        $cupboard = Cupboard::with('places')->findOrFail($id);
        return response()->json($cupboard);
    }

    public function update(Request $request, string $id)
    {
        $cupboard = Cupboard::findOrFail($id);
        $request->validate([
            'name'        => 'sometimes|string|max:255',
            'location'    => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $updated = $this->cupboardService->updateCupboard($cupboard, $request->all());
        return response()->json(['message' => 'Cupboard updated', 'cupboard' => $updated]);
    }

    public function destroy(string $id)
    {
        $cupboard = Cupboard::findOrFail($id);
        $this->cupboardService->deleteCupboard($cupboard);
        return response()->json(['message' => 'Cupboard deleted']);
    }
}