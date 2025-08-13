---
title: Getting started
---
import Aside from "@components/Aside.astro"

## Introduction

While Filament comes with virtually any tool you'll need to build great apps, sometimes you'll need to add your own functionality either for just your app or as redistributable packages that other developers can include in their own apps. This is why Filament offers a plugin system that allows you to extend its functionality.

Before we dive in, it's important to understand the different contexts in which plugins can be used. There are two main contexts:

1. **Panel Plugins**: These are plugins that are used with [Panel Builders](../introduction/installation). They are typically used only to add functionality when used inside a Panel or as a complete Panel in and of itself. Examples of this are:
   1. A plugin that adds specific functionality to the dashboard in the form of Widgets.
   2. A plugin that adds a set of Resources / functionality to an app like a Blog or User Management feature.
2. **Standalone Plugins**: These are plugins that are used in any context outside a Panel Builder. Examples of this are:
   1. A plugin that adds custom fields to be used with the [Form Builders](../forms/installation/).
   2. A plugin that adds custom columns or filters to the [Table Builders](../tables/installation/).

Although these are two different mental contexts to keep in mind when building plugins, they can be used together inside the same plugin. They do not have to be mutually exclusive.

## Important concepts

Before we dive into the specifics of building plugins, there are a few concepts that are important to understand. You should familiarize yourself with the following before building a plugin:

1. [Laravel Package Development](https://laravel.com/docs/packages)
2. [Spatie Package Tools](https://github.com/spatie/laravel-package-tools)
3. [Filament Asset Management](../advanced/assets)

### The Plugin object

Filament introduces the concept of a Plugin object that is used to configure the plugin. This object is a simple PHP class that implements the `Filament\Contracts\Plugin` interface. This class is used to configure the plugin and is the main entry point for the plugin. It is also used to register Resources and Icons that might be used by your plugin.

While the plugin object is extremely helpful, it is not required to build a plugin. You can still build plugins without using the plugin object as you can see in the [building a panel plugin](building-a-panel-plugin) tutorial.

<Aside variant="info">
   The Plugin object is only used for Panel Providers. Standalone Plugins do not use this object. All configuration for Standalone Plugins should be handled in the plugin's service provider.
</Aside>

### Registering assets

All [asset registration](../advanced/assets), including CSS, JS and Alpine Components, should be done through the plugin's service provider in the `packageBooted()` method. This allows Filament to register the assets with the Asset Manager and load them when needed.

## Creating a plugin

While you can certainly build plugins from scratch, we recommend using the [Filament Plugin Skeleton](https://github.com/filamentphp/plugin-skeleton) to quickly get started. This skeleton includes all the necessary boilerplate to get you up and running quickly.

### Usage

To use the skeleton, simply go to the GitHub repo and click the "Use this template" button. This will create a new repo in your account with the skeleton code. After that, you can clone the repo to your machine. Once you have the code on your machine, navigate to the root of the project and run the following command:

```bash
php ./configure.php
```

This will ask you a series of questions to configure the plugin. Once you've answered all the questions, the script will stub out a new plugin for you, and you can begin to build your amazing new extension for Filament.

## Upgrading existing plugins

Since every plugin varies greatly in its scope of use and functionality, there is no one size fits all approaches to upgrading existing plugins. However, one thing to note, that is consistent to all plugins is the deprecation of the `PluginServiceProvider`.

In your plugin service provider, you will need to change it to extend the PackageServiceProvider instead. You will also need to add a static `$name` property to the service provider. This property is used to register the plugin with Filament. Here is an example of what your service provider might look like:

```php
class MyPluginServiceProvider extends PackageServiceProvider
{
    public static string $name = 'my-plugin';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name);
    }
}
```

### Helpful links

Please read this guide in its entirety before upgrading your plugin. It will help you understand the concepts and how to build your plugin.

1. [Filament Asset Management](../advanced/assets)
2. [Panel Plugin Development](../panels/plugins)
3. [Icon Management](../styling/icons)
4. [Colors Management](../styling/colors)
5. [CSS Hooks](../styling/css-hooks)



```


---
title: Plugin development
---

## Introduction

The basis of Filament plugins are Laravel packages. They are installed into your Filament project via Composer, and follow all the standard techniques, like using service providers to register routes, views, and translations. If you're new to Laravel package development, here are some resources that can help you grasp the core concepts:

- [The Package Development section of the Laravel docs](https://laravel.com/docs/packages) serves as a great reference guide.
- [Spatie's Package Training course](https://spatie.be/products/laravel-package-training) is a good instructional video series to teach you the process step by step.
- [Spatie's Package Tools](https://github.com/spatie/laravel-package-tools) allows you to simplify your service provider classes using a fluent configuration object.

Filament plugins build on top of the concepts of Laravel packages and allow you to ship and consume reusable features for any Filament panel. They can be added to each panel one at a time, and are also configurable differently per-panel.

## Configuring the panel with a plugin class

A plugin class is used to allow your package to interact with a panel [configuration](../panel-configuration) file. It's a simple PHP class that implements the `Plugin` interface. 3 methods are required:

- The `getId()` method returns the unique identifier of the plugin amongst other plugins. Please ensure that it is specific enough to not clash with other plugins that might be used in the same project.
- The `register()` method allows you to use any [configuration](../panel-configuration) option that is available to the panel. This includes registering [resources](resources), [custom pages](../navigation/custom-pages), [themes](themes), [render hooks](../panel-configuration#render-hooks) and more.
- The `boot()` method is run only when the panel that the plugin is being registered to is actually in-use. It is executed by a middleware class.

```php
<?php

namespace DanHarrin\FilamentBlog;

use DanHarrin\FilamentBlog\Pages\Settings;
use DanHarrin\FilamentBlog\Resources\CategoryResource;
use DanHarrin\FilamentBlog\Resources\PostResource;
use Filament\Contracts\Plugin;
use Filament\Panel;

class BlogPlugin implements Plugin
{
    public function getId(): string
    {
        return 'blog';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                PostResource::class,
                CategoryResource::class,
            ])
            ->pages([
                Settings::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
```

The users of your plugin can add it to a panel by instantiating the plugin class and passing it to the `plugin()` method of the [configuration](../panel-configuration):

```php
use DanHarrin\FilamentBlog\BlogPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugin(new BlogPlugin());
}
```

### Fluently instantiating the plugin class

You may want to add a `make()` method to your plugin class to provide a fluent interface for your users to instantiate it. In addition, by using the container (`app()`) to instantiate the plugin object, it can be replaced with a different implementation at runtime:

```php
use Filament\Contracts\Plugin;

class BlogPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }
    
    // ...
}
```

Now, your users can use the `make()` method:

```php
use DanHarrin\FilamentBlog\BlogPlugin;
use Filament\Panel;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugin(BlogPlugin::make());
}
```

### Configuring plugins per-panel

You may add other methods to your plugin class, which allow your users to configure it. We suggest that you add both a setter and a getter method for each option you provide. You should use a property to store the preference in the setter and retrieve it again in the getter:

```php
use DanHarrin\FilamentBlog\Resources\AuthorResource;
use Filament\Contracts\Plugin;
use Filament\Panel;

class BlogPlugin implements Plugin
{
    protected bool $hasAuthorResource = false;
    
    public function authorResource(bool $condition = true): static
    {
        // This is the setter method, where the user's preference is
        // stored in a property on the plugin object.
        $this->hasAuthorResource = $condition;
    
        // The plugin object is returned from the setter method to
        // allow fluent chaining of configuration options.
        return $this;
    }
    
    public function hasAuthorResource(): bool
    {
        // This is the getter method, where the user's preference
        // is retrieved from the plugin property.
        return $this->hasAuthorResource;
    }
    
    public function register(Panel $panel): void
    {
        // Since the `register()` method is executed after the user
        // configures the plugin, you can access any of their
        // preferences inside it.
        if ($this->hasAuthorResource()) {
            // Here, we only register the author resource on the
            // panel if the user has requested it.
            $panel->resources([
                AuthorResource::class,
            ]);
        }
    }
    
    // ...
}
```

Additionally, you can use the unique ID of the plugin to access any of its configuration options from outside the plugin class. To do this, pass the ID to the `filament()` method:

```php
filament('blog')->hasAuthorResource()
```

You may wish to have better type safety and IDE autocompletion when accessing configuration. It's completely up to you how you choose to achieve this, but one idea could be adding a static method to the plugin class to retrieve it:

```php
use Filament\Contracts\Plugin;

class BlogPlugin implements Plugin
{
    public static function get(): static
    {
        return filament(app(static::class)->getId());
    }
    
    // ...
}
```

Now, you can access the plugin configuration using the new static method:

```php
BlogPlugin::get()->hasAuthorResource()
```

## Distributing a panel in a plugin

It's very easy to distribute an entire panel in a Laravel package. This way, a user can simply install your plugin and have an entirely new part of their app pre-built.

When [configuring](../panel-configuration) a panel, the configuration class extends the `PanelProvider` class, and that is a standard Laravel service provider. You can use it as a service provider in your package:

```php
<?php

namespace DanHarrin\FilamentBlog;

use Filament\Panel;
use Filament\PanelProvider;

class BlogPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('blog')
            ->path('blog')
            ->resources([
                // ...
            ])
            ->pages([
                // ...
            ])
            ->widgets([
                // ...
            ])
            ->middleware([
                // ...
            ])
            ->authMiddleware([
                // ...
            ]);
    }
}
```

You should then register it as a service provider in the `composer.json` of your package:

```json
"extra": {
    "laravel": {
        "providers": [
            "DanHarrin\\FilamentBlog\\BlogPanelProvider"
        ]
    }
}
```

---
title: Build a panel plugin
---
import Aside from "@components/Aside.astro"

## Preface

Please read the docs on [panel plugin development](../panels/plugins) and the [getting started guide](getting-started) before continuing.

## Introduction

In this walkthrough, we'll build a simple plugin that adds a new form field that can be used in forms. This also means it will be available to users in their panels.

You can find the final code for this plugin at [https://github.com/awcodes/clock-widget](https://github.com/awcodes/clock-widget).

## Step 1: Create the plugin

First, we'll create the plugin using the steps outlined in the [getting started guide](getting-started#creating-a-plugin).

## Step 2: Clean up

Next, we'll clean up the plugin to remove the boilerplate code we don't need. This will seem like a lot, but since this is a simple plugin, we can remove a lot of the boilerplate code.

Remove the following directories and files:
1. `config`
1. `database`
1. `src/Commands`
1. `src/Facades`
1. `stubs`

Since our plugin doesn't have any settings or additional methods needed for functionality, we can also remove the `ClockWidgetPlugin.php` file.

1. `ClockWidgetPlugin.php`

Since Filament recommends that users style their plugins with a custom filament theme, we'll remove the files needed for using CSS in the plugin. This is optional, and you can still use CSS if you want, but it is not recommended.

1. `resources/css`
2. `postcss.config.js`

Now we can clean up our `composer.json` file to remove unneeded options.

```json
"autoload": {
    "psr-4": {
        // We can remove the database factories
        "Awcodes\\ClockWidget\\Database\\Factories\\": "database/factories/"
    }
},
"extra": {
    "laravel": {
        // We can remove the facade
        "aliases": {
            "ClockWidget": "Awcodes\\ClockWidget\\Facades\\ClockWidget"
        }
    }
},
```

The last step is to update the `package.json` file to remove unneeded options. Replace the contents of `package.json` with the following.

```json
{
    "private": true,
    "type": "module",
    "scripts": {
        "dev": "node bin/build.js --dev",
        "build": "node bin/build.js"
    },
    "devDependencies": {
        "esbuild": "^0.17.19"
    }
}
```

Then we need to install our dependencies.

```bash
npm install
```

You may also remove the Testing directories and files, but we'll leave them in for now, although we won't be using them for this example, and we highly recommend that you write tests for your plugins.

## Step 3: Setting up the provider

Now that we have our plugin cleaned up, we can start adding our code. The boilerplate in the `src/ClockWidgetServiceProvider.php` file has a lot going on so, let's delete everything and start from scratch.

<Aside variant="info">
    In this example, we will be registering an [async Alpine component](../assets#asynchronous-alpinejs-components). Since these assets are only loaded on request, we can register them as normal in the `packageBooted()` method. If you are registering assets, like CSS or JS files, that get loaded on every page regardless of if they are used or not, you should register them in the `register()` method of the `Plugin` configuration object, using [`$panel->assets()`](../panel-configuration#registering-assets-for-a-panel). Otherwise, if you register them in the `packageBooted()` method, they will be loaded in every panel, regardless of whether or not the plugin has been registered for that panel.
</Aside>

We need to be able to register our Widget with the panel and load our Alpine component when the widget is used. To do this, we'll need to add the following to the `packageBooted` method in our service provider. This will register our widget component with Livewire and our Alpine component with the Filament Asset Manager.

```php
use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Facades\FilamentAsset;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ClockWidgetServiceProvider extends PackageServiceProvider
{
    public static string $name = 'clock-widget';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasViews()
            ->hasTranslations();
    }

    public function packageBooted(): void
    {
        Livewire::component('clock-widget', ClockWidget::class);

        // Asset Registration
        FilamentAsset::register(
            assets:[
                 AlpineComponent::make('clock-widget', __DIR__ . '/../resources/dist/clock-widget.js'),
            ],
            package: 'awcodes/clock-widget'
        );
    }
}
```

## Step 4: Create the widget

Now we can create our widget. We'll first need to extend Filament's `Widget` class in our `ClockWidget.php` file and tell it where to find the view for the widget. Since we are using the PackageServiceProvider to register our views, we can use the `::` syntax to tell Filament where to find the view.

```php
use Filament\Widgets\Widget;

class ClockWidget extends Widget
{
    protected static string $view = 'clock-widget::widget';
}
```

Next, we'll need to create the view for our widget. Create a new file at `resources/views/widget.blade.php` and add the following code. We'll make use of Filament's blade components to save time on writing the html for the widget.

We are using async Alpine to load our Alpine component, so we'll need to add the `x-load` attribute to the div to tell Alpine to load our component. You can learn more about this in the [Core Concepts](../advanced/assets#asynchronous-alpinejs-components) section of the docs.

```blade
<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            {{ __('clock-widget::clock-widget.title') }}
        </x-slot>

        <div
            x-load
            x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('clock-widget', 'awcodes/clock-widget') }}"
            x-data="clockWidget()"
            class="text-center"
        >
            <p>{{ __('clock-widget::clock-widget.description') }}</p>
            <p class="text-xl" x-text="time"></p>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
```

Next, we need to write our Alpine component in `src/js/index.js`. And build our assets with `npm run build`.

```js
export default function clockWidget() {
    return {
        time: new Date().toLocaleTimeString(),
        init() {
            setInterval(() => {
                this.time = new Date().toLocaleTimeString();
            }, 1000);
        }
    }
}
```

We should also add translations for the text in the widget so users can translate the widget into their language. We'll add the translations to `resources/lang/en/widget.php`.

```php
return [
    'title' => 'Clock Widget',
    'description' => 'Your current time is:',
];
```

## Step 5: Update your README

You'll want to update your `README.md` file to include instructions on how to install your plugin and any other information you want to share with users, Like how to use it in their projects. For example:

```php
// Register the plugin and/or Widget in your Panel provider:

use Awcodes\ClockWidget\ClockWidgetWidget;

public function panel(Panel $panel): Panel
{
    return $panel
        ->widgets([
            ClockWidgetWidget::class,
        ]);
}
```

And, that's it, our users can now install our plugin and use it in their projects.

}


```

---
title: Build a standalone plugin
---

## Preface

Please read the docs on [panel plugin development](../panels/plugins/) and the [getting started guide](getting-started) before continuing.

## Introduction

In this walkthrough, we'll build a simple plugin that adds a new form component that can be used in forms. This also means it will be available to users in their panels.

You can find the final code for this plugin at [https://github.com/awcodes/headings](https://github.com/awcodes/headings).

## Step 1: Create the plugin

First, we'll create the plugin using the steps outlined in the [getting started guide](getting-started#creating-a-plugin).

## Step 2: Clean up

Next, we'll clean up the plugin to remove the boilerplate code we don't need. This will seem like a lot, but since this is a simple plugin, we can remove a lot of the boilerplate code.

Remove the following directories and files:
1. `bin`
2. `config`
3. `database`
4. `src/Commands`
5. `src/Facades`
6. `stubs`

Now we can clean up our `composer.json` file to remove unneeded options.

```json
"autoload": {
    "psr-4": {
        // We can remove the database factories
        "Awcodes\\Headings\\Database\\Factories\\": "database/factories/"
    }
},
"extra": {
    "laravel": {
        // We can remove the facade
        "aliases": {
            "Headings": "Awcodes\\Headings\\Facades\\ClockWidget"
        }
    }
},
```

Normally, Filament recommends that users style their plugins with a custom filament theme, but for the sake of example let's provide our own stylesheet that can be loaded asynchronously using the new `x-load` features in Filament v3. So, let's update our `package.json` file to include `cssnano`, `postcss`, `postcss-cli` and `postcss-nesting` to build our stylesheet.

```json
{
    "private": true,
    "scripts": {
        "build": "postcss resources/css/index.css -o resources/dist/headings.css"
    },
    "devDependencies": {
        "cssnano": "^6.0.1",
        "postcss": "^8.4.27",
        "postcss-cli": "^10.1.0",
        "postcss-nesting": "^13.0.0"
    }
}
```

Then we need to install our dependencies.

```bash
npm install
```

We will also need to update our `postcss.config.js` file to configure postcss.

```js
module.exports = {
    plugins: [
        require('postcss-nesting')(),
        require('cssnano')({
            preset: 'default',
        }),
    ],
};
```

You may also remove the testing directories and files, but we'll leave them in for now, although we won't be using them for this example, and we highly recommend that you write tests for your plugins.

## Step 3: Setting up the provider

Now that we have our plugin cleaned up, we can start adding our code. The boilerplate in the `src/HeadingsServiceProvider.php` file has a lot going on so, let's delete everything and start from scratch.

We need to be able to register our stylesheet with the Filament Asset Manager so that we can load it on demand in our blade view. To do this, we'll need to add the following to the `packageBooted` method in our service provider.

***Note the `loadedOnRequest()` method. This is important, because it tells Filament to only load the stylesheet when it's needed.***

```php
namespace Awcodes\Headings;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class HeadingsServiceProvider extends PackageServiceProvider
{
    public static string $name = 'headings';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasViews();
    }

    public function packageBooted(): void
    {
        FilamentAsset::register([
            Css::make('headings', __DIR__ . '/../resources/dist/headings.css')->loadedOnRequest(),
        ], 'awcodes/headings');
    }
}
```

## Step 4: Creating our component

Next, we'll need to create our component. Create a new file at `src/Heading.php` and add the following code.

```php
namespace Awcodes\Headings;

use Closure;
use Filament\Schemas\Components\Component;
use Filament\Support\Colors\Color;
use Filament\Support\Concerns\HasColor;

class Heading extends Component
{
    use HasColor;

    protected string | int $level = 2;

    protected string | Closure $content = '';

    protected string $view = 'headings::heading';

    final public function __construct(string | int $level)
    {
        $this->level($level);
    }

    public static function make(string | int $level): static
    {
        return app(static::class, ['level' => $level]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->dehydrated(false);
    }

    public function content(string | Closure $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function level(string | int $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getColor(): array
    {
        return $this->evaluate($this->color) ?? Color::Amber;
    }

    public function getContent(): string
    {
        return $this->evaluate($this->content);
    }

    public function getLevel(): string
    {
        return is_int($this->level) ? 'h' . $this->level : $this->level;
    }
}
```

## Step 5: Rendering our component

Next, we'll need to create the view for our component. Create a new file at `resources/views/heading.blade.php` and add the following code.

We are using x-load to asynchronously load stylesheet, so it's only loaded when necessary. You can learn more about this in the [Core Concepts](../advanced/assets#lazy-loading-css) section of the docs.

```blade
@php
    $level = $getLevel();
    $color = $getColor();
@endphp

<{{ $level }}
    x-data
    x-load-css="[@js(\Filament\Support\Facades\FilamentAsset::getStyleHref('headings', package: 'awcodes/headings'))]"
    {{
        $attributes
            ->class([
                'headings-component',
                match ($color) {
                    'gray' => 'text-gray-600 dark:text-gray-400',
                    default => 'text-custom-500',
                },
            ])
            ->style([
                \Filament\Support\get_color_css_variables($color, [500]) => $color !== 'gray',
            ])
    }}
>
    {{ $getContent() }}
</{{ $level }}>
```

## Step 6: Adding some styles

Next, let's provide some custom styling for our field. We'll add the following to `resources/css/index.css`. And run `npm run build` to compile our CSS.

```css
.headings-component {
    &:is(h1, h2, h3, h4, h5, h6) {
         font-weight: 700;
         letter-spacing: -.025em;
         line-height: 1.1;
     }

    &h1 {
         font-size: 2rem;
     }

    &h2 {
         font-size: 1.75rem;
     }

    &h3 {
         font-size: 1.5rem;
     }

    &h4 {
         font-size: 1.25rem;
     }

    &h5,
    &h6 {
         font-size: 1rem;
     }
}
```

Then we need to build our stylesheet.

```bash
npm run build
```

## Step 7: Update your README

You'll want to update your `README.md` file to include instructions on how to install your plugin and any other information you want to share with users, Like how to use it in their projects. For example:

```php
use Awcodes\Headings\Heading;

Heading::make(2)
    ->content('Product Information')
    ->color(Color::Lime),
```

And, that's it, our users can now install our plugin and use it in their projects.
