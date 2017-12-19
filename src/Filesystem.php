<?php

namespace Terminal42\FineUploaderBundle;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\File;

class Filesystem
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var string
     */
    private $rootDir;

    /**
     * Temporary upload path
     * @var string
     */
    private $tmpPath;

    /**
     * Filesystem constructor.
     *
     * @param ContaoFrameworkInterface $framework
     * @param string                   $rootDir
     * @param string                   $tmpPath
     */
    public function __construct(ContaoFrameworkInterface $framework, $rootDir, $tmpPath)
    {
        $this->framework = $framework;
        $this->rootDir   = dirname($rootDir);
        $this->tmpPath   = $tmpPath;
    }

    /**
     * Get the temporary path
     *
     * @return string
     */
    public function getTmpPath()
    {
        return $this->tmpPath;
    }

    /**
     * Return true if the file exists
     *
     * @param string $filePath
     *
     * @return bool
     */
    public function fileExists($filePath)
    {
        return is_file($this->rootDir.'/'.$filePath);
    }

    /**
     * Return true if the file temporary exists
     *
     * @param string $file
     *
     * @return bool
     */
    public function tmpFileExists($file)
    {
        return $this->fileExists($this->tmpPath.'/'.$file);
    }

    /**
     * Merge multiple temporary files into one
     *
     * @param array  $files
     * @param string $fileName
     *
     * @return File
     */
    public function mergeTmpFiles(array $files, $fileName)
    {
        $file = new File($this->getTmpPath().'/'.$fileName);

        foreach ($files as $filePath) {
            $file->append(file_get_contents($this->rootDir.'/'.$filePath), '');
            $this->framework->createInstance('\Contao\Files')->delete($filePath);
        }

        $file->close();

        return $file;
    }

    /**
     * Get the temporary file name
     *
     * @param string $file
     *
     * @return string
     */
    public function getTmpFileName($file)
    {
        return $this->getFileName($file, $this->getTmpPath());
    }

    /**
     * Move the temporary file to its destination
     *
     * @param string $file
     * @param string $destination
     * @param bool   $doNotOverwrite
     *
     * @return string
     *
     * @throws \Exception
     */
    public function moveTmpFile($file, $destination, $doNotOverwrite = false)
    {
        if (!$this->fileExists($file)) {
            return '';
        }

        // The file is not temporary
        if (stripos($file, $this->tmpPath) === false) {
            return $file;
        }

        $new = $destination.'/'.basename($file);

        // Do not overwrite existing files
        if ($doNotOverwrite) {
            $new = $destination.'/'.$this->getFileName(basename($file), $destination);
        }

        /** @var \Contao\Files $files */
        $files = $this->framework->createInstance('\Contao\Files');
        $files->mkdir(dirname($new));

        // Try to rename the file
        if (!$files->rename($file, $new)) {
            throw new \Exception(sprintf('The file "%s" could not be renamed to "%s"', $file, $new));
        }

        // Set the default CHMOD
        $files->chmod($new, $this->framework->getAdapter('\Contao\Config')->get('defaultFileChmod'));

        return $new;
    }

    /**
     * Get the new file name if it already exists in the folder
     *
     * @param string $filePath
     * @param string $folder
     *
     * @return string
     */
    private function getFileName($filePath, $folder)
    {
        if (!$this->fileExists($folder.'/'.$filePath)) {
            return $filePath;
        }

        $offset   = 1;
        $pathinfo = pathinfo($filePath);
        $name     = $pathinfo['filename'];

        $allFiles = scan($this->rootDir.'/'.$folder);

        // Find the files with the same extension
        $files = preg_grep(
            '/^'.preg_quote($name, '/').'.*\.'.preg_quote($pathinfo['extension'], '/').'/',
            $allFiles
        );

        foreach ($files as $file) {
            if (preg_match('/__[0-9]+\.'.preg_quote($pathinfo['extension'], '/').'$/', $file)) {
                $file   = str_replace('.'.$pathinfo['extension'], '', $file);
                $value  = (int)substr($file, (strrpos($file, '_') + 1));
                $offset = max($offset, $value);
            }
        }

        return str_replace($name, $name.'__'.++$offset, $filePath);
    }
}
