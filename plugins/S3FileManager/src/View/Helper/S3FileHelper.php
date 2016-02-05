<?php
/**
 * WhiteRabbit (http://www.whiterabbitsuite.com)
 * Copyright (c) http://www.whiterabbitsuite.com
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) http://www.whiterabbitsuite.com
 * @link          http://www.whiterabbitsuite.com WhiteRabbit Project
 * @since         1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace S3FileManager\View\Helper;

use Cake\View\Helper;
use Aws\S3\S3Client;
use Aws\Credentials\Credentials;
use Cake\View\View;

require_once(ROOT .DS. 'src' . DS . 'Lib' . DS . 'aws' . DS .'aws-autoloader.php');

/**
 * S3File helper
 *
 * Configuration
 * 1. Set your path to SDK AWS API aws-autoloader.php in the require_once. In general it is like ROOT .DS. 'src' . DS . 'Lib' . DS . 'aws' . DS .'aws-autoloader.php;
 *
 * 2. Set correct parameter to access your AWS
 * 'S3Key' => ''
 * 'S3Secret' => '',
 * 'S3Region' => '',
 * 'S3Version' => '',
 * 'S3SignatureVersion' => ''
 *
 * 3. Add
 * in your AppController
 * public function initialize() {
 *  $this->helpers[] = 'WRUtils.S3File';
 * }
 * in your bootstrap.php
 * Plugin::load('WRUtils');
 *
 * 4. Usage:
 * $this->S3File->image($bucket, $path, $options);
 *
 * $path is the path of the image in the S3 bucket
 * $options are the same as for HTML image, like ['class'=>'img-responsive']
 * if you want to show a default image when no image is retrieved pass, for example, in $options ['noimageimage'=>'path/to/img/image.jpg']
 * if you want to show an HTML piece of code when no image is retrieved pass, for example, in $options ['noimagehtml'=>'<span>no image</span>']
 *
 */

class S3FileHelper extends Helper
{
    /**
     * Helpers
     *
     * @var array
     */
    public $helpers = ['Html'];

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'S3Key' => 'AKIAJMF5RMYVJVFEBOLQ',
        'S3Secret' => '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',
        'S3Region' => 'us-east-1',
        'S3Version' => 'latest',
        'S3SignatureVersion' => 'v4',
        'bucket' => 'whiterabbitsuite.com'
    ];

    /**
     * Instance of Amazon S3 Client.
     *
     * @var S3Client
     */
    protected $_s3Client;

    /**
     * Constructor. Overridden to merge passed args with URL options.
     *
     * @param \Cake\View\View $View The View this helper is being attached to.
     * @param array $config Configuration settings for the helper.
     */
    public function __construct(\Cake\View\View $View, array $config = [])
    {
        // Amazon S3 config
        $config = $this->config();

        $credentials = new Credentials($config['S3Key'], $config['S3Secret']);
        $options = [
            'region'            => $config['S3Region'],
            'version'           => $config['S3Version'],
            'http'    => [
                'verify' => false
            ],
            'signature_version' => $config['S3SignatureVersion'],
            'credentials' => $credentials,
            //'debug'   => true
        ];

        $this->_s3Client = new S3Client($options);

        parent::__construct($View, $config + [
                'helpers' => ['Html'],
            ]);
    }
    /**
     * image
     *
     * Return a file image from Amazon S3.
     *
     * ### Example:
     *
     * `$this->S3File->image($path, $options);`
     *
     * $options are the same as for HTML image, like ['class'=>'img-responsive']
     * if you want to show an HTML piece of code when no image is retrieved pass, for example, in $options ['noimagehtml'=>'<span>no image</span>']
     *
     * $path is the path of the image in the S3 bucket
     *
     * @param string $path
     * @param array $options
     * @return string
     */
    public function image($path, array $options = [])
    {
        $html = '';

        $bucketName = $this->getBucketName($this->request->session()->read('Auth.User.customer_site'));

        if ($path != null && $path != '') {
            try {
                $plainUrl = $this->_s3Client->getObjectUrl($bucketName, $path, '+10 minutes');
                $html .= $this->Html->image($plainUrl, $options);
            } catch(\Exception $e) {
                $html .= $this->getDefaultImage($options);
            }
        } else {
            $html .= $this->getDefaultImage($options);
        }

        return $html;
    }



    /**
     * filePreview
     *
     * Return a file preview from Amazon S3.
     *
     * ### Example:
     *
     * `$this->S3File->filePreview($path, $options);`
     *
     * $options
     * $options['filename']
     * $options['title']
     * $options['description']
     * $options['originalFilename']
     *
     * $path is the path of the image in the S3 bucket
     *
     * @param string $path
     * @param array $options
     * @return string
     */
    public function filePreview($path, array $options = [])
    {
        $html = '';

        $bucketName = $this->getBucketName($this->request->session()->read('Auth.User.customer_site'));

        if ($path != null && $path != '') {
            try {

                $plainUrl = $this->_s3Client->getObjectUrl($bucketName, $path, '+10 minutes');

                $fileName = $options['filename'];
                //debug($this->guessKindOfFile($plainUrl)); die;
                if ($this->guessKindOfFile($fileName) === 'text') {
                    $html .= "<div class=\"file-preview-text\" title=\"" . $options['title'] . "\">";
                    $html .= $options['description'];
                    $html .= "<span class=\"wrap-indicator\"  title=\"" . $options['title'] . "\">" . $options['description'] . "</span>";
                    $html .= "</div>";
                } elseif ($this->guessKindOfFile($fileName) === 'image') {
                    $options['class'] = 'file-preview-image';
                    $html .=  $this->Html->image($plainUrl, $options);
                } else {
                    $html .= "<div class=\"file-preview-text\">";
                    $html .= "<h2><i class=\"glyphicon glyphicon-file\"></i></h2>";
                    $html .= $options['originalFilename'] . + "</div>";
                }

            } catch(\Exception $e) {
                $html .= "No preview!";
            }
        }

        return $html;
    }


    private function guessKindOfFile($filename)
    {
        $mime_types = array(
            // text
            'text/plain' => 'text',
            'text/html' => 'text',
            'text/css' => 'text',
            'application/pdf' => 'text',

            // image
            'image/png' => 'image',
            'image/jpeg' => 'image',
            'image/gif' => 'image',
            'image/bmp' => 'image',
            'image/vnd.microsoft.icon' => 'image',
            'image/tiff' => 'image',
            'image/svg+xml' => 'image',

            // other
            'application/zip' => 'other',
            'application/x-rar-compressed' => 'other',
            'application/x-msdownload' => 'other',
            'application/vnd.ms-cab-compressed' => 'other',
            'application/javascript' => 'other',
            'application/json' => 'other',
            'application/xml' => 'other',
            'application/x-shockwave-flash' => 'other',
            'video/x-flv' => 'other',
            'audio/mpeg' => 'other',
            'video/quicktime' => 'other',
            'image/vnd.adobe.photoshop' => 'other',
            'application/postscript' => 'other',
            'application/msword' => 'other',
            'application/rtf' => 'other',
            'application/vnd.ms-excel' => 'other',
            'application/vnd.ms-powerpoint' => 'other',
            'application/vnd.oasis.opendocument.text' => 'other',
            'application/vnd.oasis.opendocument.spreadsheet' => 'other',
        );


        $mime = strtolower($this->guessMimeType($filename));
        if (array_key_exists($mime, $mime_types)) {
            return $mime_types[$mime];
        } else {
            return 'other';
        }
    }

    private function guessMimeType($filename) {

        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $fileArray = explode('.', $filename);
        $ext = strtolower(array_pop($fileArray));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        } else {
            return 'application/octet-stream';
        }
    }


    /**
     * getDefaultImage
     *
     * Return a default image or html.
     *
     * @param array $options
     * @return string
     */
    private function getDefaultImage(array $options = []) {
        $html = '';

        if (!isset($options['noimagehtml'])) {
            $options['noimagehtml'] = '';
        }

        if (!isset($options['noimageimage'])) {
            $options['noimageimage'] = '';
        }

        if ($options['noimageimage'] != '') {
            $html .= $this->Html->image($options['noimageimage'], $options);
        } else {
            $html .= $options['noimagehtml'];
        }

        return $html;
    }


    /**
     * getBucketName
     * Get the bucket name for Amazon S3
     *
     * @param string $site.
     * @return string
     */
    private function getBucketName($site)
    {
        $config = $this->config();
        $bucket = $config['bucket'];
        $builtBucket = strlen($bucket) > 0 ? $site . '.' . $bucket : $site;

        return $builtBucket;
    }
}
