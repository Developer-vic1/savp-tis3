<nav class="curricular-fases" aria-label="Fases del registro documental">
    @foreach([1=>['Documento','Carga y lectura'],2=>['Datos autorizados','Revisa la propuesta'],3=>['Confirmación','Respalda la decisión']] as $numero=>$fase)
        <button type="button" x-on:click="fase={{ $numero }}" :aria-current="fase==={{ $numero }}?'step':null" :class="{'es-actual':fase==={{ $numero }}}" @disabled($numero>1 && !($revisionDocumentoCurricular['coherente']??false))>
            <span class="curricular-fase-numero">{{ $numero }}</span><span><strong>{{ $fase[0] }}</strong><small>{{ $fase[1] }}</small></span>
            @if($numero>1 && !($revisionDocumentoCurricular['coherente']??false))<i class="ph-duotone ph-lock-simple" aria-label="Requiere lectura aprobada"></i>@endif
        </button>
    @endforeach
</nav>
