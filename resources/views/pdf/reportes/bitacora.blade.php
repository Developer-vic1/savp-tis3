{!! app(\App\Services\Reportes\GeneradorMpdfService::class)->htmlHeader(get_defined_vars()) !!}

<div class="pdf-title" style="color:#0284c7; border-color:#bae6fd;">📝 Reporte de Bitácora de Actividad</div>
<div class="pdf-kicker">Auditoría institucional del sistema · {{ now()->format('d/m/Y H:i') }}</div>

@php
$registros = $bitacora ?? collect([]);
$total = $registros->count();
@endphp

<table style="width:100%; border:0; border-collapse:collapse; margin: 10px 0;">
    <tr>
        <td style="width:33%; padding:4px;">
            <div class="kpi-card" style="border-color:#bae6fd; background:#f0f9ff;">
                <div class="kpi-label">Total Registros</div>
                <div class="kpi-value kpi-blue">{{ $total }}</div>
            </div>
        </td>
        <td style="width:33%; padding:4px;">
            <div class="kpi-card" style="border-color:#a7f3d0; background:#ecfdf5;">
                <div class="kpi-label">Exitosos</div>
                <div class="kpi-value kpi-green">{{ $registros->whereNotIn('res_bit', ['FALLIDO', 'BLOQUEADO'])->count() }}</div>
            </div>
        </td>
        <td style="width:33%; padding:4px;">
            <div class="kpi-card" style="border-color:#fecdd3; background:#fff1f2;">
                <div class="kpi-label">Fallidos / Bloqueados</div>
                <div class="kpi-value kpi-red">{{ $registros->whereIn('res_bit', ['FALLIDO', 'BLOQUEADO'])->count() }}</div>
            </div>
        </td>
    </tr>
</table>

<div class="pdf-section">Registro de Actividad del Sistema</div>

@if($registros->count())
<table class="pdf-table pdf-table-adm">
    <thead>
        <tr>
            <th>Fecha / Hora</th>
            <th>Usuario</th>
            <th>Rol</th>
            <th>Módulo</th>
            <th>Acción</th>
            <th>Registro</th>
            <th style="text-align:center;">Estado</th>
            <th>IP</th>
        </tr>
    </thead>
    <tbody>
        @foreach($registros as $item)
        @php
        $usuario = $item->usuario;
        $persona = $usuario?->persona;
        $nombreUsu = trim(($persona?->nom_per ?? '') . ' ' . ($persona?->ape_pat_per ?? '')) ?: ($usuario?->email ?? '—');
        $esFallido = in_array($item->res_bit, ['FALLIDO', 'BLOQUEADO']);
        @endphp
        <tr>
            <td style="font-size:7.5pt; white-space:nowrap;">
                {{ $item->fec_bit?->format('d/m/Y') }}<br>
                <span style="color:#64748b;">{{ $item->fec_bit?->format('H:i:s') }}</span>
            </td>
            <td style="font-size:7.5pt;"><strong>{{ $nombreUsu }}</strong></td>
            <td style="font-size:7pt; color:#7c3aed;">{{ $item->rol_bit ?? '—' }}</td>
            <td style="font-size:7.5pt; color:#0284c7; font-weight:bold;">{{ $item->mod_bit ?? 'General' }}</td>
            <td style="font-size:7.5pt;">{{ $item->acc_bit }}</td>
            <td style="font-size:7pt; color:#64748b;">{{ $item->nom_reg_bit ?? $item->reg_bit ?? '—' }}</td>
            <td style="text-align:center;">
                @if($esFallido)
                    <span class="badge badge-red">{{ $item->res_bit }}</span>
                @else
                    <span class="badge badge-green">{{ $item->res_bit ?? 'OK' }}</span>
                @endif
            </td>
            <td style="font-size:7pt; color:#64748b;">{{ $item->ip_bit ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="pdf-empty">No se encontraron registros de actividad en la bitácora para los filtros seleccionados.</div>
@endif

<div class="alert alert-info" style="margin-top: 10px;">
    <strong>Nota de auditoría:</strong> Este reporte incluye hasta los últimos 100 registros de actividad del sistema.
    Para auditorías completas, consulte directamente la bitácora del sistema SAVP-TIS3.
</div>

{!! app(\App\Services\Reportes\GeneradorMpdfService::class)->htmlFooter($sistema ?? 'SAVP-TIS3') !!}
