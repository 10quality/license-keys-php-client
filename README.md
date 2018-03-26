# WooCommerce License Keys API client (for PHP)

[![Latest Stable Version](https://poser.pugx.org/10quality/license-keys-php-client/v/stable)](https://packagist.org/packages/10quality/license-keys-php-client)
[![Total Downloads](https://poser.pugx.org/10quality/license-keys-php-client/downloads)](https://packagist.org/packages/10quality/license-keys-php-client)
[![License](https://poser.pugx.org/10quality/license-keys-php-client/license)](https://packagist.org/packages/10quality/license-keys-php-client)

License Keys API client used to activate, validate and deactivate your license keys on PHP.

## Content
* Documentation (visit our [wiki](https://github.com/10quality/license-keys-php-client/wiki))
* [Requirements](#requirements)
* [Install](#install)
    * [Using composer](#using-composer)
    * [Withput composer](#withput-composer)
* [Coding Guidelines](#coding-guidelines)
* [License](#license)

## Requirements
* PHP >= 5.4 (For PHP >= 7.1 see [main branch](https://github.com/10quality/license-keys-php-client))

## Install

### Using composer

Run command:
```bash
composer require 10quality/license-keys-php-client
```

### Without composer

Download the [latest release](https://github.com/10quality/license-keys-php-client/releases) of this package and store its content somewhere in your project.

Include the following php files:
```php
require_once '[path-to-package-folder]/src/LicenseRequest.php';
require_once '[path-to-package-folder]/src/Client.php';
require_once '[path-to-package-folder]/src/Api.php';
```

## Coding Guidelines

PSR-2 coding guidelines.

## License

MIT License. (c) 2018 [10 Quality](https://www.10quality.com/).