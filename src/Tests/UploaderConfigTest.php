<?php

namespace Terminal42\FineUploaderBundle\Tests;

use Terminal42\FineUploaderBundle\UploaderConfig;

class UploaderConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testDebug()
    {
        $config = $this->getConfig();

        $config->disableDebug();
        static::assertSame(false, $config->isDebugEnabled());

        $config->enableDebug();
        static::assertSame(true, $config->isDebugEnabled());
    }

    public function testExtensions()
    {
        $extensions = ['jpg', 'jpeg'];

        $config = $this->getConfig();
        $config->setExtensions($extensions);

        static::assertSame($extensions, $config->getExtensions());
    }

    public function testLimits()
    {
        $limit        = 5;
        $minSizeLimit = 1024;
        $maxSizeLimit = 4096;

        $config = $this->getConfig();
        $config->setLimit($limit);
        $config->setMinSizeLimit($minSizeLimit);
        $config->setMaxSizeLimit($maxSizeLimit);

        static::assertSame($limit, $config->getLimit());
        static::assertSame($minSizeLimit, $config->getMinSizeLimit());
        static::assertSame($maxSizeLimit, $config->getMaxSizeLimit());
    }

    public function testMaxImageDimensions()
    {
        $width  = 800;
        $height = 600;

        $config = $this->getConfig();
        $config->setMaxImageWidth($width);
        $config->setMaxImageHeight($height);

        static::assertSame($width, $config->getMaxImageWidth());
        static::assertSame($height, $config->getMaxImageHeight());
    }

    public function testMaxConnections()
    {
        $max = 3;

        $config = $this->getConfig();
        $config->setMaxConnections($max);

        static::assertSame($max, $config->getMaxConnections());
    }

    public function testChunking()
    {
        $chunkSize = 2048;
        $config    = $this->getConfig();

        $config->disableChunking();
        static::assertSame(false, $config->isChunkingEnabled());

        $config->enableChunking();
        static::assertSame(true, $config->isChunkingEnabled());

        $config->disableConcurrent();
        static::assertSame(false, $config->isConcurrentEnabled());

        $config->enableConcurrent();
        static::assertSame(true, $config->isConcurrentEnabled());

        $config->setChunkSize($chunkSize);
        static::assertSame($chunkSize, $config->getChunkSize());
    }

    public function testDirectUpload()
    {
        $config = $this->getConfig();

        $config->disableDirectUpload();
        static::assertSame(false, $config->isDirectUploadEnabled());

        $config->enableDirectUpload();
        static::assertSame(true, $config->isDirectUploadEnabled());
    }

    public function testStoreFile()
    {
        $config = $this->getConfig();

        $config->disableStoreFile();
        static::assertSame(false, $config->isStoreFileEnabled());

        $config->enableStoreFile();
        static::assertSame(true, $config->isStoreFileEnabled());
    }

    public function testDoNotOverwrite()
    {
        $config = $this->getConfig();

        $config->disableDoNotOverwrite();
        static::assertSame(false, $config->isDoNotOverwriteEnabled());

        $config->enableDoNotOverwrite();
        static::assertSame(true, $config->isDoNotOverwriteEnabled());
    }

    public function testAddtoDbafs()
    {
        $config = $this->getConfig();

        $config->disableAddToDbafs();
        static::assertSame(false, $config->isAddToDbafsEnabled());

        $config->enableAddToDbafs();
        static::assertSame(true, $config->isAddToDbafsEnabled());
    }

    public function testUploadFolder()
    {
        $folder = 'foo/bar';

        $config = $this->getConfig();
        $config->setUploadFolder($folder);

        static::assertSame($folder, $config->getUploadFolder());
    }

    public function testUploadButtonTitle()
    {
        $title = 'Upload';

        $config = $this->getConfig();
        $config->setUploadButtonTitle($title);

        static::assertSame($title, $config->getUploadButtonTitle());
    }

    public function testLabels()
    {
        $labels = [
            'foo' => 'Foo',
            'bar' => 'Bar',
            'baz' => [
                'qux'  => 'Qux',
                'quux' => 'Quux',
            ],
        ];

        $config = $this->getConfig();
        $config->setLabels($labels);

        static::assertSame($labels, $config->getLabels());
    }

    private function getConfig()
    {
        return new UploaderConfig();
    }
}
