@if ($media)
    <div class="space-y-3">
        @if (str_starts_with($media->mime_type, 'image/'))
            <div class="w-full rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-900 flex items-center justify-center">
                <img
                    src="{{ media_url($media->url, ['width' => 600, 'height' => 400, 'resizing_type' => 'fit']) }}"
                    alt="{{ $media->name }}"
                    class="max-w-full max-h-64 object-contain"
                />
            </div>
        @elseif ($media->mime_type === 'application/pdf')
            <div class="w-full h-48 rounded-lg bg-gray-100 dark:bg-gray-900 flex items-center justify-center">
                <x-heroicon-o-document-text class="h-16 w-16 text-red-500" />
            </div>
        @else
            <div class="w-full h-48 rounded-lg bg-gray-100 dark:bg-gray-900 flex items-center justify-center">
                <x-heroicon-o-document class="h-16 w-16 text-gray-400" />
            </div>
        @endif

        <div class="text-sm text-gray-500 dark:text-gray-400 space-y-1">
            <p><span class="font-medium text-gray-700 dark:text-gray-300">Fichier :</span> {{ $media->file_name }}</p>
            <p><span class="font-medium text-gray-700 dark:text-gray-300">Type :</span> {{ $media->mime_type }}</p>
            <p><span class="font-medium text-gray-700 dark:text-gray-300">Taille :</span> {{ $media->human_readable_size }}</p>
        </div>
    </div>
@endif
