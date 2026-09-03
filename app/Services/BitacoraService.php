<?php

namespace App\Services;

use App\Models\Bitacora;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class BitacoraService
{
    public static function registrar(
        string $accion,
        string $tabla,
        ?string $registro = null,
        ?string $modulo = null,
        ?string $nombreRegistro = null,
        ?string $descripcion = null,
        string $nivel = 'INFO',
        string $resultado = 'EXITOSO',
        ?array $valoresAnteriores = null,
        ?array $valoresNuevos = null,
        ?string $error = null
    ): void {
        if (! Schema::hasTable('bitacora')) {
            return;
        }

        $usuario = Auth::user();

        Bitacora::create([
            'cod_usu' => $usuario?->cod_usu,
            'rol_bit' => $usuario?->getRoleNames()?->first(),

            'acc_bit' => $accion,
            'mod_bit' => $modulo,
            'tab_bit' => $tabla,
            'reg_bit' => $registro,
            'nom_reg_bit' => $nombreRegistro,
            'des_bit' => $descripcion,

            'niv_bit' => $nivel,
            'res_bit' => $resultado,

            'ip_bit' => request()?->ip(),
            'age_bit' => request()?->userAgent(),
            'rut_bit' => request()?->path(),
            'met_bit' => request()?->method(),

            'val_ant_bit' => self::enmascararSensibles($valoresAnteriores),
            'val_nue_bit' => self::enmascararSensibles($valoresNuevos),

            'err_bit' => $error,
            'fec_bit' => now(),
        ]);
    }

    private static function enmascararSensibles(?array $datos): ?array
    {
        if ($datos === null) {
            return null;
        }

        $sensibles = [
            'password',
            'password_confirmation',
            'token',
            'remember_token',
            'secret',
            'api_key',
            'contrasena',
            'contraseña',
        ];

        $resultado = [];
        foreach ($datos as $clave => $valor) {
            if (is_array($valor)) {
                $resultado[$clave] = self::enmascararSensibles($valor);
            } elseif (is_string($clave) && in_array(mb_strtolower($clave), $sensibles, true)) {
                $resultado[$clave] = '********';
            } else {
                $resultado[$clave] = $valor;
            }
        }

        return $resultado;
    }
}