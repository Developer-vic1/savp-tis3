<div class="roles-detalle-capa" x-show="permisoDetalle" x-cloak x-on:keydown.escape.window="permisoDetalle=null" x-on:click.self="permisoDetalle=null" x-transition.opacity>
    <section class="ui-card roles-detalle-panel" x-show="permisoDetalle" x-transition x-trap.inert.noscroll="!!permisoDetalle" role="dialog" aria-modal="true" aria-labelledby="detalle-permiso-titulo">
        <header class="roles-titulo"><i class="ph-duotone ph-shield-check" aria-hidden="true"></i><div><p class="roles-kicker">Qué permite y dónde se utiliza</p><h2 id="detalle-permiso-titulo" x-text="permisoDetalle?.label"></h2></div><button type="button" class="ui-btn ui-btn-secondary" x-on:click="permisoDetalle=null" aria-label="Cerrar detalle"><i class="ph-duotone ph-x" aria-hidden="true"></i></button></header>
        <template x-if="permisoDetalle"><div class="roles-detalle-contenido">
            <p x-text="permisoDetalle.detalle"></p><p class="ui-muted text-sm"><span x-text="permisoDetalle.scope_label"></span> · <span x-text="permisoDetalle.capacidad==='edicion'?'Operación de modificación':permisoDetalle.name.includes('.')?'Consulta u operación específica':'Entrada al módulo; las operaciones tienen reglas adicionales'"></span></p>
            <p x-show="permisoDetalle.critical" class="roles-aviso">Acceso protegido. Su asignación requiere la autoridad correspondiente.</p>
            <x-plegable-institucional compacto etiqueta="Registro técnico de la base de datos" icono="ph-database" :abierto="true"><div class="roles-registro-tecnico"><code x-text="permisoDetalle.name"></code><span>Registro <span x-text="permisoDetalle.id"></span> · Guard <span x-text="permisoDetalle.guard"></span></span><p x-text="permisoDetalle.roles.length?'Roles: '+permisoDetalle.roles.join(', '):'No está asignado a ningún rol.'"></p></div></x-plegable-institucional>
            <h3 class="font-bold mt-4">Ventanas y requisitos</h3><template x-for="v in [...permisoDetalle.ventanas,...permisoDetalle.operaciones.filter(o=>!permisoDetalle.ventanas.some(e=>e.id===o.id))]" :key="v.id"><article class="roles-ventana-detalle">
                <strong x-text="v.label+' · '+v.actor"></strong><p class="ui-muted text-xs" x-text="v.limite"></p><code x-text="v.url"></code><p class="ui-muted text-xs">Para entrar deben cumplirse todos estos permisos o sus alternativas de consulta:</p>
                <ul><template x-for="n in v.requisitos" :key="n"><li><code x-text="n"></code></li></template></ul>
                <template x-if="v.puede_ir"><a class="ui-btn ui-btn-secondary mt-3" :href="v.url" target="_blank" rel="noopener"><i class="ph-duotone ph-arrow-square-out" aria-hidden="true"></i>Ir a la ventana</a></template><p x-show="!v.puede_ir" class="ui-muted text-xs mt-2">Tu actor actual no tiene entrada a esta ventana. El permiso por sí solo no cambia el rol.</p>
            </article></template>
            <p x-show="!permisoDetalle.ventanas.length&&!permisoDetalle.operaciones.length" class="roles-aviso">No se encontró una entrada de menú asociada. No se inventa una ruta ni se garantiza acceso por este registro.</p>
        </div></template>
    </section>
</div>
