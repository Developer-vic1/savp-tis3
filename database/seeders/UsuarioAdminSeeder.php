<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class UsuarioAdminSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $usuarios = [
            [
                'cod_usu' => 'USU_0001',
                'cod_per' => 'PER_0001', // Victor Asturizaga
                'email' => 'asturizagavictor@gmail.com',
                'password' => 'victor123',
                'role' => 'Administrador',
            ],
            [
                'cod_usu' => 'USU_0002',
                'cod_per' => 'PER_0004', // Ana Lucía Vargas
                'email' => 'ana.vargas@gmail.com',
                'password' => 'Regente123',
                'role' => 'Regente',
            ],
            [
                'cod_usu' => 'USU_0003',
                'cod_per' => 'PER_0003', // Luis Fernando Rojas
                'email' => 'luis.rojas@gmail.com',
                'password' => 'Docente123',
                'role' => 'Docente',
            ],
            [
                'cod_usu' => 'USU_0004',
                'cod_per' => 'PER_0006', // Edgar Rios
                'email' => 'edgar.rios@gmail.com',
                'password' => 'Director123',
                'role' => 'Director',
            ],
            [
                'cod_usu' => 'USU_0005',
                'cod_per' => 'PER_0002', // María Fernanda Choque
                'email' => 'maria.choque@gmail.com',
                'password' => 'Estudiante123',
                'role' => 'Estudiante',
            ],
            [
                'cod_usu' => 'USU_0006',
                'cod_per' => 'PER_0007', // Diego Alejandro Huanca Mendoza
                'email' => 'diego.huanca@gmail.com',
                'password' => 'Secretaria123',
                'role' => 'Secretaria',
            ],
            [
                'cod_usu' => 'USU_0007',
                'cod_per' => 'PER_0008', // Lucia Fernanda Quispe Choque
                'email' => 'lucia.quispe@gmail.com',
                'password' => 'Estudiante123',
                'role' => 'Estudiante',
            ],
            [
                'cod_usu' => 'USU_0008',
                'cod_per' => 'PER_0009', // Kevin Andrés Callisaya Rojas
                'email' => 'kevin.callisaya@gmail.com',
                'password' => 'Estudiante123',
                'role' => 'Estudiante',
            ],
            [
                'cod_usu' => 'USU_0009',
                'cod_per' => 'PER_0010', // Valeria Sofia Mamani Flores
                'email' => 'valeria.mamani@gmail.com',
                'password' => 'Estudiante123',
                'role' => 'Estudiante',
            ],
            [
                'cod_usu' => 'USU_0010',
                'cod_per' => 'PER_0011', // Jose Luis Condori Quispe
                'email' => 'jose.condori@gmail.com',
                'password' => 'Estudiante123',
                'role' => 'Estudiante',
            ],
            [
                'cod_usu' => 'USU_0011',
                'cod_per' => 'PER_0012', // Camila Vargas Lopez
                'email' => 'camila.vargas@gmail.com',
                'password' => 'Estudiante123',
                'role' => 'Estudiante',
            ],
            [
                'cod_usu' => 'USU_0012',
                'cod_per' => 'PER_0013', // Fernando Rojas Mamani
                'email' => 'fernando.rojas@gmail.com',
                'password' => 'Estudiante123',
                'role' => 'Estudiante',
            ],
            [
                'cod_usu' => 'USU_0013',
                'cod_per' => 'PER_0014', // Daniela Choque Gutierrez
                'email' => 'daniela.choque@gmail.com',
                'password' => 'Estudiante123',
                'role' => 'Estudiante',
            ],
            [
                'cod_usu' => 'USU_0014',
                'cod_per' => 'PER_0015', // Miguel Flores Condori
                'email' => 'miguel.flores@gmail.com',
                'password' => 'Estudiante123',
                'role' => 'Estudiante',
            ],
            [
                'cod_usu' => 'USU_0015',
                'cod_per' => 'PER_0016', // Paola Lopez Quispe
                'email' => 'paola.lopez@gmail.com',
                'password' => 'Estudiante123',
                'role' => 'Estudiante',
            ],
            [
                'cod_usu' => 'USU_0016',
                'cod_per' => 'PER_0017', // Luis Mamani Rojas
                'email' => 'luis.mamani@gmail.com',
                'password' => 'Estudiante123',
                'role' => 'Estudiante',
            ],
            [
                'cod_usu' => 'USU_0017',
                'cod_per' => 'PER_0018', // Gabriela Condori Flores
                'email' => 'gabriela.condori@gmail.com',
                'password' => 'Estudiante123',
                'role' => 'Estudiante',
            ],
            [
                'cod_usu' => 'USU_0018',
                'cod_per' => 'PER_0019', // Andres Quispe Lopez
                'email' => 'andres.quispe@gmail.com',
                'password' => 'Estudiante123',
                'role' => 'Estudiante',
            ],
            [
                'cod_usu' => 'USU_0019',
                'cod_per' => 'PER_0020', // Natalia Flores Mamani
                'email' => 'natalia.flores@gmail.com',
                'password' => 'Estudiante123',
                'role' => 'Estudiante',
            ],
            [
                'cod_usu' => 'USU_0020',
                'cod_per' => 'PER_0021', // Cristian Rojas Condori
                'email' => 'cristian.rojas@gmail.com',
                'password' => 'Estudiante123',
                'role' => 'Estudiante',
            ],
            [
                'cod_usu' => 'USU_0021',
                'cod_per' => 'PER_0005', // Jorge Andres Flores Mendoza
                'email' => 'jorge.flores@gmail.com',
                'password' => 'Estudiante123',
                'role' => 'Estudiante',
            ]
        ];

        foreach ($usuarios as $data) {
            $user = User::updateOrCreate(
                ['cod_usu' => $data['cod_usu']],
                [
                    'cod_per' => $data['cod_per'],
                    'email' => $data['email'],
                    'email_verified_at' => $now,
                    'password' => $data['password'],
                    'current_team_id' => null,
                    'profile_photo_path' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            if (! $user->hasRole($data['role'])) {
                $user->assignRole($data['role']);
            }
        }
    }
}
