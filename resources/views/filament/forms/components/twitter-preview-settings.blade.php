<div
    x-data="{
        get cardType() {
            return $wire.data?.twitter_card_default || 'summary_large_image'
        },
        get title() {
            return $wire.data?.twitter_title_template || $wire.data?.og_title_template || $wire.data?.default_meta_title_template || ''
        },
        get description() {
            return $wire.data?.twitter_description_template || $wire.data?.og_description_template || $wire.data?.default_meta_description_template || ''
        },
        get site() {
            return $wire.data?.twitter_site || ''
        },
        get imageUrl() {
            const img = $wire.data?.twitter_image_fallback || $wire.data?.og_image_fallback
            if (img && typeof img === 'object' && img.url) return img.url
            return null
        },
        truncate(text, max) {
            if (text.length <= max) return text
            return text.substring(0, max) + '...'
        }
    }"
    class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900"
>
    <span class="mb-3 block text-sm font-medium text-gray-700 dark:text-gray-300">Apercu Twitter / X (templates par defaut)</span>

    {{-- Summary Large Image --}}
    <div x-show="cardType === 'summary_large_image'" class="max-w-[504px] overflow-hidden rounded-xl border border-gray-300 dark:border-gray-600">
        {{-- Image --}}
        <div class="flex h-[252px] items-center justify-center bg-gray-200 dark:bg-gray-700">
            <img x-show="imageUrl" :src="imageUrl" class="h-full w-full object-cover" />
            <svg x-show="!imageUrl" class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z" /></svg>
        </div>
        {{-- Content --}}
        <div class="bg-white px-3 py-2.5 dark:bg-gray-800">
            <div class="text-base font-bold leading-tight text-gray-900 dark:text-gray-100" x-text="truncate(title || 'Template titre Twitter...', 70)" :class="!title && 'text-gray-300 dark:text-gray-600'"></div>
            <div class="mt-0.5 line-clamp-2 text-sm text-gray-500 dark:text-gray-400" x-text="truncate(description || 'Template description Twitter...', 200)" :class="!description && 'text-gray-300 dark:text-gray-600'"></div>
            <div class="mt-1 text-sm text-gray-400" x-text="site || 'example.com'"></div>
        </div>
    </div>

    {{-- Summary (small) --}}
    <div x-show="cardType === 'summary'" class="flex max-w-[504px] overflow-hidden rounded-xl border border-gray-300 dark:border-gray-600">
        {{-- Thumbnail --}}
        <div class="flex h-[125px] w-[125px] flex-shrink-0 items-center justify-center bg-gray-200 dark:bg-gray-700">
            <img x-show="imageUrl" :src="imageUrl" class="h-full w-full object-cover" />
            <svg x-show="!imageUrl" class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z" /></svg>
        </div>
        {{-- Content --}}
        <div class="flex flex-col justify-center bg-white px-3 py-2 dark:bg-gray-800">
            <div class="text-sm font-bold text-gray-900 dark:text-gray-100" x-text="truncate(title || 'Titre...', 70)" :class="!title && 'text-gray-300 dark:text-gray-600'"></div>
            <div class="mt-0.5 line-clamp-2 text-xs text-gray-500 dark:text-gray-400" x-text="truncate(description || 'Description...', 200)" :class="!description && 'text-gray-300 dark:text-gray-600'"></div>
            <div class="mt-1 text-xs text-gray-400" x-text="site || 'example.com'"></div>
        </div>
    </div>
</div>
