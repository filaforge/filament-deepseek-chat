<?php

namespace Filaforge\ClipboardHistory\Widgets;

use Filament\Widgets\Widget;

class ClipboardHistoryWidget extends Widget
{
    protected string $view = 'clipboard-history::widget';

    public static function canView(): bool
    {
        return auth()->check();
    }
}


