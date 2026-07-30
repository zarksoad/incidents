<?php

namespace App\Services;

use App\Models\Incident;
use App\Events\IncidentSaved;
use App\Events\IncidentDeleted;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class IncidentService
{
    /**
     * Get paginated incidents based on request filters.
     */
    public function getPaginatedIncidents(Request $request): LengthAwarePaginator
    {
        $query = $this->buildFilteredQuery($request);
        $this->applySorting($query, $request);

        $perPage = (int) $request->input('per_page', 15);
        $perPage = min($perPage, 100);

        return $query->paginate($perPage);
    }

    /**
     * Get all incidents for export based on request filters.
     */
    public function getIncidentsForExport(Request $request): Collection
    {
        return $this->buildFilteredQuery($request)->get();
    }

    /**
     * Build the eloquent query with filters and permissions applied.
     */
    private function buildFilteredQuery(Request $request): Builder
    {
        $query = Incident::with(['creator:id,name', 'assignee:id,name']);
        $user = $request->user();

        if ($user && $user->role !== 'admin') {
            $query->where('assigned_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('assigned_id')) {
            $query->where('assigned_id', $request->assigned_id);
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('due_date', [$request->from, $request->to]);
        } elseif ($request->filled('from')) {
            $query->where('due_date', '>=', $request->from);
        } elseif ($request->filled('to')) {
            $query->where('due_date', '<=', $request->to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function (Builder $q) use ($search) {
                $q->whereRaw('MATCH(title, description) AGAINST(? IN BOOLEAN MODE)', [$search . '*'])
                  ->orWhere('title', 'LIKE', "%{$search}%");
            });
        }

        return $query;
    }

    /**
     * Apply sorting to the query.
     */
    private function applySorting(Builder $query, Request $request): void
    {
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $allowedSorts = ['title', 'priority', 'status', 'due_date', 'created_at'];

        if (in_array($sortBy, $allowedSorts, true)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        }
    }

    /**
     * Create a new incident.
     */
    public function createIncident(array $data, int $userId): Incident
    {
        $data['user_id'] = $userId;
        $incident = Incident::create($data);

        $incident->load(['creator:id,name', 'assignee:id,name']);
        event(new IncidentSaved($incident));

        return $incident;
    }

    /**
     * Update an existing incident.
     */
    public function updateIncident(Incident $incident, array $data): Incident
    {
        $incident->update($data);
        $incident->load(['creator:id,name', 'assignee:id,name']);

        event(new IncidentSaved($incident));

        return $incident;
    }

    /**
     * Delete an incident.
     */
    public function deleteIncident(Incident $incident): void
    {
        $incidentId = $incident->id;
        $incident->delete();

        event(new IncidentDeleted($incidentId));
    }
}
