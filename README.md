# ocolin/hyconext-lite

A PHP HTTP client for Hyconext network switches that expose a web GUI only, with no documented REST API. This library reverse-engineers the device's internal JSON endpoints to provide programmatic access to switch data.

Tested on the **HC8MT2XP-UP** model.

---

## Requirements

- PHP ^8.4
- Guzzle ^7.1
- ocolin/global-type ^2.0

---

## Installation

```bash
composer require ocolin/hyconext-lite
```

---

## Configuration

Configuration can be provided directly or via environment variables.

### Environment Variables

| Variable                  | Description                        | Default |
|---------------------------|------------------------------------|---------|
| `HYCONEXT_LITE_HOST`      | IP address or hostname of device   | —       |
| `HYCONEXT_LITE_USERNAME`  | Login username                     | —       |
| `HYCONEXT_LITE_PASSWORD`  | Login password                     | —       |
| `HYCONEXT_LITE_SSL`       | Use HTTPS                          | `true`  |

> **Note:** The device enforces a 15-character maximum on passwords. This library handles the truncation automatically during authentication.

### Constructor Parameters

All parameters are optional and fall back to environment variables if not provided.

```php
$config = new \Ocolin\HyconextLite\Config(
    host:     '10.0.0.1',
    username: 'admin',
    password: 'yourpassword',
    ssl:      true,
);
```

---

## Usage

```php
use Ocolin\HyconextLite\Config;
use Ocolin\HyconextLite\HyconextLite;

$client = new HyconextLite(
    config: new Config(
        host:     '10.0.0.1',
        username: 'admin',
        password: 'yourpassword',
    )
);
```

Or using environment variables:

```php
$client = new HyconextLite();
```

Authentication is handled automatically on the first request. The session is reused for the lifetime of the object.

---

## Methods

### getSystem()

Returns general system information about the device.

```php
$system = $client->getSystem();

echo $system->model;           // HC8MT2XP-UP
echo $system->serialNumber;    // NYMTFJB00020
echo $system->ipv4;            // 10.75.29.2
echo $system->firmwareVersion; // 1.0.58.58.01.40.01.02.19
echo $system->tempCelsius;     // 49.0
echo $system->uptime;          // 267 days 2 hours 40 minutes 18 seconds
```

**Returns:** `\Ocolin\HyconextLite\DTO\System`

| Property          | Type     | Description                        |
|-------------------|----------|------------------------------------|
| `serialNumber`    | `string` | Device serial number               |
| `model`           | `string` | Device model                       |
| `tempCelsius`     | `float`  | Temperature in Celsius             |
| `tempFahrenheit`  | `float`  | Temperature in Fahrenheit          |
| `fanVcc`          | `float`  | Fan VCC voltage                    |
| `fanVccp`         | `float`  | Fan VCCP voltage                   |
| `ipv4`            | `string` | IPv4 address                       |
| `ipv6`            | `string` | IPv6 address                       |
| `ipv6LinkLocal`   | `string` | IPv6 link-local address            |
| `macAddress`      | `string` | Device MAC address                 |
| `firmwareVersion` | `string` | Firmware version string            |
| `hardwareVersion` | `string` | Hardware revision                  |
| `description`     | `string` | Device description/name            |
| `uptime`          | `string` | System uptime string               |

---

### getMacTable()

Returns the dynamic MAC address table.

```php
$entries = $client->getMacTable();

foreach( $entries as $entry ) {
    echo $entry->mac;      // 44:D9:E7:DE:71:A6
    echo $entry->port;     // 1
    echo $entry->fid;      // 100
    echo $entry->ageTimer; // 300
}
```

**Returns:** `\Ocolin\HyconextLite\DTO\MacEntry[]`

| Property   | Type     | Description                        |
|------------|----------|------------------------------------|
| `id`       | `int`    | Entry index                        |
| `mac`      | `string` | MAC address                        |
| `fid`      | `int`    | Forwarding/VLAN ID                 |
| `port`     | `int`    | Port number                        |
| `ageTimer` | `int`    | Aging timer in seconds             |

---

### getPortSettings()

Returns port configuration settings including descriptions and flow control.

```php
$ports = $client->getPortSettings();

foreach( $ports as $port ) {
    echo $port->id;              // 1
    echo $port->description;     // LiteAP 5AC 120
    echo $port->spdDuplexActual; // 1000Mbps Full Duplex
    echo $port->flowCtrlCfg;     // On
}
```

**Returns:** `\Ocolin\HyconextLite\DTO\PortSetting[]`

| Property          | Type     | Description                          |
|-------------------|----------|--------------------------------------|
| `id`              | `int`    | Port number                          |
| `status`          | `string` | Port admin status (Enabled/Disabled) |
| `spdDuplexCfg`    | `string` | Configured speed/duplex              |
| `spdDuplexActual` | `string` | Actual negotiated speed/duplex       |
| `flowCtrlCfg`     | `string` | Configured flow control              |
| `flowCtrlActual`  | `string` | Actual flow control state            |
| `description`     | `string` | Port description/alias               |

---

### getPortStatus()

Returns real-time port statistics including packet counters and link state.

```php
$ports = $client->getPortStatus();

foreach( $ports as $port ) {
    echo $port->id;         // 1
    echo $port->linkStatus; // 1000Mbps Full Duplex
    echo $port->rxGoodPkt;  // 3350574014
    echo $port->txGoodPkt;  // 3081943164
}
```

**Returns:** `\Ocolin\HyconextLite\DTO\PortStatus[]`

| Property     | Type     | Description                          |
|--------------|----------|--------------------------------------|
| `id`         | `int`    | Port number                          |
| `status`     | `string` | Port admin status (Enabled/Disabled) |
| `linkStatus` | `string` | Current link speed and duplex        |
| `txGoodPkt`  | `int`    | Transmitted good packet count        |
| `txBadPkt`   | `int`    | Transmitted bad packet count         |
| `rxGoodPkt`  | `int`    | Received good packet count           |
| `rxBadPkt`   | `int`    | Received bad packet count            |

---

## Authentication Notes

This library authenticates against the device's internal web GUI using the same mechanism as the browser:

- Both username and password are MD5-hashed before transmission
- The device enforces a 15-character maximum password length — longer passwords are silently truncated by the device's login form
- Authentication uses a session cookie which is maintained for the lifetime of the client object
- SSL certificate verification is disabled by default since these devices use self-signed certificates

---

## Known Endpoints (Not Yet Implemented)

The following endpoints have been identified on the device and are candidates for future implementation:

| Endpoint                    | Description      |
|-----------------------------|------------------|
| `poe_port_setting_load.json`| POE settings     |
| `port_poe_info_load.json`   | POE port status  |
| `qos_get_rate_limit.json`   | Rate limiting    |
| `port_mirror.json`          | Port mirroring   |
| `port_trunk_cfg.json`       | Link aggregation |
| `port_custom_vlan_load.json`| VLAN settings    |
| `sfp_info.json`             | SFP port info    |
| `fan_settings.json`         | Fan settings     |

Contributions welcome.

---

## Exception Handling

| Exception                                          | Thrown When                              |
|----------------------------------------------------|------------------------------------------|
| `Ocolin\HyconextLite\Exceptions\ConfigException`   | Required configuration is missing        |
| `Ocolin\HyconextLite\Exceptions\AuthException`     | Authentication fails                     |
| `Ocolin\HyconextLite\Exceptions\HyconextException` | Unexpected or unparseable device response|
| `GuzzleHttp\Exception\GuzzleException`             | HTTP transport error                     |

---

## License

MIT