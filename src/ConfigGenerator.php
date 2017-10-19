<?php

namespace Terminal42\FineUploaderBundle;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;

class ConfigGenerator
{
    /**
     * @var bool
     */
    private $debug;

    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * ConfigGenerator constructor.
     *
     * @param bool                     $debug
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct($debug, ContaoFrameworkInterface $framework)
    {
        $this->debug     = $debug;
        $this->framework = $framework;
    }

    /**
     * Generate the configuration from widget attributes
     *
     * @param array $attributes
     *
     * @return UploaderConfig
     */
    public function generateFromWidgetAttributes(array $attributes)
    {
        $config = new UploaderConfig();
        $config->setLabels($this->generateLabels());

        // Set the config from attributes
        $this->setConfigFromAttributes($config, $attributes);

        // Enable the debug
        if ($this->debug) {
            $config->enableDebug();
        }

        // Set the upload folder to the default one if not set yet
        if (!$config->getUploadFolder()) {
            $this->setUploadFolder($config, $this->framework->getAdapter('\Contao\Config')->get('uploadPath'));
        }

        return $config;
    }

    /**
     * Set the config from attributes
     *
     * @param UploaderConfig $config
     * @param array          $attributes
     */
    private function setConfigFromAttributes(UploaderConfig $config, array $attributes)
    {
        foreach ($attributes as $k => $v) {
            switch ($k) {
                case 'uploadFolder':
                    $this->setUploadFolder($config, $v);
                    break;

                case 'useHomeDir':
                    if ($v && FE_USER_LOGGED_IN) {
                        /** @var \Contao\FrontendUser $user */
                        $user = $this->framework->getAdapter('\Contao\FrontendUser');

                        if ($user->assignDir && $user->homeDir) {
                            $this->setUploadFolder($config, $user->homeDir);
                        }
                    }
                    break;

                case 'extensions':
                    $config->setExtensions(trimsplit(',', $v));
                    break;

                case 'uploaderLimit':
                    $config->setLimit($v);
                    break;

                case 'minlength':
                    $config->setMinSizeLimit($v);
                    break;

                case 'maxlength':
                    $config->setMaxSizeLimit($v);
                    break;

                case 'maxWidth':
                    $config->setMaxImageWidth($v);
                    break;

                case 'maxHeight':
                    $config->setMaxImageHeight($v);
                    break;

                case 'uploadButtonTitle':
                    $config->setUploadButtonTitle($v);
                    break;

                case 'maxConnections':
                    $config->setMaxConnections($v);
                    break;

                case 'chunking':
                    $v ? $config->enableChunking() : $config->disableChunking();
                    break;

                case 'chunkSize':
                    $config->setChunkSize($v);
                    break;

                case 'concurrent':
                    $v ? $config->enableConcurrent() : $config->disableConcurrent();
                    break;

                case 'directUpload':
                    $v ? $config->enableDirectUpload() : $config->disableDirectUpload();
                    break;

                case 'storeFile':
                    $v ? $config->enableStoreFile() : $config->disableStoreFile();
                    break;

                case 'doNotOverwrite':
                    $v ? $config->enableDoNotOverwrite() : $config->disableDoNotOverwrite();
                    break;

                case 'addToDbafs':
                    $v ? $config->enableAddToDbafs() : $config->disableAddToDbafs();
                    break;

                case 'debug':
                    $v ? $config->enableDebug() : $config->disableDebug();
                    break;
            }
        }
    }

    /**
     * Set the upload folder
     *
     * @param UploaderConfig $config
     * @param string         $folder Can be a regular path or UUID
     */
    private function setUploadFolder(UploaderConfig $config, $folder)
    {
        if ($this->framework->getAdapter('\Contao\Validator')->isUuid($folder)) {
            $model = $this->framework->getAdapter('\Contao\FilesModel')->findByUuid($folder);

            // Set the path from model
            if ($model !== null) {
                $config->setUploadFolder($model->path);
            }
        } else {
            $config->setUploadFolder($folder);
        }
    }

    /**
     * Generate the configuration array ready to use for JavaScript uploader setup
     *
     * @param UploaderConfig $config
     *
     * @return array
     */
    public function generateJavaScriptConfig(UploaderConfig $config)
    {
        $return = [
            'debug'             => $config->isDebugEnabled(),
            'extensions'        => $config->getExtensions(),
            'maxConnections'    => $config->getMaxConnections(),
            'limit'             => $config->getLimit(),
            'minSizeLimit'      => $config->getMinSizeLimit(),
            'sizeLimit'         => $config->getMaxSizeLimit(),
            'uploadButtonTitle' => $config->getUploadButtonTitle(),
        ];

        // Enable the chunking
        if ($config->isChunkingEnabled()) {
            $return['chunking']   = true;
            $return['chunkSize']  = $config->getChunkSize();
            $return['concurrent'] = $config->isConcurrentEnabled();
        }

        return $return;
    }

    /**
     * Generate the labels
     *
     * @return array
     */
    private function generateLabels()
    {
        $properties = [
            'text'       => [
                'formatProgress',
                'failUpload',
                'waitingForResponse',
                'paused',
            ],
            'messages'   => [
                'typeError',
                'sizeError',
                'minSizeError',
                'emptyError',
                'noFilesError',
                'tooManyItemsError',
                'maxHeightImageError',
                'maxWidthImageError',
                'minHeightImageError',
                'minWidthImageError',
                'retryFailTooManyItems',
                'onLeave',
                'unsupportedBrowserIos8Safari',
            ],
            'retry'      => [
                'autoRetryNote',
            ],
            'deleteFile' => [
                'confirmMessage',
                'deletingStatusText',
                'deletingFailedText',
            ],
            'paste'      => [
                'namePromptMessage',
            ],
        ];

        $labels = [];

        foreach ($properties as $category => $messages) {
            foreach ($messages as $message) {
                // Use label only if available, otherwise fall back to default message
                // defined in FineUploader JS script (EN)
                if (isset($GLOBALS['TL_LANG']['MSC']['fineuploader.trans.'.$category][$message])) {
                    $labels[$category][$message] = $GLOBALS['TL_LANG']['MSC']['fineuploader.trans.'.$category][$message];
                }
            }
        }

        return $labels;
    }
}
