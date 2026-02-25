<div class="space-y-4 p-4">
    <div class="grid grid-cols-2 gap-4 text-sm">
        <div>
            <span class="font-medium text-gray-500 dark:text-gray-400">Date</span>
            <p class="mt-1">{{ $activity->created_at->format('d/m/Y H:i:s') }}</p>
        </div>
        <div>
            <span class="font-medium text-gray-500 dark:text-gray-400">Action</span>
            <p class="mt-1">{{ $activity->event }}</p>
        </div>
        <div>
            <span class="font-medium text-gray-500 dark:text-gray-400">Modele</span>
            <p class="mt-1">{{ class_basename($activity->subject_type ?? '') }} #{{ $activity->subject_id }}</p>
        </div>
        <div>
            <span class="font-medium text-gray-500 dark:text-gray-400">Utilisateur</span>
            <p class="mt-1">
                @if($activity->causer)
                    {{ $activity->causer->first_name ?? '' }} {{ $activity->causer->last_name ?? $activity->causer->name ?? '' }}
                @else
                    Systeme
                @endif
            </p>
        </div>
    </div>

    @if($activity->properties->has('old'))
        <div>
            <h4 class="font-medium text-sm text-gray-500 dark:text-gray-400 mb-2">Anciennes valeurs</h4>
            <pre class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3 text-xs overflow-auto max-h-64">{{ json_encode($activity->properties->get('old'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    @endif

    @if($activity->properties->has('attributes'))
        <div>
            <h4 class="font-medium text-sm text-gray-500 dark:text-gray-400 mb-2">Nouvelles valeurs</h4>
            <pre class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3 text-xs overflow-auto max-h-64">{{ json_encode($activity->properties->get('attributes'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    @endif
</div>
