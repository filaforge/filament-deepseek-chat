<?php

namespace Filaforge\HuggingfaceChat\Pages;

use Filament\Pages\Page;

class HfConversationsPage extends Page
{
	protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-table-cells';
	protected string $view = 'huggingface-chat::pages.conversations';
	protected static ?string $navigationLabel = 'HF Conversations';
	protected static \UnitEnum|string|null $navigationGroup = 'System';
	protected static ?int $navigationSort = 12;
	protected static ?string $title = 'HF Conversations';
}


