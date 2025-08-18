<?php

namespace Filaforge\WirechatDashboard\Pages;

use Filament\Pages\Page;

class Profile extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static string $view = 'wirechat-dashboard::pages.profile';
    protected static ?string $title = 'Profile';
    protected static ?int $navigationSort = 2;
}