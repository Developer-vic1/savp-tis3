<?php

namespace App\Livewire\Admin;

use App\Services\BitacoraService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

abstract class CatalogoInstitucional extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    public string $search = '';
    public string $estado = '';
    public string $extraFiltro1 = '';
    public string $extraFiltro2 = '';
    public int $perPage = 10;
    public bool $modalFormulario = false;
    public bool $modalDetalle = false;
    public bool $editando = false;
    public ?string $seleccionado = null;
    public array $form = [];
    public array $analisis = [];
    public array $detalle = [];

    abstract protected function modelo(): string;
    abstract protected function soporte(): object;
    abstract protected function clavePrimaria(): string;
    abstract protected function campoNombre(): string;
    abstract protected function campoEstado(): string;
    abstract protected function camposFormulario(): array;
    abstract protected function camposBusqueda(): array;
    abstract protected function reglas(): array;
    abstract protected function vista(): string;
    abstract protected function configuracion(): array;

    protected function relacionConteo(): ?string
    {
        return null;
    }

    protected function campoOrden(): string
    {
        return $this->campoNombre();
    }

    protected function filtrosAdicionales(): array
    {
        return [];
    }

    public function mount(): void
    {
        $this->limpiarFormulario();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedEstado(): void
    {
        $this->resetPage();
    }

    public function updatedExtraFiltro1(): void
    {
        $this->resetPage();
    }

    public function updatedExtraFiltro2(): void
    {
        $this->resetPage();
    }

    public function updatedForm(mixed $value = null, ?string $key = null): void
    {
        $this->analizarFormulario();
    }

    public function limpiarFiltros(): void
    {
        $this->search = '';
        $this->estado = '';
        $this->extraFiltro1 = '';
        $this->extraFiltro2 = '';
        $this->resetPage();
    }

    public function abrirCrear(): void
    {
        $this->editando = false;
        $this->seleccionado = null;
        $this->limpiarFormulario();
        $this->modalFormulario = true;
    }

    public function abrirEditar(string $codigo): void
    {
        $registro = $this->modelo()::query()->findOrFail($codigo);
        $this->editando = true;
        $this->seleccionado = $codigo;
        $this->form = collect($this->camposFormulario())
            ->mapWithKeys(fn ($valor, $campo) => [$campo => $registro->{$campo} ?? $valor])
            ->all();
        $this->analizarFormulario();
        $this->modalFormulario = true;
    }

    public function cerrarFormulario(): void
    {
        $this->modalFormulario = false;
        $this->resetValidation();
    }

    public function analizarFormulario(): void
    {
        $this->analisis = $this->soporte()->analizar($this->form, $this->seleccionado);
    }

    public function aplicarSugerencias(): void
    {
        $this->analizarFormulario();
        $this->form = array_merge($this->form, $this->analisis['datos'] ?? []);
    }

    public function guardar(): void
    {
        $this->analizarFormulario();

        if (! ($this->analisis['puede_guardar'] ?? false)) {
            $this->dispatch('swal:warning', title: 'Registro bloqueado', text: implode(' ', $this->analisis['bloqueos'] ?? []));
            return;
        }

        $this->form = array_merge($this->form, $this->analisis['datos'] ?? []);
        $this->validate($this->reglas());
        $modelo = $this->modelo();

        if ($this->editando && $this->seleccionado) {
            $registro = $modelo::query()->findOrFail($this->seleccionado);
            $anteriores = $registro->toArray();
            $registro->update($this->form);
            $accion = 'ACTUALIZAR_' . mb_strtoupper($this->configuracion()['tabla']);
        } else {
            $registro = $modelo::query()->create($this->form);
            $anteriores = null;
            $accion = 'CREAR_' . mb_strtoupper($this->configuracion()['tabla']);
        }

        BitacoraService::registrar(
            accion: $accion,
            tabla: $this->configuracion()['tabla'],
            registro: (string) $registro->getKey(),
            modulo: $this->configuracion()['titulo'],
            nombreRegistro: (string) $registro->{$this->campoNombre()},
            descripcion: 'Se guardó el registro con validación preventiva.',
            nivel: 'SUCCESS',
            valoresAnteriores: $anteriores,
            valoresNuevos: $registro->fresh()->toArray(),
        );

        $this->modalFormulario = false;
        $this->resetPage();
        $this->dispatch('swal:success', title: 'Registro guardado', text: 'La información institucional fue actualizada correctamente.');
    }

    public function cambiarEstado(string $codigo): void
    {
        $registro = $this->modelo()::query()->findOrFail($codigo);
        $campo = $this->campoEstado();
        $nuevo = $registro->{$campo} === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';
        $registro->update([$campo => $nuevo]);

        BitacoraService::registrar(
            accion: $nuevo === 'ACTIVO' ? 'REACTIVAR_REGISTRO' : 'DESACTIVAR_REGISTRO',
            tabla: $this->configuracion()['tabla'],
            registro: (string) $registro->getKey(),
            modulo: $this->configuracion()['titulo'],
            nombreRegistro: (string) $registro->{$this->campoNombre()},
            descripcion: "El registro cambió a estado {$nuevo}.",
        );

        $this->dispatch('swal:success', title: 'Estado actualizado', text: "El registro ahora está {$nuevo}.");
    }

    public function abrirDetalle(string $codigo): void
    {
        $registro = $this->consultaBase()->findOrFail($codigo);
        $this->detalle = $registro->toArray();
        $this->modalDetalle = true;
    }

    public function cerrarDetalle(): void
    {
        $this->modalDetalle = false;
        $this->detalle = [];
    }

    public function render()
    {
        $query = $this->consultaBase()
            ->when($this->search !== '', function (Builder $query) {
                $query->where(function (Builder $sub) {
                    foreach ($this->camposBusqueda() as $campo) {
                        $sub->orWhere($campo, 'ILIKE', '%' . trim($this->search) . '%');
                    }
                });
            })
            ->when($this->estado !== '', fn (Builder $query) => $query->where($this->campoEstado(), $this->estado));

        foreach ($this->filtrosAdicionales() as $indice => $filtro) {
            $propiedad = 'extraFiltro' . ($indice + 1);
            if ($this->{$propiedad} !== '') {
                $query->where($filtro['campo'], $this->{$propiedad});
            }
        }

        $query->orderBy($this->campoOrden());

        $modelo = $this->modelo();
        $campoEstado = $this->campoEstado();
        $configuracion = $this->configuracion();

        return view($this->vista(), [
            'registros' => $query->paginate($this->perPage),
            'configuracion' => $configuracion,
            'filtrosAdicionales' => $this->filtrosAdicionales(),
            'metricas' => [
                'total' => $modelo::count(),
                'activos' => $modelo::where($campoEstado, 'ACTIVO')->count(),
                'inactivos' => $modelo::where($campoEstado, 'INACTIVO')->count(),
                'relacionados' => $this->totalRelacionados(),
            ],
        ]);
    }

    protected function consultaBase(): Builder
    {
        $query = $this->modelo()::query();

        if ($this->relacionConteo()) {
            $query->withCount($this->relacionConteo());
        }

        return $query;
    }

    private function totalRelacionados(): int
    {
        $relacion = $this->relacionConteo();

        return $relacion ? $this->modelo()::query()->withCount($relacion)->get()->sum("{$relacion}_count") : 0;
    }

    private function limpiarFormulario(): void
    {
        $this->form = $this->camposFormulario();
        $this->analisis = [];
        $this->resetValidation();
    }
}
