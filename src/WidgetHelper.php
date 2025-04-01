<?php

declare(strict_types=1);

namespace Terminal42\FineUploaderBundle;

use Contao\CoreBundle\File\Metadata;
use Contao\CoreBundle\Image\Studio\Studio;
use Contao\File;
use Contao\FilesModel;
use Contao\Image;
use Contao\Model\Collection;
use Contao\StringUtil;
use Contao\System;
use Contao\Template;
use Contao\Validator;
use Symfony\Component\Filesystem\Path;
use Terminal42\FineUploaderBundle\Widget\BaseWidget;

class WidgetHelper
{
    private Filesystem $fs;

    private Studio $studio;

    private string $projectDir;

    public function __construct(Filesystem $fs, Studio $studio, string $projectDir)
    {
        $this->fs = $fs;
        $this->studio = $studio;
        $this->projectDir = $projectDir;
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
            if (Validator::isUuid($file)) {
                $uuids[] = $file;
            } else if (\is_array($file) && Validator::isUuid($file['uuid'] ?? null)) {
                $uuids[] = $file['uuid'];
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
     *
     * @throws \InvalidArgumentException
     */
    public function addFileDataToTemplate(Template $template, $filePath, ?array $imageAttributes = null): void
    {
        if (!$this->fs->fileExists($filePath)) {
            throw new \InvalidArgumentException(\sprintf('The file "%s" does not exist', $filePath));
        }

        $file = new File($filePath);
        $template->file = $file;
        $template->icon = Image::getHtml(Image::getPath($file->icon), $file->extension);
        $template->size = System::getReadableSize($file->size);
        $template->addImage = false;

        // Add the image data
        if ($file->isImage) {
            $metaData = new Metadata([
                'title' => \sprintf('%s (%s, %sx%s px)', $file->path, $template->size, $file->width, $file->height),
                'alt' => $file->name,
            ]);

            $figure = $this->studio
                ->createFigureBuilder()
                ->from($file->path)
                ->setMetadata($metaData)
                ->setSize($imageAttributes['size'] ?? null)
                ->buildIfResourceExists()
            ;

            if (null !== $figure) {
                $figure->applyLegacyTemplateData($template);
            }
        }
    }

    /**
     * Returns an array with all the information per file that Contao expects for the widget's value or the session value.
     */
    public function getFilesArray($name, array $files, ?bool $storeFile = null): array
    {
        $storeFile ??= true;
        $count = 0;
        $return = [];

        foreach ($files as $filePath) {
            $model = null;

            // Get the file model
            if (Validator::isUuid($filePath)) {
                if (null === ($model = FilesModel::findByUuid($filePath))) {
                    continue;
                }

                $filePath = $model->path;
            }

            $file = new File($filePath);

            if (!$file->exists()) {
                continue;
            }

            $key = $name.'_'.$count++;

            $return[$key] = [
                'name' => $file->name,
                'type' => $file->mime,
                'tmp_name' => Path::join($this->projectDir, $file->path),
                'error' => 0,
                'size' => $file->size,
                'uuid' => null !== $model ? StringUtil::binToUuid($model->uuid) : '',
            ];

            // Only set the 'uploaded' key if we store the file (https://github.com/contao/contao/pull/7039)
            if ($storeFile) {
                $return[$key]['uploaded'] = true;
            }
        }

        return $return;
    }

    /**
     * Add the files to the session in order to reproduce Contao 4.13 uploader behavior.
     *
     * @param string $name
     */
    public function addFilesToSession($name, array $files, bool $storeFile = true): void
    {
        $files = $this->getFilesArray($name, $files, $storeFile);

        foreach ($files as $name => $data) {
            $_SESSION['FILES'][$name] = $data;
        }
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
        foreach ($this->generateValue(array_filter((array) $widget->value)) as $id => $path) {
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
            if (\is_array($file)) {
                $file = $file['tmp_name'] ?? null;
            }

            // Skip non existing files
            if (!$file || !$this->fs->fileExists($file)) {
                continue;
            }

            $files[$file] = $file;
        }

        return $files;
    }
}
