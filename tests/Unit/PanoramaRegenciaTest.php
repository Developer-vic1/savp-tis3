<?php

namespace Tests\Unit;

use App\Support\Academico\PanoramaRegencia;
use PHPUnit\Framework\TestCase;

class PanoramaRegenciaTest extends TestCase
{
    public function test_cuenta_personas_sin_duplicar_y_solo_en_grado_y_gestion_a_cargo(): void
    {
        $fila = fn($est,$cur,$gea,$nota='') => (object)['cod_est'=>$est,'cod_cur'=>$cur,'cod_gea'=>$gea,'obs_ins'=>$nota,'mot_obs_ins'=>''];
        $datos=collect([$fila('E1','C1','G1'),$fila('E1','C1','G1','Nota real'),$fila('E2','C1','G1'),$fila('E3','C2','G1','Nota'),$fila('E4','C1','G2','Nota')]);
        $grados=collect([(object)['cod_cur'=>'C1','cod_gea'=>'G1']]);
        $this->assertSame(['estudiantes'=>2,'observados'=>1,'sin_observaciones'=>1],PanoramaRegencia::contar($datos,$grados));
        $this->assertSame(['estudiantes'=>0,'observados'=>0,'sin_observaciones'=>0],PanoramaRegencia::contar($datos,collect()));
    }
}
