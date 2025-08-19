<?php

namespace Filaforge\QuickLinks\Widgets;

use Filaforge\QuickLinks\Models\DashboardBookmark;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

class QuickLinksWidget extends Widget
{
    protected string $view = 'quick-links::widget';

    public array $data = [
        'label' => '',
        'url' => '',
    ];

    public function add(): void
    {
        $label = trim((string) ($this->data['label'] ?? ''));
        $url = trim((string) ($this->data['url'] ?? ''));

        if ($label === '' || $url === '' || ! auth()->id()) {
            Notification::make()->title('Label and URL are required')->danger()->send();
            return;
        }

        $maxOrder = (int) DashboardBookmark::where('user_id', auth()->id())->max('order');

        DashboardBookmark::create([
            'user_id' => auth()->id(),
            'label' => $label,
            'url' => $url,
            'order' => $maxOrder + 1,
        ]);

        $this->data = ['label' => '', 'url' => ''];
        Notification::make()->title('Bookmark added')->success()->send();
    }

    public function delete(int $id): void
    {
        DashboardBookmark::where('user_id', auth()->id())->where('id', $id)->delete();
        Notification::make()->title('Bookmark removed')->success()->send();
    }

    public function getBookmarksProperty()
    {
        return DashboardBookmark::where('user_id', auth()->id())
            ->orderBy('order')
            ->get();
    }

    public static function canView(): bool
    {
        return auth()->check();
    }
}



