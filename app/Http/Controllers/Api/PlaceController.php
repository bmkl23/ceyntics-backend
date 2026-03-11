<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Place;
use App\Services\CupboardService;
use Illuminate\Http\Request;

class PlaceController extends Controller
{
    public function __construct(private CupboardService $cupboardService) {}

    public function index()
    {
        return response()->json($this->cupboardService->getAllPlaces());
    }

    public function store(Request $request)
    {
        $request->validate([
            'cupboard_id' => 'required|exists:cupboards,id',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $place = $this->cupboardService->createPlace($request->all(), auth()->id());
        return response()->json(['message' => 'Place created', 'place' => $place], 201);
    }

    public function show(string $id)
    {
        $place = Place::with('cupboard')->findOrFail($id);
        return response()->json($place);
    }

    public function update(Request $request, string $id)
    {
        $place = Place::findOrFail($id);
        $request->validate([
            'cupboard_id' => 'sometimes|exists:cupboards,id',
            'name'        => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]);

        $updated = $this->cupboardService->updatePlace($place, $request->all());
        return response()->json(['message' => 'Place updated', 'place' => $updated]);
    }

    public function destroy(string $id)
    {
        $place = Place::findOrFail($id);
        $this->cupboardService->deletePlace($place);
        return response()->json(['message' => 'Place deleted']);
    }
}