<?php

namespace Terminal42\FineUploaderBundle;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Terminal42\FineUploaderBundle\Widget\BaseWidget;

class ChunkUploader
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
     * ChunkUploader constructor.
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
     * Handle the chunk by storing it in the session for further merge
     *
     * @param Request    $request
     * @param BaseWidget $widget
     * @param string     $filePath
     *
     * @return string
     */
    public function handleChunk(Request $request, BaseWidget $widget, $filePath)
    {
        $fileName            = $request->request->get('qqfilename');
        $sessionKey          = $this->getSessionKey($widget);
        $chunks              = $this->session->get($sessionKey);
        $chunks[$fileName][] = $filePath;

        // This is the last chunking request, merge the chunks and create the final file
        if ($this->isLastChunk($request)) {
            $chunks = $this->mergeChunks($widget, $chunks[$fileName], $fileName);

            // Unset the file session after merging the chunks
            unset($chunks[$fileName]);
        }

        // Update the session
        $this->session->set($sessionKey, $chunks);

        return $filePath;
    }

    /**
     * Merge the chunks
     *
     * @param BaseWidget $widget
     * @param array      $chunks
     * @param string     $fileName
     *
     * @return string
     */
    private function mergeChunks(BaseWidget $widget, array $chunks, $fileName)
    {
        // Get the new file name if temporary file already exists
        if ($this->fs->tmpFileExists($fileName)) {
            $fileName = $this->fs->getTmpFileName($fileName);
        }

        $file = $this->fs->mergeTmpFiles($chunks[$fileName], $fileName);

        // Validate the file
        $this->validateFile($file, $widget);

        return $file->path;
    }

    /**
     * Validate the file
     *
     * @param File       $file
     * @param BaseWidget $widget
     */
    private function validateFile(File $file, BaseWidget $widget)
    {
        $config       = $widget->getUploaderConfig();
        $minSizeLimit = $config->getMinSizeLimit();

        // Validate the minimum size limit
        if ($minSizeLimit > 0 && $file->size < $minSizeLimit) {
            $widget->addError(
                sprintf(
                    $GLOBALS['TL_LANG']['ERR']['minFileSize'],
                    $this->framework->getAdapter('\Contao\System')->getReadableSize($minSizeLimit)
                )
            );
        }

        $maxSizeLimit = $config->getMaxSizeLimit();

        // Validate the maximum size limit
        if ($maxSizeLimit > 0 && $file->size > $maxSizeLimit) {
            $widget->addError(
                sprintf(
                    $GLOBALS['TL_LANG']['ERR']['maxFileSize'],
                    $this->framework->getAdapter('\Contao\System')->getReadableSize($maxSizeLimit)
                )
            );
        }
    }

    /**
     * Return true if this is the last chunk
     *
     * @param Request $request
     *
     * @return bool
     */
    public function isLastChunk(Request $request)
    {
        return $request->request->getInt('qqpartindex') === $request->request->getInt('qqtotalparts') - 1;
    }

    /**
     * Clear the session from chunks
     *
     * @param BaseWidget $widget
     */
    public function clearSession(BaseWidget $widget)
    {
        $this->session->remove($this->getSessionKey($widget));
    }

    /**
     * Get the session key
     *
     * @param BaseWidget $widget
     *
     * @return string
     */
    private function getSessionKey(BaseWidget $widget)
    {
        return $widget->name.'_FINEUPLOADER_CHUNKS';
    }
}
