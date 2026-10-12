<div class="honor-panel" wire:key="cuadro-honor-{{ $honor['gestion'] }}">
    <section class="ui-card honor-portada" aria-labelledby="honor-titulo">
        <div class="honor-portada-icono" aria-hidden="true"><i class="ph-duotone ph-trophy"></i></div>
        <div><p class="ui-kicker">Reconocer el esfuerzo · preparar la elección</p><h2 id="honor-titulo" class="ui-title">Cuadro de honor</h2><p class="ui-muted">Los mejores promedios de la gestión seleccionada, por grado y de la promoción. Revisa la evidencia antes de proponer reconocimientos o abanderados.</p></div>
        <button type="button" class="ui-btn-secondary" wire:click="descargarHonor" wire:loading.attr="disabled" wire:target="descargarHonor"><i class="ph-duotone ph-download-simple" aria-hidden="true"></i> Exportar propuestas para el acto</button>
    </section>

    <div class="ui-alert-info" role="note"><strong>Ámbito del cuadro de honor.</strong> Considera todos los grados, paralelos y períodos de la gestión seleccionada. La búsqueda y los filtros de seguimiento no cambian estas candidaturas. Un promedio parcial no acredita el promedio anual oficial.</div>

    <section class="ui-card honor-seccion" aria-labelledby="honor-promocion-titulo">
        <div class="honor-encabezado"><div><p class="ui-kicker">Bachiller destacado</p><h3 id="honor-promocion-titulo" class="ui-title">La promoción, con su propia distinción</h3><p class="ui-help">Un hombre y una mujer de sexto de secundaria, según el género registrado y el mayor promedio observado. Los empates permanecen visibles para revisión.</p></div><span class="ui-badge">{{ $honor['promocion']['nombre'] ?? 'Sexto de secundaria' }}</span></div>
        <div class="honor-promocion-grid">
            @forelse($honor['promocion']['estudiantes'] as $e)
                <div class="honor-candidatura"><p class="honor-categoria"><i class="ph-duotone ph-graduation-cap" aria-hidden="true"></i> Candidatura · {{ $e->genero }}</p><x-ficha-honor :estudiante="$e" contexto="honor-promocion" /></div>
            @empty
                <div class="honor-vacio"><i class="ph-duotone ph-graduation-cap" aria-hidden="true"></i><h4>Aún no hay candidaturas para mostrar</h4><p>Se necesitan inscripciones de sexto de secundaria con notas y género registrado en esta gestión. Revisa los registros académicos existentes.</p></div>
            @endforelse
        </div>
        <p class="ui-help mt-4">{{ (int) $honor['promocion']['total'] }} estudiantes de la promoción con notas. La referencia normativa de 2023 contempla Bs 1.000 para bachiller destacado; la vigencia y los requisitos actuales deben revisarse antes de la selección. <a class="honor-enlace" href="https://www.minedu.gob.bo/files/documentos-normativos/resoluciones-ministeriales/2023/1062.pdf" target="_blank" rel="noopener noreferrer">Consultar RM 1062/2023 <i class="ph-duotone ph-arrow-up-right" aria-hidden="true"></i><span class="sr-only"> (abre en otra pestaña)</span></a></p>
        <p class="ui-help mt-3">Para la revisión del incentivo, verifica el cierre de sexto, el historial completo de áreas y el RUDE único. La RM 1062/2023 indica comparar quinto y luego años anteriores cuando el promedio anual coincide a dos decimales. Este panel no aplica un desempate por código ni confirma beneficiarios.</p>
    </section>

    <section class="honor-grados" aria-labelledby="honor-grados-titulo">
        <div class="honor-encabezado"><div><p class="ui-kicker">Actos cívicos y reconocimientos</p><h3 id="honor-grados-titulo" class="ui-title">Los tres mejores de cada grado</h3><p class="ui-help">Incluye la promoción. Cada grado reúne sus paralelos y muestra hasta el tercer lugar, conservando los empates.</p></div><span class="ui-badge">{{ $honor['grados']->count() }} grados con notas</span></div>
        @forelse($honor['grados'] as $grado)
            <section class="ui-card honor-grado" wire:key="honor-grado-{{ $honor['gestion'] }}-{{ $grado['codigo'] }}" aria-label="Cuadro de honor de {{ $grado['nombre'] }}">
                <div class="honor-grado-cabecera"><h4><i class="ph-duotone ph-flag-banner" aria-hidden="true"></i><span>{{ $grado['nombre'] }}</span></h4><span class="ui-help">{{ (int) $grado['total'] }} estudiantes con notas · {{ $grado['estudiantes']->count() }} propuestas</span></div>
                <div class="honor-podio">@foreach($grado['estudiantes'] as $e)<x-ficha-honor :estudiante="$e" :contexto="'honor-grado-'.$grado['codigo']" />@endforeach</div>
            </section>
        @empty
            <div class="ui-card honor-vacio"><i class="ph-duotone ph-notebook" aria-hidden="true"></i><h4>Esta gestión aún no tiene promedios para comparar</h4><p>Selecciona otra gestión o revisa el registro de calificaciones. El cuadro se construye con las notas disponibles.</p></div>
        @endforelse
    </section>

    <x-plegable-institucional etiqueta="Memoria del cuadro de honor" descripcion="Los dos mejores promedios globales de cada gestión, con sus empates" icono="ph-clock-counter-clockwise" :abierto="true">
        <p class="ui-help mb-5">Vista histórica recalculada con las calificaciones disponibles de cada gestión. No es un registro de ganadores acreditados; cada año puede tener distinta cobertura de notas.</p>
        <div class="honor-historial">
            @forelse($honor['historial'] as $anio)
                <section class="honor-anio" wire:key="honor-historial-{{ $anio['gestion'] }}">
                    <div class="honor-anio-cabecera"><div><p class="ui-kicker">Gestión</p><h3>{{ $anio['anio'] }}</h3></div><button type="button" class="ui-btn-secondary" wire:click="cambiarGestionHonor('{{ $anio['gestion'] }}')" wire:loading.attr="disabled" wire:target="cambiarGestionHonor" aria-label="Abrir cuadro de honor de la gestión {{ $anio['anio'] }}">Ver esta gestión<i class="ph-duotone ph-arrow-right" aria-hidden="true"></i></button></div>
                    <div class="honor-historial-fichas">@foreach($anio['estudiantes'] as $e)<x-ficha-honor :estudiante="$e" :accion="false" :compacto="true" :contexto="'honor-historial-'.$anio['gestion']" />@endforeach</div>
                </section>
            @empty
                <div class="honor-vacio"><i class="ph-duotone ph-calendar-blank" aria-hidden="true"></i><h4>Sin gestiones con notas para el historial</h4><p>Los cuadros anteriores aparecerán cuando existan calificaciones registradas.</p></div>
            @endforelse
        </div>
    </x-plegable-institucional>

    <x-plegable-institucional etiqueta="Cómo revisar estas propuestas" descripcion="Promedios, empates y evidencias antes de elegir" icono="ph-list-checks">
        <div class="honor-criterios">
            <article><i class="ph-duotone ph-calculator" aria-hidden="true"></i><div><h3>Promedio observado</h3><p>Se calcula con las notas disponibles de cada inscripción en esta gestión. Distinta cantidad de notas o materias puede afectar la comparación; revisa la trayectoria y el cierre académico.</p></div></article>
            <article><i class="ph-duotone ph-equals" aria-hidden="true"></i><div><h3>Empates conservados</h3><p>Un empate en el corte puede producir más de tres propuestas por grado o más de dos en el historial. El sistema no desempata por código ni asigna automáticamente un abanderado.</p></div></article>
            <article><i class="ph-duotone ph-shield-check" aria-hidden="true"></i><div><h3>La evidencia acompaña la elección</h3><p>«Provisional» indica que falta cierre registrado. «Cierre registrado · requiere revisión» solicita verificar el acta y la cobertura. «Validación sintética» identifica notas de prueba que no acreditan un reconocimiento.</p></div></article>
            <article><i class="ph-duotone ph-compass" aria-hidden="true"></i><div><h3>Orientación cuando existe</h3><p>Las opciones de carrera aparecen únicamente cuando hay un análisis válido guardado y permiso para consultarlo. El promedio y una recomendación vocacional no deciden la carrera del estudiante.</p></div></article>
        </div>
    </x-plegable-institucional>
</div>
