# yii2-blocklist-validator

![Packagist Version (custom server)](https://img.shields.io/packagist/v/particleflux/yii2-blocklist-validator)
![Packagist PHP Version](https://img.shields.io/packagist/dependency-v/particleflux/yii2-blocklist-validator/php)
![build](https://github.com/particleflux/yii2-blocklist-validator/actions/workflows/tests.yml/badge.svg)
[![Maintainability](https://api.codeclimate.com/v1/badges/97d25149885e46341929/maintainability)](https://codeclimate.com/github/particleflux/yii2-blocklist-validator/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/97d25149885e46341929/test_coverage)](https://codeclimate.com/github/particleflux/yii2-blocklist-validator/test_coverage)

A Yii2 validator to block certain values

## Installation

```shell
composer require particleflux/yii2-blocklist-validator
```

## Usage

### BlockListFileValidator

Block attribute values contained in a file.

```php
public function rules(): array
{
    return [
        ['username', BlockListFileValidator::class, 'file' => '@app/config/bad-usernames.txt'],
    ];
}
```

Some of the behavior can be fine-tuned:

```php
public function rules(): array
{
    return [
        [
            'username',
            BlockListFileValidator::class,
            'file' => '@app/config/bad-usernames.txt'   // the path to the blocklist file, can contain aliases
            'strict' => true,           // whether to do strict comparison (default: false)
            'useCache' => true,         // use cache component defined in 'cache' (default: true)
            'cacheTtl' => 60,           // cache TTL (default: null, meaning the component default)
            'cache' => 'customCache',   // cache component to use (default 'cache')
        ],
    ];
}
```
