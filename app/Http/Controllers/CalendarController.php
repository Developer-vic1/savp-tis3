<?php

namespace App\Http\Controllers;

use App\Services\CalendarService;
use App\Services\RoleDashboardResolver;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function __invoke(Request $request, CalendarService $calendar)
    {
        $filters = $request->validate(['from' => 'nullable|date', 'to' => 'nullable|date|after_or_equal:from', 'search' => 'nullable|string|max:100']);
        $rows = $calendar->query($request->user())->when($filters['from'] ?? null, fn ($q, $from) => $q->whereDate('fec_lim_tar', '>=', $from))
            ->when($filters['to'] ?? null, fn ($q, $to) => $q->whereDate('fec_lim_tar', '<=', $to))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('tit_tar', 'like', '%'.$search.'%'))
            ->orderBy('fec_lim_tar')->orderBy('cod_tar')->paginate(20)->withQueryString();
        $actor = app(RoleDashboardResolver::class)->roleFor($request->user());
        $root = app(RoleDashboardResolver::class)->routeFor($request->user());
        $events = $calendar->institutionalQuery($request->user());
        $events = $events?->when($filters['from'] ?? null, fn ($q, $from) => $q->whereDate('ffi_cae', '>=', $from))
            ->when($filters['to'] ?? null, fn ($q, $to) => $q->whereDate('fii_cae', '<=', $to))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('nom_cae', 'like', '%'.$search.'%'))
            ->orderBy('fii_cae')->orderBy('cod_cae')->paginate(15, ['*'], 'eventsPage')->withQueryString();

        return view('workspaces.calendario', compact('rows', 'events', 'filters', 'actor', 'root'));
    }
}
