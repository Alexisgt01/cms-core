@props(['key', 'label', 'children' => [], 'mandatory' => null])

<div style="border: 1px solid rgba(0,0,0,0.08); border-radius: 0.5rem; padding: 0.75rem 1rem;">
    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
        <input
            type="checkbox"
            x-model="features.{{ $key }}"
            style="accent-color: rgb(var(--primary-600)); width: 1rem; height: 1rem;"
        />
        <span style="font-size: 0.875rem; font-weight: 600; color: rgb(17 24 39);">{{ $label }}</span>
    </label>

    @if ($mandatory)
        <p
            x-show="features.{{ $key }}"
            style="font-size: 0.75rem; color: rgb(156 163 175); margin: 0.375rem 0 0 1.75rem;"
        >
            Toujours inclus : {{ $mandatory }}
        </p>
    @endif

    @if (count($children))
        <div
            x-show="features.{{ $key }}"
            style="display: flex; flex-direction: column; gap: 0.375rem; margin-top: 0.5rem; padding-left: 1.75rem;"
        >
            @foreach ($children as $child)
                <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.8125rem; color: rgb(55 65 81); cursor: pointer;">
                    <input
                        type="checkbox"
                        x-model="features.{{ $child['key'] }}"
                        style="accent-color: rgb(var(--primary-600));"
                    />
                    {{ $child['label'] }}
                </label>
            @endforeach
        </div>
    @endif
</div>
