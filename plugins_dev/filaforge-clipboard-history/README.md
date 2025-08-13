## Filaforge Clipboard History

A Filament widget that stores the last few snippets you add, so you can quickly copy/paste from a history dropdown. Data is stored in localStorage.

Usage:

```php
->plugin(\Filaforge\ClipboardHistory\ClipboardHistoryPlugin::make())
```

Configuration:

Publish the config and set the maximum number of items (default 10):

```php
return [ 'max_items' => 10 ];
```



