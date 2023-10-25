# yii2-blocklist-validator

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
