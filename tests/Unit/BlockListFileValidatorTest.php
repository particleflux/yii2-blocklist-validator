<?php
declare(strict_types=1);

namespace particleflux\Yii2Validators\tests\Unit;

use particleflux\Yii2Validators\BlockListFileValidator;
use particleflux\Yii2Validators\tests\TestCase;
use yii\base\InvalidConfigException;
use yii\caching\CacheInterface;
use yii\caching\FileCache;
use yii\helpers\FileHelper;

/**
 * @covers \particleflux\Yii2Validators\BlockListFileValidator
 */
class BlockListFileValidatorTest extends TestCase
{
    private string $listFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->listFile = $this->dataFilePath('blocklist.txt');
        $this->mockApplication(
            [
                'components' => [
                    'cache' => [
                        'class' => FileCache::class,
                    ]
                ]
            ]
        );
    }

    protected function tearDown(): void
    {
        FileHelper::removeDirectory(\Yii::getAlias('@runtime/cache'));

        parent::tearDown();
    }

    public function testInitMissingFileConfig(): void
    {
        $this->expectException(InvalidConfigException::class);
        \Yii::createObject(BlockListFileValidator::class);
    }

    public function testInitFileDoesNotExist(): void
    {
        $this->expectException(InvalidConfigException::class);
        \Yii::createObject(['class' => BlockListFileValidator::class, 'file' => '@runtime/this-does-not-exist']);
    }

    public function testInitMinimal(): void
    {
        /** @var BlockListFileValidator $validator */
        $validator = \Yii::createObject(['class' => BlockListFileValidator::class, 'file' => $this->listFile]);

        $this->assertSame($this->listFile, $validator->file);
        $this->assertFalse($validator->strict);
        $this->assertTrue($validator->useCache);
        $this->assertNull($validator->cacheTtl);
        $this->assertSame('cache', $validator->cache);
    }

    public function testInitFullOptions(): void
    {
        $validator = \Yii::createObject([
            'class' => BlockListFileValidator::class,
            'file' => $this->listFile,
            'strict' => true,
            'useCache' => false,
            'cacheTtl' => 60,
            'cache' => 'customCache',
        ]);

        $this->assertSame($this->listFile, $validator->file);
        $this->assertTrue($validator->strict);
        $this->assertFalse($validator->useCache);
        $this->assertSame(60, $validator->cacheTtl);
        $this->assertSame('customCache', $validator->cache);
    }

    public function testValidateValue(): void
    {
        /** @var BlockListFileValidator $validator */
        $validator = \Yii::createObject([
                'class' => BlockListFileValidator::class,
                'file' => $this->listFile,
            ]
        );

        // blocked value
        $result = $validator->validateValue('bar');
        $this->assertSame(['{attribute} "{value}" is blocked'], $result);

        // another blocked value
        $result = $validator->validateValue('baz');
        $this->assertSame(['{attribute} "{value}" is blocked'], $result);

        // another blocked value
        $result = $validator->validateValue('42');
        $this->assertSame(['{attribute} "{value}" is blocked'], $result);

        // empty string is _not_ blocked, despite technically being at the end of file
        $result = $validator->validateValue('');
        $this->assertNull($result);

        // another non blocked value
        $result = $validator->validateValue('foo');
        $this->assertNull($result);
    }

    public function testValidateValueFileIsCachedWithinRequest(): void
    {
        $listFile = \Yii::getAlias('@runtime/to-be-deleted.txt');
        copy($this->listFile, $listFile);

        /** @var BlockListFileValidator $validator */
        $validator = \Yii::createObject([
            'class' => BlockListFileValidator::class,
            'file' => $listFile,
            ]
        );

        $result = $validator->validateValue('bar');
        $this->assertSame(['{attribute} "{value}" is blocked'], $result);

        // unlink the file to verify that validator uses memoization
        unlink($listFile);
        $result = $validator->validateValue('bar');
        $this->assertSame(['{attribute} "{value}" is blocked'], $result);
    }

    public function testValidateValueStrict(): void
    {
        /** @var BlockListFileValidator $validator */
        $validator = \Yii::createObject([
                'class' => BlockListFileValidator::class,
                'file' => $this->listFile,
                'strict' => true,
            ]
        );

        // blocked value
        $result = $validator->validateValue('bar');
        $this->assertSame(['{attribute} "{value}" is blocked'], $result);

        // blocked, correct type
        $result = $validator->validateValue('42');
        $this->assertSame(['{attribute} "{value}" is blocked'], $result);

        // not blocked, wrong type
        $result = $validator->validateValue(42);
        $this->assertNull($result);
    }

    public function testValidateValueNoCache(): void
    {
        /** @var BlockListFileValidator $validator */
        $validator = \Yii::createObject([
                'class' => BlockListFileValidator::class,
                'file' => $this->listFile,
                'useCache' => false,
            ]
        );

        $cache = $this->createMock(CacheInterface::class);
        $cache->expects($this->never())
            ->method('getOrSet');

        \Yii::$app->set('cache', $cache);

        // blocked value
        $result = $validator->validateValue('bar');
        $this->assertSame(['{attribute} "{value}" is blocked'], $result);

        // non blocked value
        $result = $validator->validateValue('foo');
        $this->assertNull($result);
    }

    public function testValidateValueCache(): void
    {
        /** @var BlockListFileValidator $validator */
        $validator = \Yii::createObject([
                'class' => BlockListFileValidator::class,
                'file' => $this->listFile,
            ]
        );

        $cache = $this->createMock(CacheInterface::class);
        $cache->expects($this->once())
            ->method('getOrSet')
            ->with(
                $this->equalTo('particleflux/yii2blocklist/' . $this->listFile),
                $this->anything(),
                $this->equalTo(null)
            )->willReturn(['lorem ipsum']);

        \Yii::$app->set('cache', $cache);

        $result = $validator->validateValue('lorem ipsum');
        $this->assertSame(['{attribute} "{value}" is blocked'], $result);
    }

    public function testValidateValueCustomCache(): void
    {
        /** @var BlockListFileValidator $validator */
        $validator = \Yii::createObject([
                'class' => BlockListFileValidator::class,
                'file' => $this->listFile,
                'cache' => 'fooCache',
                'cacheTtl' => 42,
            ]
        );

        $cache = $this->createMock(CacheInterface::class);
        $cache->expects($this->once())
            ->method('getOrSet')
            ->with(
                $this->stringStartsWith('particleflux/yii2blocklist'),
                $this->anything(),
                $this->equalTo(42)
            )->willReturn(['lorem ipsum']);

        \Yii::$app->set('fooCache', $cache);

        $cache = $this->createMock(CacheInterface::class);
        $cache->expects($this->never())
            ->method('getOrSet');
        \Yii::$app->set('cache', $cache);

        $result = $validator->validateValue('lorem ipsum');
        $this->assertSame(['{attribute} "{value}" is blocked'], $result);
    }
}
