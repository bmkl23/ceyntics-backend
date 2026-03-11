<?php

namespace App\Services;

use App\Models\Cupboard;
use App\Models\Place;

class CupboardService
{
    // ── Cupboard Methods ────────────────────────────

    public function getAllCupboards(): object
    {
        return Cupboard::with('places')->get();
    }

    public function createCupboard(array $data, int $userId): Cupboard
    {
        $cupboard = Cupboard::create([
            'name'        => $data['name'],
            'location'    => $data['location'] ?? null,
            'description' => $data['description'] ?? null,
            'created_by'  => $userId,
        ]);

        AuditLogService::log(
            'cupboard.created', 'Cupboard', $cupboard->id,
            [], $cupboard->toArray(),
            "Cupboard {$cupboard->name} created"
        );

        return $cupboard;
    }

    public function updateCupboard(Cupboard $cupboard, array $data): Cupboard
    {
        $oldValues = $cupboard->only(['name', 'location', 'description']);
        $cupboard->update(array_filter([
            'name'        => $data['name']        ?? null,
            'location'    => $data['location']    ?? null,
            'description' => $data['description'] ?? null,
        ], fn($v) => !is_null($v)));

        AuditLogService::log(
            'cupboard.updated', 'Cupboard', $cupboard->id,
            $oldValues, $cupboard->only(['name', 'location', 'description']),
            "Cupboard {$cupboard->name} updated"
        );

        return $cupboard->fresh();
    }

    public function deleteCupboard(Cupboard $cupboard): void
    {
        AuditLogService::log(
            'cupboard.deleted', 'Cupboard', $cupboard->id,
            $cupboard->toArray(), [],
            "Cupboard {$cupboard->name} deleted"
        );

        $cupboard->delete();
    }

    // ── Place Methods ───────────────────────────────

    public function getAllPlaces(): object
    {
        return Place::with('cupboard')->get();
    }

    public function createPlace(array $data, int $userId): Place
    {
        $place = Place::create([
            'cupboard_id' => $data['cupboard_id'],
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'created_by'  => $userId,
        ]);

        AuditLogService::log(
            'place.created', 'Place', $place->id,
            [], $place->toArray(),
            "Place {$place->name} created"
        );

        return $place->load('cupboard');
    }

    public function updatePlace(Place $place, array $data): Place
    {
        $oldValues = $place->only(['cupboard_id', 'name', 'description']);
        $place->update(array_filter([
            'cupboard_id' => $data['cupboard_id'] ?? null,
            'name'        => $data['name']        ?? null,
            'description' => $data['description'] ?? null,
        ], fn($v) => !is_null($v)));

        AuditLogService::log(
            'place.updated', 'Place', $place->id,
            $oldValues, $place->only(['cupboard_id', 'name', 'description']),
            "Place {$place->name} updated"
        );

        return $place->fresh();
    }

    public function deletePlace(Place $place): void
    {
        AuditLogService::log(
            'place.deleted', 'Place', $place->id,
            $place->toArray(), [],
            "Place {$place->name} deleted"
        );

        $place->delete();
    }
}