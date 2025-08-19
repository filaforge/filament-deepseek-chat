# 🚀 Quick Reference Guide

## 📋 Essential Commands

### Installation
```bash
# Quick install via Composer
composer require filaforge/shell-terminal

# Manual setup (if downloaded)
cd plugins_publish/filaforge-shell-terminal
./setup.sh                    # Linux/Mac
setup.bat                     # Windows
```

### Asset Building
```bash
# Install dependencies
npm install

# Build for production
npm run build

# Development with watch
npm run dev
npm run watch
```

### Laravel Commands
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan filament:cache-components

# Check routes
php artisan route:list | grep terminal
```

### Plugin Management
```bash
# Check if installed
composer show filaforge/shell-terminal

# Update plugin
composer update filaforge/shell-terminal

# Remove plugin
composer remove filaforge/shell-terminal
```

## ⚙️ Configuration

### Environment Variables (.env)
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

### Panel Provider Registration
```php
use Filaforge\ShellTerminal\FilaforgeShellTerminalPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugin(FilaforgeShellTerminalPlugin::make());
}
```

## 🔍 Troubleshooting

### Common Issues

| Issue | Solution |
|-------|----------|
| Plugin not found | `composer dump-autoload` |
| Class not found | `composer clear-cache && composer dump-autoload` |
| Assets not loading | `npm run build` in plugin directory |
| Terminal not working | Check database migration and config |
| Permission denied | `chmod -R 755 plugins/filaforge-shell-terminal/` |

### Debug Commands
```bash
# Check PHP version
php --version

# Check Composer
composer --version

# Check Node.js
node --version

# Check database connection
php artisan tinker
>>> DB::connection()->getPdo()

# List all providers
php artisan config:show app.providers
```

## 📁 File Structure

```
filaforge-shell-terminal/
├── src/                          # PHP source code
├── resources/                    # Views, CSS, JS
├── config/                       # Configuration files
├── database/                     # Migrations
├── bin/                          # Build scripts
├── composer.json                 # Package configuration
├── package.json                  # Node.js dependencies
├── setup.sh/setup.bat           # Setup scripts
├── README.md                     # Main documentation
└── QUICK_REFERENCE.md            # This file
```

## 🎯 Quick Start Checklist

- [ ] Install plugin: `composer require filaforge/shell-terminal`
- [ ] Register in panel provider: `->plugin(FilaforgeShellTerminalPlugin::make())`
- [ ] Run migration: `php artisan migrate`
- [ ] Clear caches: `php artisan cache:clear`
- [ ] Access plugin in Filament panel
- [ ] Test terminal functionality
- [ ] Configure security settings

## 📞 Support

- **Email**: filaforger@gmail.com
- **Documentation**: README.md
- **Quick Help**: This reference guide

## 🔗 Related Files

- **README.md** - Main documentation and features
- **setup.sh/setup.bat** - Automated setup scripts
- **composer.json** - Package dependencies
- **package.json** - Node.js dependencies

---

**💡 Tip**: Run `./setup.sh` (Linux/Mac) or `setup.bat` (Windows) for automated installation!
