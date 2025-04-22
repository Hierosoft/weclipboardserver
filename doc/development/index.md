# weclipboardserver - Development
## Generating APIs
### How to Generate a Java Client from the OpenAPI Spec:

1. **Using OpenAPI Generator**: You can generate the Java client using the [OpenAPI Generator](https://openapi-generator.tech/).

    bash

    CopyEdit

    `openapi-generator-cli generate -i swagger.yaml -g java -o /path/to/output/directory`

    This will create a Java client in the specified output directory, with models and API classes based on your OpenAPI spec.

2. **Integrating the Java Client in a Bukkit/Folia Plugin**: Once the Java client is generated, you can integrate it into your Bukkit/Folia plugin to interact with the PHP-based API. Use the client to send and receive clipboard data, ensuring that Mojang session IDs are passed correctly for authentication.


## Internal Structure

```
CREATE TABLE clipboard_history (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    uuid TEXT NOT NULL,
    data TEXT NOT NULL,
    description TEXT,
    date TEXT NOT NULL
);
```
- `uuid`: Minecraft UUID of the user.

- `data`: base64 clipboard content.

- `description`: optional description.

- `date`: ISO 8601 string format (`Y-m-d\TH:i:sP`), easily produced from PHP or Go.


`src/Middleware/IPWhitelistMiddleware.php`

- Accepts an array of allowed IPs directly (as passed from `$config['permit'][...]` in `index.php`)

- Checks the client's IP address against the allowed list.

- Responds with a structured JSON error if access is denied.

- Supports CIDR notation and `localhost` (127.0.0.1 / ::1) matching.

Features:

- Flexible: Supports IP lists like `["127.0.0.1", "192.168.0.0/24"]` and `"localhost"` as a keyword.

- Safe fallback if `REMOTE_ADDR` is missing.

- Easily plugged into any Slim route group as shown in your `index.php`.
