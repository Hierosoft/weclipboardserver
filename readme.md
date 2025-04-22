# WeClipboardServer

## Overview
This project is a relay server for synchronizing Minecraft WorldEdit clipboard data between servers. The server uses a secure REST API to upload and retrieve clipboard entries, and is built using the Slim Framework, Doctrine, and Symfony components.

## Features
- **Clipboard Management**: Upload, list, and retrieve clipboard data with UUID, timestamp, and an optional description.
- **API Security**: Only whitelisted IP addresses can interact with specific API endpoints.
- **Mojang Session Authentication**: The API uses Mojang authentication via Minecraft session data.
- **Data Storage**: Clipboard data is stored in an SQLite database and historical entries are kept for reference.
- **Configuration**: API access and IP whitelist settings can be reloaded dynamically via the API.

## Prerequisites
- PHP >= 7.4
- Composer (for dependency management)
- SQLite for data storage

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/Hierosoft/weclipboardserver.git
   ```

2. Install the dependencies using Composer:
   ```bash
   cd weclipboardserver
   composer install
   ```

3. Configure the IP whitelist and server settings:
   - Edit `config/endpoints.json` to specify the IP addresses allowed to interact with the `copy`, `paste`, and `reload` endpoints.

4. Set up the SQLite database:
   ```bash
   php bin/setupDatabase.php
   ```

5. Start the server:
   ```bash
   php public/index.php
   ```

## API Endpoints

### POST /api/clipboard/copy
- **Description**: Upload clipboard data to the server.
- **Authentication**: Minecraft session information must be included in the request.
- **Request Body**:
  ```json
  {
    "uuid": "minecraft-uuid",
    "clipboard_data": "<clipboard-payload>",
    "description": "Optional description of clipboard"
  }
  ```

### POST /api/clipboard/paste
- **Description**: Retrieve clipboard data to be pasted.
- **Authentication**: Minecraft session information must be included in the request.

### GET /api/clipboard/list
- **Description**: List all clipboard entries for the authenticated user.
- **Authentication**: Minecraft session information must be included in the request.
- **Response**:
  ```json
  {
    "entries": [
      {
        "id": 123,
        "date": "2025-04-22T15:30:00Z",
        "description": "Clipboard entry description"
      }
    ]
  }
  ```

### GET /api/clipboard/entry/{id}
- **Description**: Retrieve a specific clipboard entry by its unique ID.
- **Authentication**: Minecraft session information must be included in the request.
- **Response**:
  ```json
  {
    "id": 123,
    "date": "2025-04-22T15:30:00Z",
    "description": "Clipboard entry description",
    "clipboard_data": "<clipboard-payload>"
  }
  ```

### POST /api/reload
- **Description**: Reload the configuration file. Only allowed from whitelisted IPs.
- **Authentication**: None required.

## Authentication

The server uses Mojang's authentication system to verify users. Requests must provide Minecraft session data that can be validated through Mojang's authentication servers.

- The client plugin for Minecraft (Bukkit/Folia/Spigot) should provide the necessary session data when making requests.

## Configuration

The server’s configuration is stored in the `config/endpoints.json` file. This file specifies:
- IP address whitelists for `copy`, `paste`, and `reload` endpoints.
- The optional description and configuration for any other specific settings.

Example `endpoints.json`:
```json
{
  "permit": {
    "copy": ["192.168.1.1", "localhost"],
    "paste": ["192.168.1.1"],
    "reload": ["localhost"]
  }
}
```

## Database

The server uses SQLite to store clipboard data. The database is stored in `storage/database.sqlite`. The database is automatically created upon the first request.

## Testing

Unit tests are included in the project using PHPUnit.

To run tests:
```bash
composer test
```

## Contributing

1. Fork the repository.
2. Create a new branch.
3. Implement your changes.
4. Submit a pull request.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Repository

GitHub: [https://github.com/Hierosoft/weclipboardserver](https://github.com/Hierosoft/weclipboardserver)

