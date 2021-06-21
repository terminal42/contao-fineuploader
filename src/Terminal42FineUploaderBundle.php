<?php

declare(strict_types=1);

namespace Terminal42\FineUploaderBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class Terminal42FineUploaderBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
