<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncidentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'status' => $this->status,
            'due_date' => $this->due_date->format('Y-m-d'),
            'is_overdue' => $this->due_date->isPast() && $this->status !== 'cerrado',
            'creator' => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
            ],
            'assignee' => $this->whenLoaded('assignee', function () {
                return $this->assignee ? [
                    'id' => $this->assignee->id,
                    'name' => $this->assignee->name,
                ] : null;
            }),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            'audit_logs' => $this->whenLoaded('auditLogs', function () {
                return $this->auditLogs->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'action' => $log->action,
                        'details' => $log->details,
                        'created_at' => $log->created_at->toISOString(),
                        'user' => $log->user ? [
                            'id' => $log->user->id,
                            'name' => $log->user->name,
                        ] : null,
                    ];
                });
            }),
        ];
    }
}
