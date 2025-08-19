# Ollama Chat Plugin

## Overview
The Ollama Chat Plugin is designed to provide a seamless chat experience within your application. It integrates with the Ollama API to facilitate real-time messaging and conversation management.

## Features
- Real-time chat functionality
- Conversation and message management
- User settings for personalized experiences
- Admin panel integration for managing conversations and settings

## Installation
To install the Ollama Chat Plugin, follow these steps:

1. **Clone the repository:**
   ```bash
   git clone <repository-url>
   cd filaforge-ollama-chat
   ```

2. **Install dependencies:**
   For PHP dependencies, run:
   ```bash
   composer install
   ```

   For JavaScript dependencies, run:
   ```bash
   npm install
   ```

3. **Run migrations:**
   To set up the database tables, run:
   ```bash
   php artisan migrate
   ```

4. **Publish configuration:**
   Publish the configuration file using:
   ```bash
   php artisan vendor:publish --provider="OllamaChatServiceProvider"
   ```

## Usage
After installation, you can access the chat interface through the designated route in your application. The admin panel provides options to manage conversations and settings.

## Configuration
The configuration file can be found at `config/ollama-chat.php`. You can customize various settings such as API keys and default options.

## Contributing
We welcome contributions! Please refer to the `CONTRIBUTING.md` file for guidelines on how to contribute to this project.

## License
This project is licensed under the terms of the MIT License. See the `LICENSE` file for details.

## Acknowledgments
Thanks to the contributors and the community for their support in developing this plugin.