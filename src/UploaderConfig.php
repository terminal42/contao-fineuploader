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
     * Minimum image width.
     *
     * @var int
     */
    private $minImageWidth;

    /**
     * Maximum image width.
     *
     * @var int
     */
    private $maxImageWidth;

    /**
     * Minimum image height.
     *
     * @var int
     */
    private $minImageHeight;

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
     */
    public function enableDebug(): self
    {
        $this->debug = true;

        return $this;
    }

    /**
     * Disable debug.
     */
    public function disableDebug(): self
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
     */
    public function setExtensions(array $extensions): self
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
     */
    public function setLimit($limit): self
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
     */
    public function setMinSizeLimit($minSizeLimit): self
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
     */
    public function setMaxSizeLimit($maxSizeLimit): self
    {
        $this->maxSizeLimit = (int) $maxSizeLimit;

        return $this;
    }

    /**
     * Get the minimum image width.
     *
     * @return int
     */
    public function getMinImageWidth()
    {
        return $this->minImageWidth;
    }

    /**
     * Set the minimum image width.
     *
     * @param int $minImageWidth
     */
    public function setMinImageWidth($minImageWidth): self
    {
        $this->minImageWidth = (int) $minImageWidth;

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
     */
    public function setMaxImageWidth($maxImageWidth): self
    {
        $this->maxImageWidth = (int) $maxImageWidth;

        return $this;
    }

    /**
     * Get the minimum image height.
     *
     * @return int
     */
    public function getMinImageHeight()
    {
        return $this->minImageHeight;
    }

    /**
     * Set the minimum image height.
     *
     * @param int $minImageHeight
     */
    public function setMinImageHeight($minImageHeight): self
    {
        $this->minImageHeight = (int) $minImageHeight;

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
     */
    public function setMaxImageHeight($maxImageHeight): self
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
     */
    public function setMaxConnections($maxConnections): self
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
     */
    public function enableChunking(): self
    {
        $this->chunking = true;

        return $this;
    }

    /**
     * Disable chunking.
     */
    public function disableChunking(): self
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
     */
    public function setChunkSize($chunkSize): self
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
     */
    public function enableConcurrent(): self
    {
        $this->concurrent = true;

        return $this;
    }

    /**
     * Disable the concurrent connections.
     */
    public function disableConcurrent(): self
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
     */
    public function enableDirectUpload(): self
    {
        $this->directUpload = true;

        return $this;
    }

    /**
     * Disable the direct upload.
     */
    public function disableDirectUpload(): self
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
     */
    public function enableStoreFile(): self
    {
        $this->storeFile = true;

        return $this;
    }

    /**
     * Disable the store file.
     */
    public function disableStoreFile(): self
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
     */
    public function enableDoNotOverwrite(): self
    {
        $this->doNotOverwrite = true;

        return $this;
    }

    /**
     * Disable the do not overwrite file.
     */
    public function disableDoNotOverwrite(): self
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
     */
    public function enableAddToDbafs(): self
    {
        $this->addToDbafs = true;

        return $this;
    }

    /**
     * Disable the add file to database file system.
     */
    public function disableAddToDbafs(): self
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
     */
    public function setUploadFolder($uploadFolder): self
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
    public function setUploadButtonTitle($uploadButtonTitle): self
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
     */
    public function setLabels(array $labels): self
    {
        $this->labels = $labels;

        return $this;
    }
}
