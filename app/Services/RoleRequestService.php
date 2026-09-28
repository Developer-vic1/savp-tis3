<?php

namespace App\Services;

use App\Models\RoleRequest;
use App\Models\User;
use App\Support\InstitutionalRoleGovernance;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleRequestService
{
    public function __construct(private InstitutionalRoleGovernance $governance, private InstitutionalAuthorityService $authority, private InstitutionalDocumentAnalyzer $analyzer) {}

    public function authorize(User $actor, string $permission): void
    {
        if ($actor->est_usu !== 'ACTIVO' || ! $actor->hasRole('Administrador') || ! $actor->can('roles-permisos.gestionar') || ! $actor->can($permission)) {
            throw new AuthorizationException('No tiene autorización para esta operación de gobernanza.');
        }
    }

    public function analyze(array $data): array
    {
        return $this->governance->analyze(
            $data['requested_name'], $data['justification'], $data['functions'], $data['requested_permissions'],
            Role::query()->where('guard_name', 'web')->pluck('name')->all(),
            Permission::query()->where('guard_name', 'web')->pluck('name')->all(),
        );
    }

    public function submit(User $actor, array $data, UploadedFile $file): RoleRequest
    {
        $this->authorize($actor, 'roles.solicitudes.crear');
        $authority = $this->authority->current();
        if ($authority['status'] !== 'ACTIVO') throw ValidationException::withMessages(['document' => $authority['message']]);
        $analysis = $this->analyze($data);
        if ($analysis['status'] !== 'APTO') throw ValidationException::withMessages(['requested_name' => $analysis['summary']]);

        // MIME se determina por contenido; el nombre original solo se conserva como metadato.
        $mime = $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension());
        $types = ['application/pdf' => 'pdf', 'image/jpeg' => 'jpg', 'image/png' => 'png'];
        if (! isset($types[$mime]) || ! in_array($extension, $mime === 'image/jpeg' ? ['jpg', 'jpeg'] : [$types[$mime]], true)
            || $file->getSize() < 1 || $file->getSize() > 10 * 1024 * 1024) {
            throw ValidationException::withMessages(['document' => 'Adjunte un PDF, JPG o PNG válido de hasta 10 MB.']);
        }
        $hash = hash_file('sha256', $file->getRealPath());
        $reuse = RoleRequest::query()->where('document_hash', $hash)->exists();
        $path = $file->storeAs('role-requests', bin2hex(random_bytes(20)).'.'.$types[$mime], 'local');
        if (! $path) throw ValidationException::withMessages(['document' => 'No se pudo guardar el documento privado.']);
        try {
            return DB::transaction(function () use ($actor, $data, $file, $path, $hash, $mime, $analysis, $authority, $reuse) {
                $request = RoleRequest::create([
                    ...$data, 'analysis_result' => $analysis, 'status' => $reuse ? 'REQUIERE_REVISION_REUSO' : 'PENDIENTE_REVISION',
                    'requested_by' => $actor->cod_usu, 'director_id' => $authority['director']->cod_dir,
                    'document_path' => $path, 'document_hash' => $hash,
                    'document_original_name' => mb_substr(basename(str_replace('\\', '/', $file->getClientOriginalName())), 0, 255),
                    'document_mime' => $mime, 'document_size' => $file->getSize(),
                ]);
                $request->update(['document_analysis' => $this->analyzer->analyze($request)]);
                BitacoraService::registrar('CREAR_SOLICITUD_ROL', 'role_requests', (string) $request->id, 'Roles y Permisos', $request->requested_name,
                    'Solicitud documental recibida; pendiente de revisión independiente.', valoresNuevos: ['hash' => $hash, 'estado' => $request->status]);
                BitacoraService::registrar('DOCUMENTO_ASOCIADO', 'role_requests', (string) $request->id, 'Roles y Permisos', $request->requested_name,
                    'Documento privado asociado.', valoresNuevos: ['hash' => $hash]);
                return $request;
            });
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            throw $e;
        }
    }

    public function cancel(User $actor, int $id): void
    {
        $this->authorize($actor, 'roles.solicitudes.cancelar');
        DB::transaction(function () use ($actor, $id) {
            $request = RoleRequest::query()->lockForUpdate()->findOrFail($id);
            if ($request->requested_by !== $actor->cod_usu || ! in_array($request->status, ['PENDIENTE_REVISION', 'REQUIERE_REVISION_REUSO'], true)) {
                throw ValidationException::withMessages(['role_request' => 'Solo el solicitante puede cancelar una solicitud pendiente.']);
            }
            $request->update(['status' => 'CANCELADA']);
            BitacoraService::registrar('CANCELAR_SOLICITUD_ROL', 'role_requests', (string) $request->id, 'Roles y Permisos', $request->requested_name,
                'El solicitante canceló la solicitud.', valoresNuevos: ['estado' => 'CANCELADA', 'hash' => $request->document_hash]);
        });
    }

    public function review(User $actor, int $id, bool $approved, string $note, bool $directorMatches, bool $readable, bool $signaturePresent, bool $sealPresent): RoleRequest
    {
        $this->authorize($actor, 'roles.solicitudes.analizar');
        if (mb_strlen(trim($note)) < 20) throw ValidationException::withMessages(['review_note' => 'Explique la revisión en al menos 20 caracteres.']);
        return DB::transaction(function () use ($actor, $id, $approved, $note, $directorMatches, $readable, $signaturePresent, $sealPresent) {
            $request = RoleRequest::query()->lockForUpdate()->findOrFail($id);
            if (! in_array($request->status, ['PENDIENTE_REVISION', 'REQUIERE_REVISION_REUSO'], true) || $request->requested_by === $actor->cod_usu) {
                throw ValidationException::withMessages(['review_note' => 'La solicitud no admite esta revisión o usted es quien la presentó.']);
            }
            $authority = $this->authority->current();
            if ($authority['status'] !== 'ACTIVO' || $authority['director']->cod_dir !== $request->director_id) {
                throw ValidationException::withMessages(['review_note' => 'La autoridad institucional cambió o no es única.']);
            }
            if ($approved && (! $directorMatches || ! $readable || ! $signaturePresent || ! $sealPresent)) {
                throw ValidationException::withMessages(['review_note' => 'Para aprobar, verifique legibilidad, identidad del Director y presencia de firma y sello.']);
            }
            if ($approved && $request->status === 'REQUIERE_REVISION_REUSO' && ! preg_match('/reutiliz|mismo documento|uso anterior/iu', $note)) {
                throw ValidationException::withMessages(['review_note' => 'Explique expresamente la reutilización de esta autorización.']);
            }
            $request->update(['status' => $approved ? 'REVISADA' : 'RECHAZADA', 'reviewed_by' => $actor->cod_usu,
                'reviewed_at' => now(), 'review_note' => $note,
                'document_analysis' => array_merge($request->document_analysis ?? [], [
                    'manual_review' => ['director_matches' => $directorMatches, 'document_readable' => $readable,
                        'signature_present' => $signaturePresent, 'seal_present' => $sealPresent, 'reviewed_by' => $actor->cod_usu],
                ])]);
            BitacoraService::registrar($approved ? 'ANALIZAR_SOLICITUD_ROL' : 'RECHAZAR_SOLICITUD_ROL', 'role_requests', (string) $request->id,
                'Roles y Permisos', $request->requested_name, $note, valoresNuevos: ['estado' => $request->status, 'hash' => $request->document_hash]);
            return $request;
        });
    }

    public function createRole(User $actor, int $id): Role
    {
        $this->authorize($actor, 'roles.crear');
        $this->authorize($actor, 'roles.permisos.asignar');
        return DB::transaction(function () use ($actor, $id) {
            $this->authorize($actor, 'roles.crear');
            $this->authorize($actor, 'roles.permisos.asignar');
            $request = RoleRequest::query()->lockForUpdate()->findOrFail($id);
            if ($request->status !== 'REVISADA' || ! $request->reviewed_by || $request->reviewed_by === $request->requested_by) {
                throw ValidationException::withMessages(['role_request' => 'La solicitud necesita revisión documental independiente.']);
            }
            $authority = $this->authority->current();
            if ($authority['status'] !== 'ACTIVO' || $authority['director']->cod_dir !== $request->director_id) {
                throw ValidationException::withMessages(['role_request' => 'La autoridad institucional cambió.']);
            }
            $review = $request->document_analysis['manual_review'] ?? [];
            foreach (['director_matches', 'document_readable', 'signature_present', 'seal_present'] as $key) {
                if (($review[$key] ?? false) !== true) throw ValidationException::withMessages(['role_request' => 'La revisión documental está incompleta.']);
            }
            if (! Storage::disk('local')->exists($request->document_path) || hash_file('sha256', Storage::disk('local')->path($request->document_path)) !== $request->document_hash) {
                throw ValidationException::withMessages(['role_request' => 'El documento ya no está disponible o fue alterado.']);
            }
            $analysis = $this->analyze($request->only(['requested_name', 'justification', 'functions', 'requested_permissions']));
            if ($analysis['status'] !== 'APTO') throw ValidationException::withMessages(['role_request' => $analysis['summary']]);
            $role = Role::create(['name' => trim($request->requested_name), 'guard_name' => 'web']);
            $role->syncPermissions($analysis['allowed_permissions']);
            $request->update(['status' => 'CREADA', 'created_role_id' => $role->id, 'analysis_result' => $analysis]);
            BitacoraService::registrar('CREAR_ROL', 'roles', (string) $role->id, 'Roles y Permisos', $role->name,
                'Rol creado desde solicitud validada.', valoresNuevos: ['solicitud' => $request->id, 'permisos' => $analysis['allowed_permissions'], 'hash' => $request->document_hash]);
            app(PermissionRegistrar::class)->forgetCachedPermissions();
            return $role;
        });
    }
}
