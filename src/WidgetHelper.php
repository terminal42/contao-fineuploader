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

use Contao\Controller;
use Contao\File;
use Contao\FilesModel;
use Contao\Image;
use Contao\Model\Collection;
use Contao\StringUtil;
use Contao\System;
use Contao\Template;
use Symfony\Component\HttpFoundation\Session\Session;
use Terminal42\FineUploaderBundle\Widget\BaseWidget;

class WidgetHelper
{
    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var Session
     */
    private $session;

    /**
     * WidgetHelper constructor.
     */
    public function __construct(Filesystem $fs, Session $session)
    {
        $this->fs = $fs;
        $this->session = $session;
    }

    /**
     * Generate the value.
     *
     * @return array
     */
    public function generateValue(array $value)
    {
        if (\count($value) < 1) {
            return [];
        }

        $uuids = [];
        $tmpFiles = [];

        // Split the files into UUIDs and temporary ones
        foreach ($value as $file) {
            if (\Contao\Validator::isBinaryUuid($file)) {
                $uuids[] = $file;
            } else {
                $tmpFiles[] = $file;
            }
        }

        // Get the database files
        $return = $this->generateDatabaseFiles($uuids);

        // Get the temporary files
        return array_merge($return, $this->generateTmpFiles($tmpFiles));
    }

    /**
     * Add the file data to template.
     *
     * @param string $filePath
     * @param array  $imageAttributes
     *
     * @throws \InvalidArgumentException
     */
    public function addFileDataToTemplate(Template $template, $filePath, array $imageAttributes = null): void
    {
        if (!$this->fs->fileExists($filePath)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" does not exist', $filePath));
        }

        $file = new File($filePath);
        $template->file = $file;
        $template->icon = Image::getHtml(Image::getPath($file->icon), $file->extension);
        $template->size = System::getReadableSize($file->size);
        $template->addImage = false;

        // Add the image data
        if ($file->isImage) {
            $attributes = [
                'singleSRC' => $file->path,
                'title' => sprintf('%s (%s, %sx%s px)', $file->path, $template->size, $file->width, $file->height),
                'alt' => $file->name,
            ];

            // Merge custom image attributes
            if (null !== $imageAttributes) {
                $attributes = array_merge($attributes, $imageAttributes);
            }

            Controller::addImageToTemplate($template, $attributes);
        }
    }

    /**
     * Add the files to the session in order to reproduce Contao uploader behavior.
     *
     * @param string $name
     */
    public function addFilesToSession($name, array $files): void
    {
        $count = 0;
        $sessionKey = 'FILES';
        $sessionFiles = $this->session->get($sessionKey);

        foreach ($files as $filePath) {
            $model = null;

            // Get the file model
            if (\Contao\Validator::isUuid($filePath)) {
                if (null === ($model = FilesModel::findByUuid($filePath))) {
                    continue;
                }

                $filePath = $model->path;
            }

            $file = new File($filePath);

            $sessionFiles[$name.'_'.$count++] = [
                'name' => $file->name,
                'type' => $file->mime,
                'tmp_name' => TL_ROOT.'/'.$file->path,
                'error' => 0,
                'size' => $file->size,
                'uploaded' => true,
                'uuid' => (null !== $model) ? StringUtil::binToUuid($model->uuid) : '',
            ];
        }

        $this->session->set($sessionKey, $sessionFiles);
    }

    /**
     * Generate the item template.
     *
     * @param string $id
     * @param string $path
     *
     * @return Template
     */
    public function generateItemTemplate(BaseWidget $widget, $id, $path)
    {
        $template = $widget->getItemTemplate();
        $template->id = $id;
        $template->isDownloads = $widget->isDownloads;
        $template->isGallery = $widget->isGallery;

        $this->addFileDataToTemplate($template, $path, ['size' => $widget->imageSize]);

        return $template;
    }

    /**
     * Generate the values template.
     *
     * @return Template
     */
    public function generateValuesTemplate(BaseWidget $widget)
    {
        $template = $widget->getValuesTemplate();
        $template->setData($widget->getConfiguration());

        $values = [];

        // Generate the values
        foreach ($this->generateValue((array) array_filter($widget->value)) as $id => $path) {
            $values[$id] = $this->generateItemTemplate($widget, $id, $path);
        }

        $template->id = $widget->id;
        $template->name = $widget->name;
        $template->order = array_keys($values);
        $template->sortable = $widget->sortable && $widget->multiple && \count($values) > 1;
        $template->values = $values;

        return $template;
    }

    /**
     * Generate the database files.
     *
     * @return array
     */
    private function generateDatabaseFiles(array $uuids)
    {
        if (null === ($fileModels = FilesModel::findMultipleByUuids($uuids))) {
            return [];
        }

        $files = [];

        /**
         * @var Collection $fileModels
         * @var FilesModel $fileModel
         */
        foreach ($fileModels as $fileModel) {
            // Skip non existing files
            if (!$this->fs->fileExists($fileModel->path)) {
                continue;
            }

            $files[StringUtil::binToUuid($fileModel->uuid)] = $fileModel->path;
        }

        return $files;
    }

    /**
     * Generate the temporary files.
     *
     * @return array
     */
    private function generateTmpFiles(array $tmpFiles)
    {
        $files = [];

        foreach ($tmpFiles as $file) {
            // Skip non existing files
            if (!$this->fs->fileExists($file)) {
                continue;
            }

            $files[$file] = $file;
        }

        return $files;
    }
}
