<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex,nofollow">
    <title>Modo de diseño · SAVP</title><x-icono-institucional />
    <script>if(localStorage.getItem('savp-theme')==='dark'){document.documentElement.classList.add('dark');document.documentElement.dataset.theme='dark';}</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        [x-cloak]{display:none!important}body{margin:0;background:var(--ui-bg);color:var(--ui-text);font-family:Figtree,system-ui,sans-serif}.diseno-contenedor{width:min(1440px,100%);margin:auto;padding:1.2rem}.diseno-acceso{max-width:30rem;margin:10vh auto;padding:1.6rem}.diseno-cabecera{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;padding:1.3rem}.diseno-identidad{display:flex;align-items:center;gap:1rem}.diseno-identidad img{width:3rem;height:3rem;object-fit:contain}.diseno-roles{display:flex;gap:.5rem;flex-wrap:wrap;margin:1rem 0}.diseno-roles button[aria-pressed=true]{background:var(--ui-primary-soft);color:var(--ui-primary);border-color:var(--ui-primary-border)}.diseno-catalogo{display:grid;grid-template-columns:minmax(16rem,20rem) minmax(0,1fr);gap:1rem;align-items:start}.diseno-lista{max-height:75vh;overflow:auto;display:grid;gap:.4rem;margin-top:.8rem}.diseno-lista button{padding:.8rem;text-align:left;border-radius:.7rem;color:var(--ui-text);transition:background .15s}.diseno-lista button:hover,.diseno-lista button[aria-pressed=true]{background:var(--ui-primary-soft);color:var(--ui-primary)}.diseno-lista small{display:block;color:var(--ui-muted);margin-top:.2rem}.diseno-captura{display:block;width:100%;height:auto;border:1px solid var(--ui-border);border-radius:.7rem;margin-top:1rem}.diseno-visor{padding:1.2rem;animation:diseno-aparecer .18s ease-out}.diseno-vacio{padding:2.5rem 1rem;text-align:center;border:1px dashed var(--ui-border);border-radius:1rem;margin-top:1rem}.diseno-aviso{padding:1rem;border:1px solid var(--ui-info-border);border-radius:.8rem;background:var(--ui-info-soft);margin:1rem 0;font-size:.85rem}.diseno-acceso label{display:block;margin:1.5rem 0 .5rem}.diseno-error{color:var(--ui-danger);margin-top:.5rem;font-size:.85rem}.diseno-tema{display:flex;align-items:center;gap:.6rem}.diseno-catalogo .ui-card{min-width:0}@keyframes diseno-aparecer{from{opacity:0;transform:translateY(4px)}to{opacity:1;transform:translateY(0)}}@media(max-width:720px){.diseno-contenedor{padding:.75rem}.diseno-catalogo{grid-template-columns:1fr}.diseno-lista{max-height:15rem}.diseno-acceso{margin:5vh .75rem}.diseno-roles .ui-btn{flex:1}.diseno-cabecera{padding:1rem}}@media(prefers-reduced-motion:reduce){.diseno-visor{animation:none}.diseno-lista button{transition:none}}
    </style>
</head>
<body>
    @yield('contenido')
    @livewireScripts
</body>
</html>
