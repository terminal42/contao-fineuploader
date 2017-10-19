<?php

namespace Terminal42\FineUploaderBundle\Tests;

use Terminal42\FineUploaderBundle\AssetsManager;

class AssetsManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testIncludeAssets()
    {
        $assets = [
            'foo/bar.css',
            'foo/baz.css',
            'foo/bar.js',
            'foo/baz.js',
        ];

        $GLOBALS['TL_CSS']        = [];
        $GLOBALS['TL_JAVASCRIPT'] = [];

        $manager = $this->getManager(false);
        $manager->includeAssets($assets);

        static::assertSame(['foo/bar.css', 'foo/baz.css'], $GLOBALS['TL_CSS']);
        static::assertSame(['foo/bar.js', 'foo/baz.js'], $GLOBALS['TL_JAVASCRIPT']);
    }

    public function testBasicAssets()
    {
        $manager = $this->getManager(true);

        static::assertSame(
            [
                'bundles/terminal42fineuploader/fine-uploader/fine-uploader.js',
                'bundles/terminal42fineuploader/handler/handler.css',
                'bundles/terminal42fineuploader/handler/handler.js',
            ],
            $manager->getBasicAssets()
        );

        $manager = $this->getManager(false);

        static::assertSame(
            [
                'bundles/terminal42fineuploader/fine-uploader/fine-uploader.min.js',
                'bundles/terminal42fineuploader/handler/handler.min.css',
                'bundles/terminal42fineuploader/handler/handler.min.js',
            ],
            $manager->getBasicAssets()
        );
    }

    public function testBackendAssets()
    {
        $manager = $this->getManager(true);

        static::assertSame(
            [
                'bundles/terminal42fineuploader/backend/backend.css',
                'bundles/terminal42fineuploader/backend/backend.js',
            ],
            $manager->getBackendAssets()
        );

        $manager = $this->getManager(false);

        static::assertSame(
            [
                'bundles/terminal42fineuploader/backend/backend.min.css',
                'bundles/terminal42fineuploader/backend/backend.min.js',
            ],
            $manager->getBackendAssets()
        );
    }

    public function testFrontendAssets()
    {
        $manager = $this->getManager(true);

        static::assertSame(
            [
                'bundles/terminal42fineuploader/frontend/frontend.css',
                'bundles/terminal42fineuploader/frontend/frontend.js',
            ],
            $manager->getFrontendAssets()
        );

        static::assertSame(
            [
                'bundles/terminal42fineuploader/frontend/frontend.css',
                'bundles/terminal42fineuploader/frontend/frontend.js',
                'bundles/terminal42fineuploader/sortable/sortable.js',
            ],
            $manager->getFrontendAssets(true)
        );

        $manager = $this->getManager(false);

        static::assertSame(
            [
                'bundles/terminal42fineuploader/frontend/frontend.min.css',
                'bundles/terminal42fineuploader/frontend/frontend.min.js',
            ],
            $manager->getFrontendAssets()
        );

        static::assertSame(
            [
                'bundles/terminal42fineuploader/frontend/frontend.min.css',
                'bundles/terminal42fineuploader/frontend/frontend.min.js',
                'bundles/terminal42fineuploader/sortable/sortable.min.js',
            ],
            $manager->getFrontendAssets(true)
        );
    }

    private function getManager($debug)
    {
        return new AssetsManager($debug);
    }
}
