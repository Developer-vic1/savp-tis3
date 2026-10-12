@php
    $explicacionVersion = data_get($instrument?->data ?? [], 'instrument_version');
    $explicacionEscala = collect(data_get($instrument?->data ?? [], 'response_scale', []));
    $explicacionFormatoActual = $explicacionVersion === '2.0-es-2025'
        && collect(data_get($instrument?->data ?? [], 'items', []))->count() === 30
        && $explicacionEscala->pluck('value')->sort()->values()->all() === [1, 2, 3, 4, 5];
@endphp
<section aria-labelledby="sentido-cuestionario" class="space-y-4">
    <div><p class="intereses-etiqueta">Responde con sentido</p><h2 id="sentido-cuestionario" class="mt-2">¿Qué descubrimos con tus respuestas?</h2><p class="intereses-texto mt-2">Qué actividades te gustaría realizar. Esa información te ayuda a investigar opciones de estudio con tus propios motivos.</p></div>
    <x-plegable-institucional etiqueta="Cómo responder sin buscar una respuesta correcta" descripcion="Imagina la actividad y piensa en tu interés, no en tu nota." icono="ph-lightbulb" :abierto="true">
        <div class="space-y-3 intereses-texto"><p>Lee cada actividad e imagina hacerla. Elige cuánto te gustaría realizarla, aunque todavía no sepas cómo hacerlo.</p><p>Si una actividad no te interesa, puedes expresarlo. Si no sabes cuánto te atrae, usa la opción de la escala que refleje esa duda. No respondas para obtener una carrera concreta ni para agradar a otra persona.</p><p>Los bloques ayudan a avanzar y revisar. El progreso cuenta respuestas; no premia que elijas un valor alto.</p></div>
        @if($explicacionEscala->isNotEmpty())
            <ol class="mt-4 space-y-2" aria-label="Escala del cuestionario disponible">@foreach($explicacionEscala as $opcion)<li class="flex items-start gap-3"><span class="ui-badge shrink-0">{{ data_get($opcion, 'value') }}</span><span>{{ data_get($opcion, 'label') }}</span></li>@endforeach</ol>
        @endif
    </x-plegable-institucional>
    <x-plegable-institucional etiqueta="Seis formas de interesarte por una actividad" descripcion="Puedes disfrutar actividades de varias áreas; ninguna vale más que otra." icono="ph-compass">
        <div class="grid gap-4 sm:grid-cols-2">@foreach(\App\Support\Orientacion\ExperienciaIntereses::AREAS as $codigo => $area)<article class="ui-card-soft p-4"><div class="flex items-center gap-2"><i class="ph-duotone ph-{{ $area['icono'] }}" aria-hidden="true"></i><h3 class="font-bold">{{ $codigo }} · {{ $area['nombre'] }}</h3></div><p class="intereses-texto mt-2">{{ $area['actividad'] }}</p></article>@endforeach</div>
        <p class="intereses-texto mt-4">RIASEC reúne las iniciales de estas seis áreas. Sirven para describir intereses, no para etiquetar tu personalidad ni medir qué tan capaz eres.</p>
    </x-plegable-institucional>
    <x-plegable-institucional etiqueta="Cómo se obtiene el puntaje y el código" descripcion="Conoce la cuenta y entiende qué ocurre cuando hay un empate." icono="ph-calculator">
        @if($explicacionFormatoActual)
            <div class="space-y-3 intereses-texto"><p>Cada área reúne cinco actividades distribuidas a lo largo del cuestionario. El servidor verifica que hayas respondido una vez cada una de las 30 actividades.</p><p>Para calcular, a cada respuesta de la escala 1–5 se le resta 1. Así se convierte en 0–4. Después se suman las cinco respuestas de cada área: su puntaje va de 0 a 20.</p></div>
            <div class="ui-card-soft mt-4 p-4"><h3 class="font-bold">Ejemplo didáctico, no tu resultado</h3><p class="intereses-texto mt-2">Imagina cinco respuestas de una misma área: <strong>4, 5, 3, 2 y 3</strong>. Se convierten en <strong>3, 4, 2, 1 y 2</strong>. La suma es <strong>12 de 20</strong>. Este ejemplo artificial solo explica la cuenta; no representa a ningún estudiante.</p></div>
            <p class="intereses-texto mt-4">El código Holland muestra las tres letras con mayor puntaje. Si dos áreas empatan, tienen el mismo interés declarado: el orden estable de las letras permite presentar el código, pero no convierte una en favorita. Si las seis empatan, ninguna destaca.</p>
            <p class="intereses-texto mt-3">Un cero puede ser un resultado válido. La falta de respuestas o un resultado de otra escala no se convierte en cero.</p>
        @else
            <p class="intereses-texto">La explicación del cálculo necesita el cuestionario vigente. Cuando esté disponible, podrás revisar su escala y la versión del instrumento antes de responder.</p>
        @endif
    </x-plegable-institucional>
    <x-plegable-institucional etiqueta="Qué datos se usan después del cuestionario" descripcion="Tus notas aportan contexto al estudio; no cambian tus intereses RIASEC." icono="ph-shield-check">
        <div class="space-y-3 intereses-texto"><p><strong>Para tu perfil RIASEC:</strong> se utilizan las 30 respuestas y la versión del instrumento. Tus calificaciones no suman ni restan puntos a ese perfil.</p><p><strong>Al generar el estudio en Mi análisis:</strong> se revisan los registros académicos disponibles con su período, la historia previa y la formación técnica cuando corresponda. Asistencia y actividades disponibles añaden contexto; una ausencia de datos no demuestra bajo rendimiento.</p><p><strong>Para explorar carreras:</strong> se consultan referencias ocupacionales y fuentes universitarias documentadas. Mostrar una referencia no demuestra compatibilidad contigo ni constituye un ranking de carreras.</p><p>El estudio apoya una decisión que sigues tomando tú. No calcula admisión, inteligencia, aptitud ni probabilidad de éxito. La validez del instrumento para estudiantes bolivianos no está demostrada.</p></div>
        <a class="ui-btn-secondary mt-4 inline-flex" href="https://www.onetcenter.org/IP.html" target="_blank" rel="noopener noreferrer">Conocer O*NET Interest Profiler<i class="ph-duotone ph-arrow-square-out" aria-hidden="true"></i><span class="sr-only"> (abre otra pestaña)</span></a>
    </x-plegable-institucional>
</section>
