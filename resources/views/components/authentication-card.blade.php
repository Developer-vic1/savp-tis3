<div class="w-full max-w-[46rem]">
    @isset($logo)
        <div class="mb-5">
            {{ $logo }}
        </div>
    @endisset

    {{ $slot }}
</div>