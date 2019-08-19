<?php

namespace Terminal42\FineUploaderBundle;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Haste\Util\FileUpload;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Terminal42\FineUploaderBundle\Widget\BaseWidget;

class Uploader
{
    /**
     * @var ChunkUploader
     */
    private $chunkUploader;

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
     * Uploader constructor.
     *
     * @param ChunkUploader            $chunkUploader
     * @param ContaoFrameworkInterface $framework
     * @param Filesystem               $fs
     * @param Session                  $session
     */
    public function __construct(
        ChunkUploader $chunkUploader,
        ContaoFrameworkInterface $framework,
        Filesystem $fs,
        Session $session
    ) {
        $this->chunkUploader = $chunkUploader;
        $this->framework     = $framework;
        $this->fs            = $fs;
        $this->session       = $session;
    }

    /**
     * Upload the file
     *
     * @param Request    $request
     * @param BaseWidget $widget
     *
     * @return string|null
     */
    public function upload(Request $request, BaseWidget $widget)
    {
        $uploader = new FileUpload($widget->name);
        $config   = $widget->getUploaderConfig();
        $isChunk  = $config->isChunkingEnabled() && $request->request->has('qqpartindex');

        // Convert the $_FILES array to Contao format
        $this->convertGlobalFilesArray($request, $widget, $isChunk);

        // Configure the uploader
        $this->configureUploader($uploader, $config, $isChunk);

        // Run the upload
        if (($result = $this->runUpload($uploader, $widget, $request->attributes->get('_scope'))) === null) {
            return null;
        }

        $filePath = $result[0];

        // Handle the chunk
        if ($isChunk) {
            $filePath = $this->chunkUploader->handleChunk($request, $widget, $filePath);
            $isChunk  = !$this->chunkUploader->isLastChunk($request);
        }

        // Validate and move the file immediately
        if ($config->isDirectUploadEnabled() && !$isChunk) {
            $filePath = $this->storeFile($config, $result);
        }

        return $filePath;
    }

    /**
     * Run the upload
     *
     * @param FileUpload $uploader
     * @param BaseWidget $widget
     * @param string     $scope
     *
     * @return array|null
     */
    private function runUpload(FileUpload $uploader, BaseWidget $widget, $scope)
    {
        $result = null;

        try {
            $result = $uploader->uploadTo($this->fs->getTmpPath());

            // Collect the errors
            if ($uploader->hasError()) {
                $errors = $this->session->getFlashBag()->peek(sprintf('contao.%s.error', $scope));

                foreach ($errors as $error) {
                    $widget->addError($error);
                }
            }

            $this->session->getFlashBag()->clear();
        } catch (\Exception $e) {
            $widget->addError($e->getMessage());
        }

        // Add an error if the result is incorrect
        if (!is_array($result) || count($result) < 1) {
            $widget->addError($GLOBALS['TL_LANG']['MSC']['fineuploader.error']);
            $result = null;
        }

        return $result;
    }

    /**
     * Store a single file
     *
     * @param UploaderConfig $config
     * @param string         $file
     *
     * @return string
     */
    public function storeFile(UploaderConfig $config, $file)
    {
        /** @var \Contao\Validator $validator */
        $validator = $this->framework->getAdapter('\Contao\Validator');

        // Move the temporary file
        if (!$validator->isStringUuid($file) && $this->fs->fileExists($file) && $config->isStoreFileEnabled()) {
            $file = $this->fs->moveTmpFile($file, $config->getUploadFolder(), $config->isDoNotOverwriteEnabled());

            // Add the file to database file system
            if ($config->isAddToDbafsEnabled()
                && ($model = $this->framework->getAdapter('\Contao\Dbafs')->addResource($file)) !== null
            ) {
                $file = $model->uuid;
            }
        }

        // Convert uuid to binary format
        if ($validator->isStringUuid($file)) {
            $file = $this->framework->getAdapter('\Contao\StringUtil')->uuidToBin($file);
        }

        return $file;
    }

    /**
     * Configure the uploader
     *
     * @param FileUpload     $uploader
     * @param UploaderConfig $config
     * @param bool           $isChunk
     */
    private function configureUploader(FileUpload $uploader, UploaderConfig $config, $isChunk)
    {
        // Add the "chunk" extension to upload types
        if ($isChunk) {
            $uploader->setExtensions(['chunk']);
        }

        // Set the minimum size limit
        if ($config->getMinSizeLimit() > 0 && !$isChunk) {
            $uploader->setMinFileSize($config->getMinSizeLimit());
        }

        // Set the maximum file or chunk size
        if ($config->getMaxSizeLimit() > 0 || $isChunk) {
            $uploader->setMaxFileSize($isChunk ? $config->getChunkSize() : $uploader->getMaxFileSize());
        }

        // Set the maximum image width
        if ($config->getMaxImageWidth() > 0 && !$isChunk) {
            $uploader->setImageWidth($config->getMaxImageWidth());
        }

        // Set the maximum image height
        if ($config->getMaxImageHeight() > 0 && !$isChunk) {
            $uploader->setImageHeight($config->getMaxImageHeight());
        }
    }

    /**
     * Convert the global files array to Contao format
     *
     * @param Request    $request
     * @param BaseWidget $widget
     * @param bool       $isChunk
     */
    private function convertGlobalFilesArray(Request $request, BaseWidget $widget, $isChunk)
    {
        $name = $widget->name.'_fineuploader';

        if (empty($_FILES[$name])) {
            return;
        }

        $file = [
            'name'     => [$_FILES[$name]['name']],
            'type'     => [$_FILES[$name]['type']],
            'tmp_name' => [$_FILES[$name]['tmp_name']],
            'error'    => [$_FILES[$name]['error']],
            'size'     => [$_FILES[$name]['size']],
        ];

        // Replace the comma character (#22)
        $file['name'] = str_replace(',', '_', $file['name']);

        // Set the UUID as the filename
        if ($isChunk) {
            $file['name'][0] = $request->request->get('qquuid').'.chunk';
        }

        // Check if the file exists
        if ($this->fs->tmpFileExists($file['name'][0])) {
            $file['name'][0] = $this->fs->getTmpFileName($file['name'][0]);
        }

        $_FILES[$widget->name] = $file;
        unset($_FILES[$name]); // Unset the temporary file
    }
}
