<?php
declare(strict_types=1);

namespace particleflux\yii2blocklist;

use yii\caching\CacheInterface;
use yii\validators\Validator;

/**
 * BlockListFileValidator blocks attribute value in given array
 */
class BlockListFileValidator extends Validator
{
    private const CACHE_KEY_PREFIX = 'particleflux/yii2blocklist/';

    /**
     * @var ?string Filename of blocklist file. Might contain Yii aliases.
     *      File format is: one blocked value per line
     */
    public ?string $file;

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
     * @var int|null TTL for the cached values. If set to null, will use defaultDuration of the cache component
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
            throw new \InvalidArgumentException('Option file must be set');
        }

        $this->resolvedFile = \Yii::getAlias($this->file);
        if (!file_exists($this->resolvedFile)) {
            throw new \InvalidArgumentException('File does not exist');
        }
    }

    public function validateValue($value)
    {
        // cache it also in member property, in case it is called multiple times on the same object
        if ($this->blocklist === []) {
            if ($this->useCache) {
                /** @var CacheInterface $cache */
                $cache = \Yii::$app->get($this->cache);

                $this->blocklist = $cache->getOrSet(self::CACHE_KEY_PREFIX . $this->file, function () {
                    return file($this->resolvedFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                }, $this->cacheTtl);
            } else {
                $this->blocklist = file($this->resolvedFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            }
        }

        if (in_array($value, $this->blocklist, $this->strict)) {
            return [
                $this->message,
            ];
        }

        return null;
    }
}
