<?php

declare(strict_types=1);

/*
 * FineUploader Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2020, terminal42 gmbh
 * @author     terminal42 <https://terminal42.ch>
 * @license    MIT
 */

namespace Terminal42\FineUploaderBundle;

final class UploaderEvents
{
    /**
     * The file upload event occurs when the file is being uploaded.
     *
     * @Event("Terminal42\FineUploaderBundle\Event\FileUploadEvent")
     *
     * @var string
     */
    public const FILE_UPLOAD = 'terminal42_fineuploader.file_upload';

    /**
     * The widget reload event occurs when the file upload is done
     * and widget has to be reloaded.
     *
     * @Event("Terminal42\FineUploaderBundle\Event\WidgetReloadEvent")
     *
     * @var string
     */
    public const WIDGET_RELOAD = 'terminal42_fineuploader.widget_reload';
}
