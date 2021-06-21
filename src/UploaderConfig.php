<?php

declare(strict_types=1);

namespace Terminal42\FineUploaderBundle;

class UploaderConfig
{
    /**
     * Debug mode.
     *
     * @var bool
     */
    private $debug = false;

    /**
     * Allowed extensions.
     *
     * @var array
     */
    private $extensions = [];

    /**
     * Limit.
     *
     * @var int
     */
    private $limit;

    /**
     * Minimum file size.
     *
     * @var int
     */
    private $minSizeLimit;

    /**
     * Maximum file size.
     *
     * @var int
     */
    private $maxSizeLimit;

    /**
     * Maximum image width.
     *
     * @var int
     */
    private $maxImageWidth;

    /**
     * Maximum image height.
     *
     * @var int
     */
    private $maxImageHeight;

    /**
     * Maximum number of connections.
     *
     * @var int
     */
    private $maxConnections;

    /**
     * Allow chunking.
     *
     * @var bool
     */
    private $chunking = false;

    /**
     * Chunk size.
     *
     * @var int
     */
    private $chunkSize;

    /**
     * Allow concurrent connections.
     *
     * @var bool
     */
    private $concurrent = false;

    /**
     * Allow direct upload.
     *
     * @var bool
     */
    private $directUpload = false;

    /**
     * Store file.
     *
     * @var bool
     */
    private $storeFile = false;

    /**
     * Do not overwrite file.
     *
     * @var bool
     */
    private $doNotOverwrite = false;

    /**
     * Add to database file system.
     *
     * @var bool
     */
    private $addToDbafs = false;

    /**
     * Upload folder.
     *
     * @var string
     */
    private $uploadFolder;

    /**
     * Upload button title.
     *
     * @var string
     */
    private $uploadButtonTitle;

    /**
     * Labels.
     *
     * @var array
     */
    private $labels = [];

    /**
     * Return true if debug mode is enabled.
     *
     * @return bool
     */
    public function isDebugEnabled()
    {
        return $this->debug;
    }

    /**
     * Enable debug.
     *
     * @return UploaderConfig
     */
    public function enableDebug()
    {
        $this->debug = true;

        return $this;
    }

    /**
     * Disable debug.
     *
     * @return UploaderConfig
     */
    public function disableDebug()
    {
        $this->debug = false;

        return $this;
    }

    /**
     * Get the allowed extensions.
     *
     * @return array
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Set the allowed extensions.
     *
     * @return UploaderConfig
     */
    public function setExtensions(array $extensions)
    {
        $this->extensions = $extensions;

        return $this;
    }

    /**
     * Get the file limit.
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Set the file limit.
     *
     * @param int $limit
     *
     * @return UploaderConfig
     */
    public function setLimit($limit)
    {
        $this->limit = (int) $limit;

        return $this;
    }

    /**
     * Get the minimum file size limit.
     *
     * @return int
     */
    public function getMinSizeLimit()
    {
        return $this->minSizeLimit;
    }

    /**
     * Set the minimum file size limit.
     *
     * @param int $minSizeLimit
     *
     * @return UploaderConfig
     */
    public function setMinSizeLimit($minSizeLimit)
    {
        $this->minSizeLimit = (int) $minSizeLimit;

        return $this;
    }

    /**
     * Get the maximum file size limit.
     *
     * @return int
     */
    public function getMaxSizeLimit()
    {
        return $this->maxSizeLimit;
    }

    /**
     * Set the maximum file size limit.
     *
     * @param int $maxSizeLimit
     *
     * @return UploaderConfig
     */
    public function setMaxSizeLimit($maxSizeLimit)
    {
        $this->maxSizeLimit = (int) $maxSizeLimit;

        return $this;
    }

    /**
     * Get the maximum image width.
     *
     * @return int
     */
    public function getMaxImageWidth()
    {
        return $this->maxImageWidth;
    }

    /**
     * Set the maximum image width.
     *
     * @param int $maxImageWidth
     *
     * @return UploaderConfig
     */
    public function setMaxImageWidth($maxImageWidth)
    {
        $this->maxImageWidth = (int) $maxImageWidth;

        return $this;
    }

    /**
     * Get the maximum image height.
     *
     * @return int
     */
    public function getMaxImageHeight()
    {
        return $this->maxImageHeight;
    }

    /**
     * Set the maximum image height.
     *
     * @param int $maxImageHeight
     *
     * @return UploaderConfig
     */
    public function setMaxImageHeight($maxImageHeight)
    {
        $this->maxImageHeight = (int) $maxImageHeight;

        return $this;
    }

    /**
     * Get the maximum number of connections.
     *
     * @return int
     */
    public function getMaxConnections()
    {
        return $this->maxConnections;
    }

    /**
     * Set the maximum number of connections.
     *
     * @param int $maxConnections
     *
     * @return UploaderConfig
     */
    public function setMaxConnections($maxConnections)
    {
        $this->maxConnections = (int) $maxConnections;

        return $this;
    }

    /**
     * Return true if chunking is enabled.
     *
     * @return bool
     */
    public function isChunkingEnabled()
    {
        return $this->chunking;
    }

    /**
     * Enable chunking.
     *
     * @return UploaderConfig
     */
    public function enableChunking()
    {
        $this->chunking = true;

        return $this;
    }

    /**
     * Disable chunking.
     *
     * @return UploaderConfig
     */
    public function disableChunking()
    {
        $this->chunking = false;

        return $this;
    }

    /**
     * Get the chunk size.
     *
     * @return int
     */
    public function getChunkSize()
    {
        return $this->chunkSize;
    }

    /**
     * Set the chunk size.
     *
     * @param int $chunkSize
     *
     * @return UploaderConfig
     */
    public function setChunkSize($chunkSize)
    {
        $this->chunkSize = (int) $chunkSize;

        return $this;
    }

    /**
     * Return true if the concurrent connections are enabled.
     *
     * @return bool
     */
    public function isConcurrentEnabled()
    {
        return $this->concurrent;
    }

    /**
     * Enable the concurrent connections.
     *
     * @return UploaderConfig
     */
    public function enableConcurrent()
    {
        $this->concurrent = true;

        return $this;
    }

    /**
     * Disable the concurrent connections.
     *
     * @return UploaderConfig
     */
    public function disableConcurrent()
    {
        $this->concurrent = false;

        return $this;
    }

    /**
     * Return true if the direct upload is enabled.
     *
     * @return bool
     */
    public function isDirectUploadEnabled()
    {
        return $this->directUpload;
    }

    /**
     * Enable the direct upload.
     *
     * @return UploaderConfig
     */
    public function enableDirectUpload()
    {
        $this->directUpload = true;

        return $this;
    }

    /**
     * Disable the direct upload.
     *
     * @return UploaderConfig
     */
    public function disableDirectUpload()
    {
        $this->directUpload = false;

        return $this;
    }

    /**
     * Return true if the store file is enabled.
     *
     * @return bool
     */
    public function isStoreFileEnabled()
    {
        return $this->storeFile;
    }

    /**
     * Enable the store file.
     *
     * @return UploaderConfig
     */
    public function enableStoreFile()
    {
        $this->storeFile = true;

        return $this;
    }

    /**
     * Disable the store file.
     *
     * @return UploaderConfig
     */
    public function disableStoreFile()
    {
        $this->storeFile = false;

        return $this;
    }

    /**
     * Return true if the do not overwrite file is enabled.
     *
     * @return bool
     */
    public function isDoNotOverwriteEnabled()
    {
        return $this->doNotOverwrite;
    }

    /**
     * Enable the do not overwrite file.
     *
     * @return UploaderConfig
     */
    public function enableDoNotOverwrite()
    {
        $this->doNotOverwrite = true;

        return $this;
    }

    /**
     * Disable the do not overwrite file.
     *
     * @return UploaderConfig
     */
    public function disableDoNotOverwrite()
    {
        $this->doNotOverwrite = false;

        return $this;
    }

    /**
     * Return true if the add file to database file system is enabled.
     *
     * @return bool
     */
    public function isAddToDbafsEnabled()
    {
        return $this->addToDbafs;
    }

    /**
     * Enable the add file to database file system.
     *
     * @return UploaderConfig
     */
    public function enableAddToDbafs()
    {
        $this->addToDbafs = true;

        return $this;
    }

    /**
     * Disable the add file to database file system.
     *
     * @return UploaderConfig
     */
    public function disableAddToDbafs()
    {
        $this->addToDbafs = false;

        return $this;
    }

    /**
     * Get the upload folder.
     *
     * @return string
     */
    public function getUploadFolder()
    {
        return $this->uploadFolder;
    }

    /**
     * Set the upload folder.
     *
     * @param string $uploadFolder
     *
     * @return UploaderConfig
     */
    public function setUploadFolder($uploadFolder)
    {
        $this->uploadFolder = $uploadFolder;

        return $this;
    }

    /**
     * Get the upload button title.
     *
     * @return string
     */
    public function getUploadButtonTitle()
    {
        return $this->uploadButtonTitle;
    }

    /**
     * Set the upload button title.
     *
     * @param string $uploadButtonTitle
     */
    public function setUploadButtonTitle($uploadButtonTitle)
    {
        $this->uploadButtonTitle = $uploadButtonTitle;

        return $this;
    }

    /**
     * Get the labels.
     *
     * @return array
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * Set the labels.
     *
     * @return UploaderConfig
     */
    public function setLabels(array $labels)
    {
        $this->labels = $labels;

        return $this;
    }
}
