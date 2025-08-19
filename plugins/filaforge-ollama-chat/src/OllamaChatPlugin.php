<?php

namespace FilaforgeOllamaChat;

use Illuminate\Support\ServiceProvider;

class OllamaChatPlugin
{
    protected $serviceProvider;

    public function __construct()
    {
        $this->serviceProvider = new OllamaChatServiceProvider();
    }

    public function register()
    {
        $this->serviceProvider->register();
    }

    public function boot()
    {
        $this->serviceProvider->boot();
    }
}