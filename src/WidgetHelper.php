<?php

namespace Terminal42\FineUploaderBundle;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\File;
use Contao\FilesModel;
use Contao\Model\Collection;
use Contao\Template;
use Symfony\Component\HttpFoundation\Session\Session;
use Terminal42\FineUploaderBundle\Widget\BaseWidget;

class WidgetHelper
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

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
     *
     * @param ContaoFrameworkInterface $framework
     * @param Filesystem               $fs
     * @param Session                  $session
     */
    public function __construct(ContaoFrameworkInterface $framework, Filesystem $fs, Session $session)
    {
        $this->framework = $framework;
        $this->fs        = $fs;
        $this->session   = $session;
    }

    /**
     * Generate the value
     *
     * @param array $value
     *
     * @return array
     */
    public function generateValue(array $value)
    {
        if (count($value) < 1) {
            return [];
        }

        /** @var \Contao\Validator $validator */
        $validator = $this->framework->getAdapter('\Contao\Validator');
        $uuids     = [];
        $tmpFiles  = [];

        // Split the files into UUIDs and temporary ones
        foreach ($value as $file) {
            if ($validator->isBinaryUuid($file)) {
                $uuids[] = $file;
            } else {
                $tmpFiles[] = $file;
            }
        }

        // Get the database files
        $return = $this->generateDatabaseFiles($uuids);

        // Get the temporary files
        $return = array_merge($return, $this->generateTmpFiles($tmpFiles));

        return $return;
    }

    /**
     * Generate the database files
     *
     * @param array $uuids
     *
     * @return array
     */
    private function generateDatabaseFiles(array $uuids)
    {
        if (($fileModels = $this->framework->getAdapter('\Contao\FilesModel')->findMultipleByUuids($uuids)) === null) {
            return [];
        }

        $files = [];

        /** @var \Contao\StringUtil $stringUtil */
        $stringUtil = $this->framework->getAdapter('\Contao\StringUtil');

        /**
         * @var Collection $fileModels
         * @var FilesModel $fileModel
         */
        foreach ($fileModels as $fileModel) {
            // Skip non existing files
            if (!$this->fs->fileExists($fileModel->path)) {
                continue;
            }

            $files[$stringUtil->binToUuid($fileModel->uuid)] = $fileModel->path;
        }

        return $files;
    }

    /**
     * Generate the temporary files
     *
     * @param array $tmpFiles
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

    /**
     * Add the file data to template
     *
     * @param Template $template
     * @param string   $filePath
     * @param array    $imageAttributes
     *
     * @throws \InvalidArgumentException
     */
    public function addFileDataToTemplate(Template $template, $filePath, array $imageAttributes = null)
    {
        if (!$this->fs->fileExists($filePath)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" does not exist', $filePath));
        }

        /** @var \Contao\Image $imageAdapter */
        $imageAdapter = $this->framework->getAdapter('\Contao\Image');

        $file               = new File($filePath, true);
        $template->file     = $file;
        $template->icon     = $imageAdapter->getHtml($imageAdapter->getPath($file->icon), $file->extension);
        $template->size     = $this->framework->getAdapter('\Contao\System')->getReadableSize($file->size);
        $template->addImage = false;

        // Add the image data
        if ($file->isImage) {
            $attributes = [
                'singleSRC' => $file->path,
                'title'     => sprintf('%s (%s, %sx%s px)', $file->path, $template->size, $file->width, $file->height),
                'alt'       => $file->name,
            ];

            // Merge custom image attributes
            if ($imageAttributes !== null) {
                $attributes = array_merge($attributes, $imageAttributes);
            }

            $this->framework->getAdapter('\Contao\Controller')->addImageToTemplate($template, $attributes);
        }
    }

    /**
     * Add the files to the session in order to reproduce Contao uploader behavior
     *
     * @param string $name
     * @param array  $files
     */
    public function addFilesToSession($name, array $files)
    {
        $count        = 0;
        $sessionKey   = 'FILES';
        $sessionFiles = $this->session->get($sessionKey);

        /**
         * @var \Contao\FilesModel $filesModelAdapter
         * @var \Contao\StringUtil $stringUtil
         * @var \Contao\Validator  $validator
         */
        $filesModelAdapter = $this->framework->getAdapter('\Contao\FilesModel');
        $stringUtil        = $this->framework->getAdapter('\Contao\StringUtil');
        $validator         = $this->framework->getAdapter('\Contao\Validator');

        foreach ($files as $filePath) {
            $model = null;

            // Get the file model
            if ($validator->isUuid($filePath)) {
                if (($model = $filesModelAdapter->findByUuid($filePath)) === null) {
                    continue;
                }

                $filePath = $model->path;
            }

            $file = new File($filePath, true);

            $sessionFiles[$name.'_'.$count++] = [
                'name'     => $file->name,
                'type'     => $file->mime,
                'tmp_name' => TL_ROOT.'/'.$file->path,
                'error'    => 0,
                'size'     => $file->size,
                'uploaded' => true,
                'uuid'     => ($model !== null) ? $stringUtil->binToUuid($model->uuid) : '',
            ];
        }

        $this->session->set($sessionKey, $sessionFiles);
    }

    /**
     * Generate the item template
     *
     * @param BaseWidget $widget
     * @param string     $id
     * @param string     $path
     *
     * @return Template
     */
    public function generateItemTemplate(BaseWidget $widget, $id, $path)
    {
        $template              = $widget->getItemTemplate();
        $template->id          = $id;
        $template->isDownloads = $widget->isDownloads;
        $template->isGallery   = $widget->isGallery;

        $this->addFileDataToTemplate($template, $path, ['size' => $widget->imageSize]);

        return $template;
    }

    /**
     * Generate the values template
     *
     * @param BaseWidget $widget
     *
     * @return Template
     */
    public function generateValuesTemplate(BaseWidget $widget)
    {
        $template = $widget->getValuesTemplate();
        $template->setData($widget->getConfiguration());

        $values = [];

        // Generate the values
        foreach ($this->generateValue((array)$widget->value) as $id => $path) {
            $values[$id] = $this->generateItemTemplate($widget, $id, $path);
        }

        $template->id       = $widget->id;
        $template->name     = $widget->name;
        $template->order    = array_keys($values);
        $template->sortable = $widget->sortable && $widget->multiple && count($values) > 1;
        $template->values   = $values;

        return $template;
    }
}
