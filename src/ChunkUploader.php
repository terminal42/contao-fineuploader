<?php

declare(strict_types=1);

namespace Terminal42\FineUploaderBundle;

use Contao\File;
use Contao\System;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Terminal42\FineUploaderBundle\Widget\BaseWidget;

class ChunkUploader
{
    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * ChunkUploader constructor.
     */
    public function __construct(Filesystem $fs, RequestStack $requestStack)
    {
        $this->fs = $fs;
        $this->requestStack = $requestStack;
    }

    /**
     * Handle the chunk by storing it in the session for further merge.
     *
     * @param string $filePath
     *
     * @return string
     */
    public function handleChunk(Request $request, BaseWidget $widget, $filePath)
    {
        $fileName = $request->request->get('qqfilename');
        $sessionKey = $this->getSessionKey($widget);
        $chunks = $this->requestStack->getSession()->get($sessionKey);
        $chunks[$fileName][] = $filePath;

        // This is the last chunking request, merge the chunks and create the final file
        if ($this->isLastChunk($request)) {
            $filePath = $this->mergeChunks($widget, $chunks[$fileName], $fileName);

            // Unset the file session after merging the chunks
            unset($chunks[$fileName]);
        }

        // Update the session
        $this->requestStack->getSession()->set($sessionKey, $chunks);

        return $filePath;
    }

    /**
     * Return true if this is the last chunk.
     *
     * @return bool
     */
    public function isLastChunk(Request $request)
    {
        return $request->request->getInt('qqpartindex') === $request->request->getInt('qqtotalparts') - 1;
    }

    /**
     * Clear the session from chunks.
     */
    public function clearSession(BaseWidget $widget): void
    {
        $this->requestStack->getSession()->remove($this->getSessionKey($widget));
    }

    /**
     * Merge the chunks.
     *
     * @param string $fileName
     *
     * @return string
     */
    private function mergeChunks(BaseWidget $widget, array $chunks, $fileName)
    {
        // Replace the special characters (#22)
        $fileName = $this->fs->standardizeFileName($fileName);

        // Get the new file name if temporary file already exists
        if ($this->fs->tmpFileExists($fileName)) {
            $fileName = $this->fs->getTmpFileName($fileName);
        }

        $file = $this->fs->mergeTmpFiles($chunks, $fileName);

        // Validate the file
        $this->validateFile($file, $widget);

        return $file->path;
    }

    /**
     * Validate the file.
     */
    private function validateFile(File $file, BaseWidget $widget): void
    {
        $config = $widget->getUploaderConfig();
        $minSizeLimit = $config->getMinSizeLimit();

        // Validate the minimum size limit
        if ($minSizeLimit > 0 && $file->size < $minSizeLimit) {
            $widget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['minFileSize'], System::getReadableSize($minSizeLimit)));
        }

        $maxSizeLimit = $config->getMaxSizeLimit();

        // Validate the maximum size limit
        if ($maxSizeLimit > 0 && $file->size > $maxSizeLimit) {
            $widget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['maxFileSize'], System::getReadableSize($maxSizeLimit)));
        }
    }

    /**
     * Get the session key.
     *
     * @return string
     */
    private function getSessionKey(BaseWidget $widget)
    {
        return $widget->name.'_FINEUPLOADER_CHUNKS';
    }
}
