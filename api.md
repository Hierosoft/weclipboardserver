- IPWhitelistMiddleware ensures requests are filtered by IP using config['permit'][...].

- /clipboard/copy stores UUID, date, optional description, and clipboard blob in a DB table.

- /clipboard/list returns metadata (id, date, description) only, wrapped in { "entries": [...] } or { "error": "..." }.

- /clipboard/entry/{id} (or latest if no ID) returns metadata and full clipboard data.

- /reload is restricted to IPs in config['permit']['reload'] and reloads the config JSON file.

- All endpoints reject unauthorized IPs with an error message in { "error": "..." } format.

## date-time

PHP DateTime to ISO 8601:
```PHP
// Create a DateTime object
$date = new DateTime();

// Convert to ISO 8601 format
$iso8601Date = $date->format(DateTime::ATOM); // Equivalent to 'Y-m-d\TH:i:sP'

// Output: '2025-04-22T14:30:00+00:00'
echo $iso8601Date;
```

Java DateTime to ISO 8601 (for generated API client):
```Java
import java.time.Instant;
import java.time.ZoneOffset;
import java.time.format.DateTimeFormatter;
import java.util.Date;

// Current time in ISO 8601 format
String iso8601Date = DateTimeFormatter.ISO_OFFSET_DATE_TIME
    .withZone(ZoneOffset.UTC)
    .format(Instant.now());

// Output: '2025-04-22T14:30:00Z'
System.out.println(iso8601Date);
```
