<?php

namespace Filaforge\WirechatDashboard\Pages;

use Filament\Pages\Page;

class Settings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static string $view = 'wirechat-dashboard::pages.settings';
    protected static ?string $title = 'Settings';
    protected static ?int $navigationSort = 3;
}