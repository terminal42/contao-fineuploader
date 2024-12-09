<?php

declare(strict_types=1);

use Terminal42\FineUploaderBundle\Widget\BackendWidget;
use Terminal42\FineUploaderBundle\Widget\FrontendWidget;

$GLOBALS['BE_FFL']['fineUploader'] = BackendWidget::class;
$GLOBALS['TL_FFL']['fineUploader'] = FrontendWidget::class;
