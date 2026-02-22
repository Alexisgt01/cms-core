<div
    x-data="{
        mode: 'desktop',
        get title() {
            return $wire.data?.default_meta_title_template || ''
        },
        get description() {
            return $wire.data?.default_meta_description_template || ''
        },
        get titleLength() { return this.title.length },
        get descLength() { return this.description.length },
        get titleColor() {
            if (this.titleLength === 0) return 'text-gray-400'
            if (this.titleLength <= 60) return 'text-green-600'
            if (this.titleLength <= 70) return 'text-orange-500'
            return 'text-red-600'
        },
        get descColor() {
            if (this.descLength === 0) return 'text-gray-400'
            if (this.descLength >= 120 && this.descLength <= 160) return 'text-green-600'
            if (this.descLength > 160) return 'text-red-600'
            return 'text-orange-500'
        },
        truncate(text, max) {
            if (text.length <= max) return text
            return text.substring(0, max) + '...'
        }
    }"
    class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900"
>
    {{-- Header --}}
    <div class="mb-3 flex items-center justify-between">
        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Apercu Google (template par defaut)</span>
        <div class="flex gap-1 rounded-md bg-gray-100 p-0.5 dark:bg-gray-800">
            <button
                type="button"
                @click="mode = 'desktop'"
                :class="mode === 'desktop' ? 'bg-white shadow dark:bg-gray-700' : ''"
                class="rounded px-2 py-1 text-xs font-medium text-gray-600 transition dark:text-gray-400"
            >Desktop</button>
            <button
                type="button"
                @click="mode = 'mobile'"
                :class="mode === 'mobile' ? 'bg-white shadow dark:bg-gray-700' : ''"
                class="rounded px-2 py-1 text-xs font-medium text-gray-600 transition dark:text-gray-400"
            >Mobile</button>
        </div>
    </div>

    {{-- SERP Preview --}}
    <div :class="mode === 'mobile' ? 'max-w-[360px]' : 'max-w-[600px]'" class="space-y-1">
        {{-- URL --}}
        <div class="flex items-center gap-1.5 text-sm">
            <svg class="h-4 w-4 text-gray-400" viewBox="0 0 16 16" fill="currentColor"><circle cx="8" cy="8" r="7" fill="none" stroke="currentColor" stroke-width="1.5"/><text x="50%" y="52%" text-anchor="middle" dominant-baseline="central" font-size="8" fill="currentColor" font-family="sans-serif">G</text></svg>
            <span class="text-gray-600 dark:text-gray-400">example.com</span>
            <span class="text-gray-400"> > </span>
            <span class="text-gray-600 dark:text-gray-400">exemple-article</span>
        </div>

        {{-- Title --}}
        <div
            class="text-xl leading-snug"
            :class="title ? 'text-blue-700 dark:text-blue-400' : 'text-gray-300 dark:text-gray-600'"
            x-text="title ? truncate(title, mode === 'mobile' ? 55 : 60) : 'Template titre meta...'"
        ></div>

        {{-- Description --}}
        <div
            class="text-sm leading-relaxed"
            :class="description ? 'text-gray-600 dark:text-gray-400' : 'text-gray-300 dark:text-gray-600'"
            x-text="description ? truncate(description, mode === 'mobile' ? 120 : 160) : 'Template description meta...'"
        ></div>
    </div>

    {{-- Counters --}}
    <div class="mt-3 flex gap-4 text-xs">
        <span :class="titleColor">
            Title : <span x-text="titleLength"></span>/60
        </span>
        <span :class="descColor">
            Description : <span x-text="descLength"></span>/160
        </span>
    </div>

    <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">
        Utilisez <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">@{{title}}</code> et <code class="rounded bg-gray-100 px-1 dark:bg-gray-800">@{{site}}</code> comme variables dans vos templates.
    </p>
</div>
