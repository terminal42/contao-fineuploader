<?php

declare(strict_types=1);

namespace Terminal42\FineUploaderBundle;

final class UploaderEvents
{
    /**
     * The file upload event occurs when the file is being uploaded.
     *
     * @Event("Terminal42\FineUploaderBundle\Event\FileUploadEvent")
     */
    public const FILE_UPLOAD = 'terminal42_fineuploader.file_upload';

    /**
     * The widget reload event occurs when the file upload is done
     * and widget has to be reloaded.
     *
     * @Event("Terminal42\FineUploaderBundle\Event\WidgetReloadEvent")
     */
    public const WIDGET_RELOAD = 'terminal42_fineuploader.widget_reload';
}
