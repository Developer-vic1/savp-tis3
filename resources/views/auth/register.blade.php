<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo"><x-authentication-card-logo /></x-slot>
        <h1 class="ui-title text-xl font-bold">Solicitar acceso institucional</h1>
        <p class="ui-subtitle mt-4">Tu cuenta debe estar vinculada a tu identidad y rol en la unidad educativa. Solicita el acceso al responsable del sistema.</p>
        <p class="ui-subtitle mt-3">Si ya tienes una cuenta, utiliza tus credenciales institucionales.</p>
        <a class="ui-btn-primary mt-5" href="{{ route('login') }}">Iniciar sesión</a>
    </x-authentication-card>
</x-guest-layout>
