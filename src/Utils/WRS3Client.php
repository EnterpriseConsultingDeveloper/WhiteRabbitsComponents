<?php

/**
 * Created by PhpStorm.
 * User: user
 * Date: 14/03/2016
 * Time: 10:04
 */

namespace S3FileManager\Utils;

use Aws\S3\S3Client;
use Aws\Credentials\Credentials;

require_once(ROOT .DS. 'src' . DS . 'Lib' . DS . 'aws' . DS .'aws-autoloader.php');

class WRS3Client extends S3Client
{

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_config = [
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
     * Constructor.
     *
     */
    public function __construct()
    {


        // Amazon S3 config
        $config = $this->_config;


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

    }

    public function WRS3Client() {
        return $this->_s3Client;
    }

    /**
     * Returns the URL to an object identified by its bucket and key.
     *
     * The URL returned by this method is not signed nor does it ensure the the
     * bucket and key given to the method exist. If you need a signed URL, then
     * use the {@see \Aws\S3\S3Client::createPresignedRequest} method and get
     * the URI of the signed request.
     *
     * @param string $bucket  The name of the bucket where the object is located
     * @param string $key     The key of the object
     *
     * @return string The URL to the object
     */
    public function getObjectUrl($site, $key)
    {
        $config = $this->_config;
        $bucket = $config['bucket'];
        $builtBucket = strlen($bucket) > 0 ? $site . '.' . $bucket : $site;

        return $this->_s3Client->getObjectUrl($builtBucket, $key);
    }



    /**
     * createFilePreview
     *
     * Return a file preview from Amazon S3.
     *
     * ### Example:
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
     * @param string $site
     * @param array $options
     * @return string
     */
    public function createFilePreview($path, $site, array $options = [])
    {
        $html = '';

        if ($path != null && $path != '') {
            try {
                $plainUrl = $this->getObjectUrl($site, $path); //$this->_s3Client->getObjectUrl($bucketName, $path, '+10 minutes');

                $fileName = $options['filename'];
                if (WRUtils::guessKindOfFile($fileName) == 'image') {
                    $html .= "<div class=\"file-selectable\" my-data-key=\"" . $options['id'] . "\" title=\"" . $options['title'] . "\">";
                    $html .= "<img style='height:120px' src='" . $plainUrl ."' class='file-preview-image' alt='" . $options['originalFilename'] . "' title='" . $options['originalFilename'] . "'>";
                    $html .= "</div>";
                } else {
                    $html .= "<div class=\"file-selectable\" my-data-key=\"" . $options['id'] . "\" title=\"" . $options['title'] . "\">";
                    $html .= WRUtils::getPreviewFileIcon($fileName);
                    $html .= "</div>";
                }
            } catch(\Exception $e) {
                $html .= "No preview!";
            }
        }

        return $html;
    }

}