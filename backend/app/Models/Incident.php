<?php

namespace App\Models;

use Database\Factories\IncidentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'title',
    'description',
    'priority',
    'status',
    'user_id',
    'assigned_id',
    'due_date',
])]
class Incident extends Model
{
    
    use HasFactory;

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class)->latest();
    }

    // Refactored: Audit logs are now handled in the IncidentService to decouple from HTTP session
}
