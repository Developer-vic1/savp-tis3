# FUENTES INSTITUCIONALES REALES

Esta carpeta conserva 25 seeders institucionales existentes del repositorio `savp-tis3`, sin cambiar nombres, códigos, docentes, planes, horarios ni instrucciones de carga. Únicamente se adapta el namespace a la ubicación `Database\Seeders\Oficial\REALES`, para cumplir PSR-4. Los hashes original y organizado se registran en `MANIFIESTO_SEEDERS.json`.

Los archivos PHP conservan sus llamadas a modelos del esquema anterior. Su conservación aquí no los convierte automáticamente en importadores del nuevo contrato canónico. El motor de historial consume sus fuentes y conserva los registros, adaptando las relaciones normalizadas; no llama estas rutinas heredadas para modificar planes o horarios.

`FUENTES` contiene las fuentes auxiliares originales. Los archivos locales se excluyen de Git porque pueden contener datos personales institucionales; no se publica la nómina ni los hashes de usuarios. El motor nuevo debe conservar sus datos, sin crear nuevos docentes o alterar asignaciones.

El historial sintético se organiza exclusivamente en las carpetas hermanas `2020`, `2021`, `2022`, `2023`, `2024`, `2025` y `2026`. La copia de una fuente oficial de 2026 no acredita por sí sola una distribución horaria oficial de gestiones anteriores.
