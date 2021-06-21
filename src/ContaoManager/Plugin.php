<?php

declare(strict_types=1);

namespace Terminal42\FineUploaderBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Terminal42\FineUploaderBundle\Terminal42FineUploaderBundle;

class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(Terminal42FineUploaderBundle::class)->setLoadAfter([ContaoCoreBundle::class]),
        ];
    }
}
