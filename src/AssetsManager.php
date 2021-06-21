<?php

declare(strict_types=1);

namespace Terminal42\FineUploaderBundle;

class AssetsManager
{
    /**
     * @var bool
     */
    private $debug;

    /**
     * AssetsManager constructor.
     *
     * @param bool $debug
     */
    public function __construct($debug)
    {
        $this->debug = $debug;
    }

    /**
     * Include the assets.
     */
    public function includeAssets(array $assets): void
    {
        foreach ($assets as $asset) {
            switch (pathinfo($asset, PATHINFO_EXTENSION)) {
                case 'css':
                    $GLOBALS['TL_CSS'][] = $asset;
                    break;

                case 'js':
                    $GLOBALS['TL_JAVASCRIPT'][] = $asset;
                    break;
            }
        }
    }

    /**
     * Get the basic assets.
     *
     * @return array
     */
    public function getBasicAssets()
    {
        return [
            $this->getAssetPath('fine-uploader/fine-uploader.js'),
            $this->getAssetPath('handler/handler.css'),
            $this->getAssetPath('handler/handler.js'),
        ];
    }

    /**
     * Get the backend assets.
     *
     * @return array
     */
    public function getBackendAssets()
    {
        return [
            $this->getAssetPath('backend/backend.css'),
            $this->getAssetPath('backend/backend.js'),
        ];
    }

    /**
     * Get the frontend assets.
     *
     * @param bool $sortable
     *
     * @return array
     */
    public function getFrontendAssets($sortable = false)
    {
        $assets = [
            $this->getAssetPath('frontend/frontend.css'),
            $this->getAssetPath('frontend/frontend.js'),
        ];

        // Include the sortable library
        if ($sortable) {
            $assets[] = $this->getAssetPath('sortable/sortable.js');
        }

        return $assets;
    }

    /**
     * Get the asset path.
     *
     * @param string $path
     *
     * @return string
     */
    private function getAssetPath($path)
    {
        if (!$this->debug) {
            $info = pathinfo($path);
            $path = $info['dirname'].'/'.$info['filename'].'.min.'.$info['extension'];
        }

        return 'bundles/terminal42fineuploader/'.$path;
    }
}
