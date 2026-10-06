@props(['id' => null, 'maxWidth' => null])
@php $id = $id ?? md5($attributes->wire('model')); @endphp

<x-modal :id="$id" :maxWidth="$maxWidth" aria-labelledby="{{ $id }}-titulo" {{ $attributes }}>
    <div class="px-6 py-4">
        <div id="{{ $id }}-titulo" class="ui-title text-lg font-medium">
            {{ $title }}
        </div>

        <div class="ui-muted mt-4 text-sm">
            {{ $content }}
        </div>
    </div>

    <div class="savp-modal-pie text-end">
        {{ $footer }}
    </div>
</x-modal>
