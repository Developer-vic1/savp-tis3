<x-plegable-institucional class="my-4" etiqueta="Ventanas de este rol" icono="ph-browser" :abierto="true" descripcion="Elige una ventana para revisar sus permisos y requisitos de entrada.">
    <x-slot:contador><span x-text="ventanasRol.filter(v=>estadoVentana(v)).length"></span> habilitadas · <span x-text="ventanasRol.length"></span> disponibles</x-slot:contador>
    <div class="roles-ventanas"><button type="button" class="roles-ventana" :aria-pressed="!ventanaElegida" x-on:click="limpiar()"><i class="ph-duotone ph-stack" aria-hidden="true"></i>Todas las tareas</button>
        <template x-for="v in ventanasRol" :key="v.id"><button type="button" class="roles-ventana" :aria-pressed="ventanaElegida===v.id" x-on:click="limpiar();ventanaElegida=v.id"><i class="ph-duotone" :class="v.icon" aria-hidden="true"></i><span x-text="v.label"></span><i class="ph-duotone" :class="estadoVentana(v)?'ph-check-circle':'ph-lock-simple'" :aria-label="estadoVentana(v)?'Entrada habilitada':'Faltan permisos de entrada'"></i></button></template>
    </div>
    <template x-for="v in ventanasRol.filter(v=>v.id===ventanaElegida)" :key="v.id"><div class="roles-ventana-detalle">
        <strong x-text="v.label"></strong><p x-text="v.limite"></p><p><span x-text="estadoVentana(v)?'Requisitos de entrada completos':'Entrada pendiente: faltan permisos'"></span> · Las operaciones de edición se validan por separado.</p>
        <div class="roles-requisitos"><template x-for="n in v.requisitos" :key="n"><span><i class="ph-duotone" :class="alternativas(n,v.actor).some(a=>seleccion.includes(a))?'ph-check-circle':'ph-lock-simple'" aria-hidden="true"></i><span x-text="permisos.find(p=>p.name===n)?.label??'Consulta de roles y permisos'"></span><code x-text="n"></code></span></template></div>
        <div class="roles-ruta"><code x-text="v.url"></code><template x-if="v.puede_ir"><a class="ui-btn ui-btn-secondary" :href="v.url" target="_blank" rel="noopener"><i class="ph-duotone ph-arrow-square-out" aria-hidden="true"></i>Ir a la ventana</a></template><span x-show="!v.puede_ir" class="ui-muted text-xs">Disponible para el actor <span x-text="v.actor"></span>; se conserva tu sesión actual.</span></div>
    </div></template>
    <p x-show="!ventanasRol.length" class="ui-muted text-xs">Este rol complementa tareas. Las ventanas dependen del actor principal de la cuenta.</p>
</x-plegable-institucional>
