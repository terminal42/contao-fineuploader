<?php

declare(strict_types=1);

namespace Terminal42\FineUploaderBundle;

use Contao\Config;
use Contao\File;
use Contao\Files;

class Filesystem
{
    /**
     * @var string
     */
    private $projectDir;

    /**
     * Temporary upload path.
     *
     * @var string
     */
    private $tmpPath;

    /**
     * Filesystem constructor.
     *
     * @param string $projectDir
     * @param string $tmpPath
     */
    public function __construct($projectDir, $tmpPath)
    {
        $this->projectDir = $projectDir;
        $this->tmpPath = $tmpPath;
    }

    /**
     * Get the temporary path.
     *
     * @return string
     */
    public function getTmpPath()
    {
        return $this->tmpPath;
    }

    /**
     * Return true if the file exists.
     *
     * @param string $filePath
     *
     * @return bool
     */
    public function fileExists($filePath)
    {
        return is_file($this->projectDir.'/'.$filePath);
    }

    /**
     * Return true if the file temporary exists.
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
     * Merge multiple temporary files into one.
     *
     * @param string $fileName
     *
     * @return File
     */
    public function mergeTmpFiles(array $files, $fileName)
    {
        $file = new File($this->getTmpPath().'/'.$fileName);

        foreach ($files as $filePath) {
            $file->append(file_get_contents($this->projectDir.'/'.$filePath), '');
            Files::getInstance()->delete($filePath);
        }

        $file->close();

        return $file;
    }

    /**
     * Get the temporary file name.
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
     * Move the temporary file to its destination.
     *
     * @param string $file
     * @param string $destination
     * @param bool   $doNotOverwrite
     *
     * @throws \Exception
     *
     * @return string
     */
    public function moveTmpFile($file, $destination, $doNotOverwrite = false)
    {
        if (!$this->fileExists($file)) {
            return '';
        }

        // The file is not temporary
        if (false === stripos($file, $this->tmpPath)) {
            return $file;
        }

        $new = $destination.'/'.basename($file);

        // Do not overwrite existing files
        if ($doNotOverwrite) {
            $new = $destination.'/'.$this->getFileName(basename($file), $destination);
        }

        $files = Files::getInstance();
        $files->mkdir(\dirname($new));

        // Try to rename the file
        if (!$files->rename($file, $new)) {
            throw new \Exception(sprintf('The file "%s" could not be renamed to "%s"', $file, $new));
        }

        // Set the default CHMOD
        $files->chmod($new, Config::get('defaultFileChmod'));

        return $new;
    }

    /**
     * Get the new file name if it already exists in the folder.
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

        $offset = 1;
        $pathinfo = pathinfo($filePath);
        $name = $pathinfo['filename'];

        $allFiles = scan($this->projectDir.'/'.$folder);

        // Find the files with the same extension
        $files = preg_grep(
            '/^'.preg_quote($name, '/').'.*\.'.preg_quote($pathinfo['extension'], '/').'/',
            $allFiles
        );

        foreach ($files as $file) {
            if (preg_match('/__[0-9]+\.'.preg_quote($pathinfo['extension'], '/').'$/', $file)) {
                $file = str_replace('.'.$pathinfo['extension'], '', $file);
                $value = (int) substr($file, strrpos($file, '_') + 1);
                $offset = max($offset, $value);
            }
        }

        return str_replace($name.'.', $name.'__'.++$offset.'.', $filePath);
    }
}
