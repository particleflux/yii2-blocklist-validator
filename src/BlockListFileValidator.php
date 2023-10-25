<?php
declare(strict_types=1);

namespace particleflux\Yii2Validators;

use yii\base\InvalidConfigException;
use yii\base\InvalidValueException;
use yii\caching\CacheInterface;
use yii\validators\Validator;

/**
 * BlockListFileValidator blocks specific attribute values in given file
 *
 * The file should contain one blocked value per line.
 *
 * Usage example:
 *
 *  ```
 *  public function rules(): array
 *  {
 *      return [
 *          ['username', BlockListFileValidator::class, 'file' => '/tmp/bad-usernames.txt'],
 *      ];
 *  }
 *  ```
 */
class BlockListFileValidator extends Validator
{
    private const CACHE_KEY_PREFIX = 'particleflux/yii2blocklist/';

    /**
     * @var ?string Filename of blocklist file. Might contain Yii aliases.
     *      File format is: one blocked value per line
     */
    public ?string $file = null;

    /**
     * @var bool Whether to do strict checking (same-type)
     */
    public bool $strict = false;

    /**
     * @inheritdoc
     */
    public $message = '{attribute} "{value}" is blocked';

    /**
     * @var bool Whether to cache the blocklist. Uses the cache component defined by $cache
     */
    public bool $useCache = true;

    /**
     * @var ?int TTL for the cached values, in seconds. If set to null, will use defaultDuration of the cache component
     */
    public ?int $cacheTtl = null;

    /**
     * @var string The cache component to use
     */
    public string $cache = 'cache';


    private string $resolvedFile = '';

    private array $blocklist = [];


    public function init(): void
    {
        if ($this->file === null) {
            throw new InvalidConfigException('Option file must be set');
        }

        /** @var string $file phpstan crutch, this cannot be false due to $throw param */
        $file = \Yii::getAlias($this->file);
        if (!file_exists($file)) {
            throw new InvalidConfigException('File does not exist');
        }

        $this->resolvedFile = $file;
    }

    public function validateValue($value): ?array
    {
        $readFile = function (): array {
            $lines = file($this->resolvedFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if ($lines === false) {
                throw new InvalidValueException('File reading error');
            }

            return $lines;
        };

        // cache it also in member property, in case it is called multiple times on the same object
        if ($this->blocklist === []) {
            if ($this->useCache) {
                /** @var CacheInterface $cache */
                $cache = \Yii::$app->get($this->cache);
                $this->blocklist = $cache->getOrSet(self::CACHE_KEY_PREFIX . $this->file, $readFile, $this->cacheTtl);
            } else {
                $this->blocklist = $readFile();
            }
        }

        if (in_array($value, $this->blocklist, $this->strict)) {
            return [
                $this->message,
                [],
            ];
        }

        return null;
    }
}
