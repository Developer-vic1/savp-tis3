<?php

namespace App\Livewire\Admin;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class GlobalSearch extends Component
{
    public string $query = '';

    #[Locked]
    public ?string $selectedPersonId = null;

    public function mount(): void
    {
        $this->authorizeSearch();
    }

    private function authorizeSearch(): void
    {
        $user = auth()->user();

        abort_unless($user, 401);

        $authorized =
            $user->hasAnyRole([
                'Super Admin',
                'Administrador',
                'Admin',
            ])
            || $user->can('Gestion_Usuarios')
            || $user->can('Gestion_Academica');

        abort_unless($authorized, 403);
    }

    public function updatedQuery(): void
    {
        $this->query = trim(
            mb_substr($this->query, 0, 80)
        );
    }

    #[Computed]
    public function people(): Collection
    {
        $term = trim($this->query);

        if (mb_strlen($term) < 2) {
            return collect();
        }

        $like = '%'.$term.'%';

        return DB::table('persona as p')
            ->leftJoin('users as u', 'u.cod_per', '=', 'p.cod_per')
            ->leftJoin(
                'personal_institucional as pi',
                'pi.cod_per',
                '=',
                'p.cod_per'
            )
            ->leftJoin(
                'docente as d',
                'd.cod_pin',
                '=',
                'pi.cod_pin'
            )
            ->select([
                'p.cod_per',
                'p.nom_per',
                'p.ape_pat_per',
                'p.ape_mat_per',
                'p.ci_per',
                'p.tel_per',
                'p.ema_per',
                'p.est_per',

                'u.cod_usu',
                'u.email as email_usuario',
                'u.est_usu',

                'pi.cod_pin',
                'pi.car_pin',
                'pi.est_pin',

                'd.cod_doc',
                'd.esp_doc',
                'd.est_doc',
            ])
            ->where(function ($q) use ($like) {
                $q
                    ->whereRaw(
                        "CONCAT_WS(' ', p.nom_per, p.ape_pat_per, p.ape_mat_per) ILIKE ?",
                        [$like]
                    )
                    ->orWhereRaw('p.nom_per ILIKE ?', [$like])
                    ->orWhereRaw('p.ape_pat_per ILIKE ?', [$like])
                    ->orWhereRaw('p.ape_mat_per ILIKE ?', [$like])
                    ->orWhereRaw('p.ci_per ILIKE ?', [$like])
                    ->orWhereRaw('p.tel_per ILIKE ?', [$like])
                    ->orWhereRaw('p.ema_per ILIKE ?', [$like])
                    ->orWhereRaw('u.email ILIKE ?', [$like])
                    ->orWhereRaw('pi.car_pin ILIKE ?', [$like])
                    ->orWhereRaw('d.esp_doc ILIKE ?', [$like]);
            })
            ->orderBy('p.ape_pat_per')
            ->orderBy('p.ape_mat_per')
            ->orderBy('p.nom_per')
            ->limit(8)
            ->get();
    }

    public function selectPerson(string $codPer): void
    {
        $this->authorizeSearch();

        abort_unless(
            DB::table('persona')
                ->where('cod_per', $codPer)
                ->exists(),
            404
        );

        $this->selectedPersonId = $codPer;
    }

    public function closePerson(): void
    {
        $this->selectedPersonId = null;
    }

    public function clearSearch(): void
    {
        $this->query = '';
    }

    #[Computed]
    public function personDetail(): ?array
    {
        if (! $this->selectedPersonId) {
            return null;
        }

        $person = DB::table('persona as p')
            ->leftJoin('users as u', 'u.cod_per', '=', 'p.cod_per')
            ->leftJoin(
                'personal_institucional as pi',
                'pi.cod_per',
                '=',
                'p.cod_per'
            )
            ->leftJoin(
                'docente as d',
                'd.cod_pin',
                '=',
                'pi.cod_pin'
            )
            ->where('p.cod_per', $this->selectedPersonId)
            ->select([
                'p.*',

                'u.cod_usu',
                'u.email as email_usuario',
                'u.est_usu',

                'pi.cod_pin',
                'pi.car_pin',
                'pi.est_pin',

                'd.cod_doc',
                'd.esp_doc',
                'd.est_doc',
            ])
            ->first();

        if (! $person) {
            return null;
        }

        $roles = [];

        if ($person->cod_usu) {
            $roles = DB::table('model_has_roles as mhr')
                ->join('roles as r', 'r.id', '=', 'mhr.role_id')
                ->where('mhr.cod_usu', $person->cod_usu)
                ->where('mhr.model_type', 'App\Models\User')
                ->pluck('r.name')
                ->all();
        }

        $birthDate = null;
        $age = null;

        if ($person->fec_nac_per) {
            $date = Carbon::parse($person->fec_nac_per);

            $birthDate = $date->format('d/m/Y');
            $age = $date->age;
        }

        $expedido = match ($person->exp_per) {
            'LP' => 'La Paz',
            'CBBA' => 'Cochabamba',
            'SCZ' => 'Santa Cruz',
            'ORU' => 'Oruro',
            'PT' => 'Potosí',
            'CH' => 'Chuquisaca',
            'TJ' => 'Tarija',
            'BN' => 'Beni',
            'PD' => 'Pando',
            default => $person->exp_per,
        };

        return [
            'cod_per' => $person->cod_per,

            'nombre' => trim(
                collect([
                    $person->nom_per,
                    $person->ape_pat_per,
                    $person->ape_mat_per,
                ])->filter()->implode(' ')
            ),

            'nombres' => $person->nom_per,
            'paterno' => $person->ape_pat_per,
            'materno' => $person->ape_mat_per,

            'ci' => $person->ci_per,
            'complemento' => $person->com_per,
            'expedido' => $expedido,

            'fecha_nacimiento' => $birthDate,
            'edad' => $age,

            'genero' => $person->gen_per,

            'telefono' => $person->tel_per,
            'correo_personal' => $person->ema_per,
            'direccion' => $person->dir_per,

            'estado_persona' => $person->est_per,

            'cod_usu' => $person->cod_usu,
            'email_usuario' => $person->email_usuario,
            'estado_usuario' => $person->est_usu,

            'roles' => $roles,

            'cod_pin' => $person->cod_pin,
            'cargo' => $person->car_pin,
            'estado_personal' => $person->est_pin,

            'cod_doc' => $person->cod_doc,
            'especialidad' => $person->esp_doc,
            'estado_docente' => $person->est_doc,
        ];
    }

    public function render()
    {
        return view('livewire.admin.global-search');
    }
}
