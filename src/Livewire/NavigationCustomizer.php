<?php

namespace Alexisgt01\CmsCore\Livewire;

use Alexisgt01\CmsCore\Models\SiteSetting;
use Illuminate\View\View;
use Livewire\Component;

class NavigationCustomizer extends Component
{
    public array $features = [];

    public function mount(): void
    {
        $stored = [];

        try {
            $stored = SiteSetting::instance()->features ?? [];
        } catch (\Throwable) {
        }

        $defaults = config('cms-features', []);

        foreach ($defaults as $key => $default) {
            $this->features[$key] = $stored[$key] ?? $default;
        }
    }

    public function save(array $features): void
    {
        $settings = SiteSetting::instance();
        $settings->features = $features;
        $settings->save();

        $this->redirect(request()->url());
    }

    public function render(): View
    {
        return view('cms-core::livewire.navigation-customizer');
    }
}
