<?php

// Puente de compatibilidad: los nombres y URLs existentes permanecen registrados.
foreach (['direccion', 'secretaria', 'regencia', 'docente', 'estudiante'] as $workspace) {
    require __DIR__.'/'.$workspace.'.php';
}
require __DIR__.'/workspace_domains.php';
