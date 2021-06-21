<?php

declare(strict_types=1);

namespace Terminal42\FineUploaderBundle;

use Symfony\Component\Asset\Packages;

class AssetsManager
{
    /**
     * @var Packages
     */
    private $packages;

    public function __construct(Packages $packages)
    {
        $this->packages = $packages;
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
        return [];
    }

    /**
     * Get the backend assets.
     *
     * @return array
     */
    public function getBackendAssets()
    {
        return [
            $this->packages->getUrl('backend.css', 'terminal42_fine_uploader'),
            $this->packages->getUrl('backend.js', 'terminal42_fine_uploader'),
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
            $this->packages->getUrl('frontend.css', 'terminal42_fine_uploader'),
            $this->packages->getUrl('frontend.js', 'terminal42_fine_uploader'),
        ];

        // Include the sortable library
        if ($sortable) {
            $assets[] = $this->packages->getUrl('sortable.js', 'terminal42_fine_uploader');
        }

        return $assets;
    }
}
