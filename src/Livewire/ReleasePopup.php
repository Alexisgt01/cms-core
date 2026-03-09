<?php

namespace Alexisgt01\CmsCore\Livewire;

use Alexisgt01\CmsCore\Services\ReleaseService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ReleasePopup extends Component
{
    public bool $show = false;

    public ?array $release = null;

    public function mount(): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $service = app(ReleaseService::class);
        $latest = $service->getLatestUnreadRelease($user);

        if ($latest) {
            $this->release = $latest;
            $this->show = true;
        }
    }

    public function dismiss(): void
    {
        $user = auth()->user();

        if ($user) {
            app(ReleaseService::class)->markAllAsRead($user);
        }

        $this->show = false;
    }

    public function render(): View
    {
        return view('cms-core::livewire.release-popup');
    }
}
