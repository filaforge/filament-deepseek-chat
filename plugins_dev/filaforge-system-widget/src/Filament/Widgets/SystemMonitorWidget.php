<?php

namespace Filaforge\SystemWidget\Filament\Widgets;

use Filament\Widgets\Widget;
use Filaforge\SystemWidget\Services\SystemMetricsProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class SystemMonitorWidget extends Widget
{
    protected string $view = 'system-widget::widgets.system-monitor';

    /** @var array<string, mixed> */
    public array $metrics = [];

    protected static ?string $heading = null;

    public function mount(SystemMetricsProvider $provider)
    {
        $this->metrics = $provider->collect();
    }

    public function updateMetrics(SystemMetricsProvider $provider)
    {
        $this->metrics = $provider->collect();
    }

    public static function canView(): bool
    {
        $user = auth()->user();
        if (!$user instanceof Authenticatable) {
            return false;
        }

    $allowed = (array) config('system-widget.allow_roles', []);
        if (empty($allowed)) {
            return true;
        }

        if (method_exists($user, 'hasAnyRole')) {
            return $user->hasAnyRole($allowed);
        }

        $userRole = data_get($user, 'role');
        return $userRole ? in_array($userRole, $allowed, true) : false;
    }

    protected function getHeading(): string
    {
        return trans('system-widget::widget.title');
    }
}
