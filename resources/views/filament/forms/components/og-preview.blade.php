<div
    x-data="{
        get title() {
            return $wire.data?.og_title || $wire.data?.meta_title || $wire.data?.title || $wire.data?.name || $wire.data?.display_name || ''
        },
        get description() {
            return $wire.data?.og_description || $wire.data?.meta_description || ''
        },
        get siteName() {
            return $wire.data?.og_site_name || '{{ \Alexisgt01\CmsCore\Models\BlogSetting::instance()->og_site_name ?? config('app.name') }}'
        },
        get imageUrl() {
            const img = $wire.data?.og_image
            if (img && typeof img === 'object' && img.url) return img.url
            return null
        }
    }"
    class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900"
>
    <span class="mb-3 block text-sm font-medium text-gray-700 dark:text-gray-300">Apercu Facebook</span>

    <div class="max-w-[524px] overflow-hidden rounded-md border border-gray-300 bg-gray-50 dark:border-gray-600 dark:bg-gray-800">
        {{-- Image --}}
        <div
            class="flex h-[274px] items-center justify-center bg-gray-200 dark:bg-gray-700"
            x-show="imageUrl"
        >
            <img :src="imageUrl" class="h-full w-full object-cover" x-show="imageUrl" />
        </div>
        <div
            class="flex h-[274px] items-center justify-center bg-gray-200 dark:bg-gray-700"
            x-show="!imageUrl"
        >
            <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z" /></svg>
        </div>

        {{-- Content --}}
        <div class="px-3 py-2.5">
            <div class="text-xs uppercase tracking-wide text-gray-500" x-text="siteName"></div>
            <div
                class="mt-0.5 text-base font-semibold leading-tight text-gray-900 dark:text-gray-100"
                x-text="title || 'Titre OG...'"
                :class="!title && 'text-gray-300 dark:text-gray-600'"
            ></div>
            <div
                class="mt-0.5 line-clamp-2 text-sm leading-snug text-gray-500 dark:text-gray-400"
                x-text="description || 'Description OG...'"
                :class="!description && 'text-gray-300 dark:text-gray-600'"
            ></div>
        </div>
    </div>
</div>
