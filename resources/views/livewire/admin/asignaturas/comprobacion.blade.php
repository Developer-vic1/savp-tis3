<section class="ui-card-soft p-5 mt-4">
    <h3 class="ui-title font-bold">3. Confirma el respaldo</h3>
    <fieldset @disabled(!($revisionDocumentoCurricular['coherente']??false))>
        <label class="ui-label block mt-3" for="curricular-motivo">Necesidad educativa y alcance *</label>
        <textarea id="curricular-motivo" class="ui-textarea mt-2" wire:model.live.debounce.400ms="motivoCurricular" rows="3" maxlength="1500" placeholder="Ej.: Incorporar formación en programación por la ampliación autorizada de la oferta técnica."></textarea><x-input-error for="motivoCurricular" />
        <label class="cursos-confirmar mt-4"><input type="checkbox" wire:model.live="firmaSelloComprobados"><span>Comprobé la firma y el sello visibles y contrasté la autorización con la autoridad emisora.</span></label><x-input-error for="firmaSelloComprobados" />
        <label class="ui-label block mt-3" for="curricular-canal">Cómo comprobaste la autorización *</label><input id="curricular-canal" class="ui-input mt-2" wire:model.live.debounce.400ms="canalComprobacion" maxlength="300" placeholder="Ej.: Contraste con el original en Dirección, referencia y fecha de consulta."><x-input-error for="canalComprobacion" />
    </fieldset>
    <p class="ui-muted text-xs mt-3">La comprobación queda atribuida a tu cuenta en bitácora. La lectura automática no certifica autenticidad.</p>
</section>
