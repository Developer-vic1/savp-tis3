<section class="rounded-xl border ga-borde ga-fondo-suave p-5">
    <p class="ui-kicker">Fechas y su respaldo</p><h3 class="ui-title font-bold mt-2">Preparemos el calendario de clases</h3>
    <p class="ui-muted text-xs mt-2">Primero revisa lo publicado por el Ministerio de Educación para {{ $form['anio'] }}. Puedes preparar una propuesta mientras se confirma la disposición.</p>
    @if((int)$form['anio']>=2020 && (int)$form['anio']<=2100)
        <livewire:admin.estudio-calendario :anio="(string)$form['anio']" :key="'estudio-calendario-'.$form['anio']" />
        <x-plegable-institucional class="ga-revision-detalle" icono="ph-calendar-dots"><x-slot:titulo>Ver el inicio habitual como orientación</x-slot:titulo><div class="ga-nota-fuente mt-3"><i class="ph-duotone ph-calendar-check" aria-hidden="true"></i><div><strong>Primer lunes de febrero · {{ $formatDate($fechasSugeridas['inicio_curricular'] ?? null) }}</strong><p>El segundo lunes sería {{ $formatDate($fechasSugeridas['inicio_curricular_alternativo'] ?? null) }}. Esta alternativa necesita una disposición que la respalde.</p><p>El cierre sugerido es {{ $formatDate($fechasSugeridas['cierre_curricular'] ?? null) }}; revisaremos feriados y descanso pedagógico para planificar 200 días efectivos.</p><button type="button" class="ui-btn ui-btn-secondary mt-2" wire:click="aplicarFechasCurriculares">Preparar con el inicio habitual</button></div></div></x-plegable-institucional>
    @else<p class="ui-error mt-3">Selecciona un año válido para consultar sus fuentes.</p>@endif
</section>
