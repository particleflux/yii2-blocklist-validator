<?php
declare(strict_types=1);

namespace particleflux\Yii2Validators\tests;

use yii\console\Application;
use yii\helpers\ArrayHelper;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Clean up after test.
     * By default the application created with [[mockApplication]] will be destroyed.
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->destroyApplication();
    }

    protected function dataDir(): string
    {
        return __DIR__ . '/data';
    }

    protected function dataFilePath(string $filename): string
    {
        return $this->dataDir() . '/' . $filename;
    }

    /**
     * Populates Yii::$app with a new application
     * The application will be destroyed on tearDown() automatically.
     *
     * @param array $config The application configuration, if needed
     * @param string $appClass name of the application class to create
     */
    protected function mockApplication($config = [], $appClass = Application::class): void
    {
        new $appClass(ArrayHelper::merge([
            'id' => 'testapp',
            'basePath' => __DIR__,
            'vendorPath' => $this->getVendorPath(),
            'aliases' => [
                '@runtime' => __DIR__ . '/_output',
            ],
        ], $config));
    }

    /**
     * Destroys application in Yii::$app by setting it to null.
     */
    protected function destroyApplication(): void
    {
        if (\Yii::$app && \Yii::$app->has('session', true)) {
            \Yii::$app->session->close();
        }
        \Yii::$app = null;
    }

    protected function getVendorPath(): string
    {
        return dirname(__DIR__, 2) . '/vendor';
    }
}