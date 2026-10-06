@props(['tipo'=>'grado'])
<section class="ui-card-soft p-5 h-fit">
    <h3 class="ui-title font-bold"><i class="ph-duotone ph-file-magnifying-glass" aria-hidden="true"></i> Qué revisar en el documento</h3>
    <ul class="ui-muted space-y-3 text-sm mt-4">
        <li>Identificación o membrete oficial de la autoridad emisora. El logotipo del Ministerio por sí solo no acredita validez.</li>
        <li>Número, fecha y autoridad competente, coincidentes con los datos que llenas.</li>
        <li>Unidad educativa, {{ $tipo==='paralelo'?'paralelo solicitado':'grado y nivel' }} y gestión autorizada claramente identificados.</li>
        <li>Parte resolutiva con la decisión concreta. Una solicitud enviada o una denegación no aprueba el cambio.</li>
        <li>Firma, sello o mecanismo de verificación que debes comprobar por el canal oficial de la autoridad. La lectura automática no autentica estos elementos.</li>
        <li>Para abrir paralelos en fiscales y convenio: SICH actualizado, informe técnico distrital y resolución administrativa departamental (art. 21, norma 2026).</li>
    </ul>
    <p class="ui-muted text-xs mt-4">PDF legible, sin contraseña, hasta 8 MB y 60 páginas. Si el PDF leído no cumple las coincidencias, el intento y su evidencia se guardan en bitácora.</p>
    <a class="ui-btn ui-btn-secondary mt-4" href="{{ \App\Support\Academico\RespaldoCursoInstitucional::FUENTE }}" target="_blank" rel="noopener noreferrer"><i class="ph-duotone ph-book-open-text" aria-hidden="true"></i>Consultar norma 2026</a>
</section>