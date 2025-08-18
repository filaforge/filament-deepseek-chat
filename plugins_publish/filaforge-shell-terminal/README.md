# Filaforge Shell Terminal

A powerful and secure shell terminal plugin for FilamentPHP with local assets, enhanced security, and comprehensive configuration options.

## 🚀 Features

- **🔒 Enhanced Security**: Comprehensive command filtering and rate limiting
- **💻 Full Terminal Experience**: Xterm.js-based terminal with local assets
- **⌨️ Smart Input**: Tab completion, command history, and keyboard shortcuts
- **🎨 Modern UI**: Responsive design with dark/light mode support
- **⚙️ Configurable**: Extensive configuration options for customization
- **📱 Responsive**: Works seamlessly on all device sizes
- **🔧 Preset Commands**: Quick access to common Laravel and system commands
- **📊 Command Logging**: Optional audit trail for security compliance

## 📋 Requirements

- **PHP**: 8.1 or higher
- **Laravel**: 12.x
- **Filament**: 4.x (Panel Builder)
- **Node.js**: 16+ (for asset building)

## 🚀 Installation

### Via Composer

```bash
composer require filaforge/shell-terminal
```

### Manual Installation

1. Clone this repository to your `plugins` directory
2. Install dependencies: `composer install`
3. Build assets: `npm install && npm run build`
4. Register the plugin in your panel provider

## ⚙️ Configuration

### Plugin Registration

Add the plugin to your panel provider:

```php
use Filaforge\ShellTerminal\FilaforgeShellTerminalPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugin(FilaforgeShellTerminalPlugin::make());
}
```

### Environment Variables

```env
# Shell Terminal Configuration
SHELL_TERMINAL_ENABLED=true
SHELL_TERMINAL_RATE_LIMIT=60
SHELL_TERMINAL_TIMEOUT=300
SHELL_TERMINAL_MAX_HISTORY=100
SHELL_TERMINAL_LOG_COMMANDS=false
SHELL_TERMINAL_REQUIRE_CONFIRMATION=true
SHELL_TERMINAL_SHOW_WELCOME=true
SHELL_TERMINAL_TAB_COMPLETION=true
SHELL_TERMINAL_HISTORY=true
SHELL_TERMINAL_HEIGHT=60
SHELL_TERMINAL_DARK_MODE=true
```

### Publish Configuration

```bash
php artisan vendor:publish --tag="shell-terminal-config"
```

## 🎯 Usage

### Accessing the Terminal

1. Navigate to your Filament panel
2. Look for "Shell Terminal" in the navigation
3. Click to access the terminal interface

### Basic Commands

- **Enter**: Execute command
- **Tab**: Auto-completion
- **↑/↓**: Navigate command history
- **Ctrl+L**: Clear screen
- **Ctrl+C**: Cancel current command

### Preset Commands

The plugin includes categorized preset commands:

- **Laravel**: Artisan commands, migrations, testing
- **Composer**: Package management
- **Git**: Version control operations
- **System**: File operations, monitoring
- **Database**: Database management
- **Optimization**: Performance tuning
- **Maintenance**: System maintenance

## 🔒 Security Features

### Command Filtering

- **Disallowed Commands**: Built-in protection against dangerous commands
- **Pattern Matching**: Blocks shell piping and other risky patterns
- **Directory Restrictions**: Limits command execution to safe directories

### Rate Limiting

- **Per-User Limits**: Configurable commands per minute
- **Abuse Prevention**: Automatic blocking of excessive usage
- **Configurable Windows**: Adjustable time windows

### Audit Logging

- **Command Logging**: Optional logging of all executed commands
- **User Tracking**: Associate commands with authenticated users
- **Output Logging**: Configurable output logging for compliance

## 🎨 Customization

### CSS Variables

```css
.ff-shell-terminal {
    --terminal-bg: #0a0a0c;
    --terminal-fg: #ffffff;
    --terminal-cursor: #ffffff;
    --terminal-selection: #264f78;
    --terminal-border: #374151;
}
```

### Configuration Options

- **Terminal Height**: Adjust viewport height
- **Theme Support**: Dark/light mode switching
- **Font Settings**: Customize terminal fonts
- **Color Schemes**: Customize terminal colors

## 🛠️ Development

### Building Assets

```bash
# Install dependencies
npm install

# Development build
npm run dev

# Production build
npm run build

# Watch for changes
npm run watch
```

### Local Development

1. Clone the repository
2. Install dependencies: `composer install && npm install`
3. Link to your Laravel project for testing
4. Build assets: `npm run build`

## 📁 File Structure

```
filaforge-shell-terminal/
├── src/                          # PHP source code
│   ├── Pages/                    # Filament pages
│   ├── Providers/                # Service providers
│   └── FilaforgeShellTerminalPlugin.php
├── resources/                    # Frontend assets
│   ├── css/                      # Stylesheets
│   ├── js/                       # JavaScript
│   ├── views/                    # Blade templates
│   └── dist/                     # Compiled assets
├── config/                       # Configuration files
├── database/                     # Migrations
├── bin/                          # Build scripts
├── composer.json                 # PHP dependencies
├── package.json                  # Node.js dependencies
└── README.md                     # This file
```

## 🔧 Configuration Reference

### General Settings

| Setting | Default | Description |
|---------|---------|-------------|
| `enabled` | `true` | Enable/disable terminal |
| `rate_limit` | `60` | Commands per minute |
| `command_timeout` | `300` | Execution timeout (seconds) |
| `max_history` | `100` | Maximum command history |

### Security Settings

| Setting | Default | Description |
|---------|---------|-------------|
| `log_commands` | `false` | Log executed commands |
| `require_confirmation` | `true` | Require confirmation |
| `disallowed_commands` | `[]` | Blocked command list |
| `allowed_directories` | `[base_path()]` | Safe directories |

### Display Settings

| Setting | Default | Description |
|---------|---------|-------------|
| `show_welcome_message` | `true` | Show welcome message |
| `enable_tab_completion` | `true` | Enable tab completion |
| `enable_command_history` | `true` | Enable history navigation |
| `terminal_height` | `60` | Height in viewport units |
| `dark_mode` | `true` | Use dark theme |

## 🧪 Testing

```bash
# Run tests
composer test

# Run with coverage
composer test -- --coverage
```

## 📞 Support

- **Documentation**: This README
- **Issues**: GitHub Issues
- **Email**: filaforger@gmail.com

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- **FilamentPHP Team** for the excellent admin panel framework
- **Xterm.js** for the terminal emulator
- **Laravel Community** for the robust PHP framework

---

**Made with ❤️ by the Filaforge Team**
