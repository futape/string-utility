# futape/string-utility

This library offers a set of string utilities.

Utility functions are implemented as static functions in an abstract class, which is never expected to be instantiated.

Most functions are implemented multibyte-safe. If not, this is mentioned in the source code.

## Install

```bash
composer require futape/string-utility
```

## Usage

```php
use Futape\Utility\String\Strings;

echo Strings::stripLeft('foobar', 'foo') // "bar"
```

## Testing

The library is tested by unit tests using PHP Unit.

To execute the tests, install the composer dependencies (including the dev-dependencies), switch into the `tests`
directory and run the following command:

```bash
../vendor/bin/phpunit
```
