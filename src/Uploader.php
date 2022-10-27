<?php

declare(strict_types=1);

namespace Terminal42\FineUploaderBundle;

use Contao\Dbafs;
use Contao\StringUtil;
use Contao\Validator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Terminal42\FineUploaderBundle\Widget\BaseWidget;

class Uploader
{
    /**
     * @var ChunkUploader
     */
    private $chunkUploader;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * Uploader constructor.
     */
    public function __construct(ChunkUploader $chunkUploader, Filesystem $fs, RequestStack $requestStack)
    {
        $this->chunkUploader = $chunkUploader;
        $this->fs = $fs;
        $this->requestStack = $requestStack;
    }

    /**
     * Upload the file.
     *
     * @return string|null
     */
    public function upload(Request $request, BaseWidget $widget)
    {
        $uploader = new FileUpload($widget->name);
        $config = $widget->getUploaderConfig();
        $isChunk = $config->isChunkingEnabled() && $request->request->has('qqpartindex');

        // Convert the $_FILES array to Contao format
        $this->convertGlobalFilesArray($request, $widget, $isChunk);

        // Configure the uploader
        $this->configureUploader($uploader, $config, $isChunk);

        // Run the upload
        if (null === ($result = $this->runUpload($uploader, $widget, $request->attributes->get('_scope')))) {
            return null;
        }

        $filePath = $result[0];

        // Handle the chunk
        if ($isChunk) {
            $filePath = $this->chunkUploader->handleChunk($request, $widget, $filePath);
            $isChunk = !$this->chunkUploader->isLastChunk($request);
        }

        // Validate and move the file immediately
        if ($config->isDirectUploadEnabled() && !$isChunk) {
            $filePath = $this->storeFile($config, $filePath);
        }

        return $filePath;
    }

    /**
     * Store a single file.
     *
     * @param string $file
     *
     * @return string
     */
    public function storeFile(UploaderConfig $config, $file)
    {
        // Convert uuid to binary format
        if (Validator::isStringUuid($file)) {
            $file = StringUtil::uuidToBin($file);
        } elseif ($this->fs->fileExists($file)) {
            // Move the temporary file
            if ($config->isStoreFileEnabled() && $config->getUploadFolder()) {
                $file = $this->fs->moveTmpFile($file, $config->getUploadFolder(), $config->isDoNotOverwriteEnabled());

                // Add the file to database file system
                if ($config->isAddToDbafsEnabled() && null !== ($model = Dbafs::addResource($file))) {
                    $file = $model->uuid;
                }
            }
        } else {
            // The file does not exist
            throw new \Exception(sprintf('The file "%s" does not exist', $file));
        }

        return $file;
    }

    /**
     * Run the upload.
     *
     * @param string $scope
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
                $errors = $this->requestStack->getSession()->getFlashBag()->peek(sprintf('contao.%s.error', $scope));

                foreach ($errors as $error) {
                    $widget->addError($error);
                }
            }

            $this->requestStack->getSession()->getFlashBag()->clear();
        } catch (\Exception $e) {
            $widget->addError($e->getMessage());
        }

        // Add an error if the result is incorrect
        if (!\is_array($result) || \count($result) < 1) {
            $widget->addError($GLOBALS['TL_LANG']['MSC']['fineuploader.error']);
            $result = null;
        }

        return $result;
    }

    /**
     * Configure the uploader.
     *
     * @param bool $isChunk
     */
    private function configureUploader(FileUpload $uploader, UploaderConfig $config, $isChunk): void
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
     * Convert the global files array to Contao format.
     *
     * @param bool $isChunk
     */
    private function convertGlobalFilesArray(Request $request, BaseWidget $widget, $isChunk): void
    {
        $name = $widget->name.'_fineuploader';

        if (empty($_FILES[$name])) {
            return;
        }

        $file = [
            'name' => [$_FILES[$name]['name']],
            'type' => [$_FILES[$name]['type']],
            'tmp_name' => [$_FILES[$name]['tmp_name']],
            'error' => [$_FILES[$name]['error']],
            'size' => [$_FILES[$name]['size']],
        ];

        // Replace the special characters (#22)
        $file['name'][0] = $this->fs->standardizeFileName($file['name'][0]);

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
