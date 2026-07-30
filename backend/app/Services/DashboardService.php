<?php

namespace App\Services;

use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get aggregated statistics for the dashboard.
     */
    public function getDashboardStats(Request $request): array
    {
        $user = $request->user();
        $query = Incident::query();

        if ($user && $user->role !== 'admin') {
            $query->where('assigned_id', $user->id);
        }

        $total = (clone $query)->count();

        $byStatus = (clone $query)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $byPriority = (clone $query)
            ->select('priority', DB::raw('COUNT(*) as count'))
            ->groupBy('priority')
            ->pluck('count', 'priority');

        $overdue = (clone $query)
            ->where('due_date', '<', now()->toDateString())
            ->where('status', '!=', 'cerrado')
            ->count();

        return [
            'total' => $total,
            'by_status' => [
                'abierto' => $byStatus['abierto'] ?? 0,
                'en_progreso' => $byStatus['en_progreso'] ?? 0,
                'cerrado' => $byStatus['cerrado'] ?? 0,
                'vencido' => $byStatus['vencido'] ?? 0,
            ],
            'by_priority' => [
                'baja' => $byPriority['baja'] ?? 0,
                'media' => $byPriority['media'] ?? 0,
                'alta' => $byPriority['alta'] ?? 0,
                'critica' => $byPriority['critica'] ?? 0,
            ],
            'overdue' => $overdue,
        ];
    }
}
