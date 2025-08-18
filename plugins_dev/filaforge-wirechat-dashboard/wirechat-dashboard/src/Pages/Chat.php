<?php

namespace Filaforge\WirechatDashboard\Pages;

use Filament\Pages\Page;

class Chat extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static string $view = 'wirechat-dashboard::pages.chat';
    protected static ?string $title = 'Chat';
    protected static ?int $navigationSort = 1;
}