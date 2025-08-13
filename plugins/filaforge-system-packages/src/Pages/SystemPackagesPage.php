<?php

namespace Filaforge\SystemPackages\Pages;

use Filament\Pages\Page;

class SystemPackagesPage extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static \UnitEnum|string|null $navigationGroup = 'System';

    protected static ?string $title = 'System Packages';

    /**
     * @return array<class-string<\Filament\Widgets\Widget> | \Filament\Widgets\WidgetConfiguration>
     */
    protected function getHeaderWidgets(): array
    {
        return [\Filaforge\SystemPackages\Widgets\SystemPackagesWidget::class];
    }
}
