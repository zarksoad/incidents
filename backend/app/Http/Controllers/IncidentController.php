<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIncidentRequest;
use App\Http\Requests\UpdateIncidentRequest;
use App\Http\Resources\IncidentResource;
use App\Models\Incident;
use App\Services\IncidentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IncidentController extends Controller
{
    private IncidentService $incidentService;

    public function __construct(IncidentService $incidentService)
    {
        $this->incidentService = $incidentService;
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $incidents = $this->incidentService->getPaginatedIncidents($request);

        return IncidentResource::collection($incidents);
    }

    public function store(StoreIncidentRequest $request): JsonResponse
    {
        $incident = $this->incidentService->createIncident(
            $request->validated(),
            $request->user()->id
        );

        return response()->json(new IncidentResource($incident), 201);
    }

    public function show(Incident $incident): IncidentResource
    {
        $incident->load(['creator:id,name', 'assignee:id,name', 'auditLogs.user:id,name']);

        return new IncidentResource($incident);
    }

    public function update(UpdateIncidentRequest $request, Incident $incident): IncidentResource
    {
        $incident = $this->incidentService->updateIncident($incident, $request->validated());

        return new IncidentResource($incident);
    }

    public function destroy(Incident $incident): JsonResponse|\Illuminate\Http\Response
    {
        $user = request()->user();
        
        if ($user && $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $this->incidentService->deleteIncident($incident);

        return response()->noContent();
    }

    public function export(Request $request): StreamedResponse
    {
        $incidents = $this->incidentService->getIncidentsForExport($request);

        $response = new StreamedResponse(function () use ($incidents) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Titulo', 'Prioridad', 'Estado', 'Creador', 'Asignado', 'Fecha Vencimiento', 'Creado El']);

            foreach ($incidents as $incident) {
                fputcsv($handle, [
                    $incident->id,
                    $incident->title,
                    $incident->priority,
                    $incident->status,
                    $incident->creator->name ?? '',
                    $incident->assignee->name ?? '',
                    $incident->due_date ? $incident->due_date->format('Y-m-d') : '',
                    $incident->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="incidentes.csv"');

        return $response;
    }
}
