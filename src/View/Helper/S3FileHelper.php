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
use Cake\View\View;
use S3FileManager\Utils\WRUtils;
use Cake\ORM\TableRegistry;
use Cake\Cache\Cache;


/**
 * S3File helper
 *
 * Configuration
 *
 * 1. Add
 * in your AppController
 * public function initialize() {
 *  $this->helpers[] = 'WRUtils.S3File';
 * }
 * in your bootstrap.php
 * Plugin::load('WRUtils');
 *
 * 4. Usage:
 * $this->S3File->image($path, $options);
 *
 * $path is the path of the image with respect to the proxy
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

    ];


    private $proxyBasePath = '/s3_file_manager/Files/media';

    /**
     * Constructor. Overridden to merge passed args with URL options.
     *
     * @param \Cake\View\View $View The View this helper is being attached to.
     * @param array $config Configuration settings for the helper.
     */
    public function __construct(\Cake\View\View $View, array $config = [])
    {
        $config = $this->config();

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

        if ($path != null && $path != '') {
            try {
                $html .= $this->Html->image($this->preparePath($path), $options);
            } catch(\Exception $e) {
                $html .= $this->getDefaultImage($options);
            }
        } else {
            $html .= $this->getDefaultImage($options);
        }

        return $html;
    }

    /**
     * imageWithDefault
     *
     * Return an image. If image isn't available return default image or default html placeholder
     *
     * ### Example:
     *
     * `$this->S3File->imageWithDefault($path, $options);`
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
    public function imageWithDefault($path, array $options = [])
    {
        $html = '';
        if (
            $path != null && $path != ''
            && WRUtils::guessKindOfFile($path) === 'image'
            // && @getimagesize($path)
        )
        {
            try {
                $html .= $this->Html->image($this->preparePath($path), $options);
            } catch(\Exception $e) {
                $html .= $this->getDefaultImage($options);
            }
        } else {
            $html .= $this->getDefaultImage($options);
        }

        return $html;
    }

    /**
     * fileInfo
     *
     * Return an image. If image isn't available return default image or default html placeholder
     *
     * ### Example:
     *
     * `$this->S3File->fileInfo($name, $path, $options);`
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
    public function fileInfo($name, $path, array $options = [])
    {
      $html = '';

      $html = $this->Html->link(
        $name,
        $this->preparePath($path),
        $options
      );

      return $html;
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


    private function preparePath($path) {
        // if complete path, it's ok!
        if (WRUtils::startsWith($path, "//") || WRUtils::startsWith($path, "http"))
            return $path;

        if (!WRUtils::startsWith($path, "/"))
            $path = '/' . $path;

        return $this->proxyBasePath . $path;
    }

    /*
     * controlla se è auth a entrare nel link
     */

    public function linkEnabled($variations) {

        $package = Cache::read('packages'.$this->request->session()->read('Auth.User.customer_id'), 'db_results_daily');
        $packageReached = $package['limit'][$variations]['reached'];
        if (($packageReached == true)) {
            return true;
        }
        return false;

    }

    public function infoLimit($variationName) {

        $package = Cache::read('packages'.$this->request->session()->read('Auth.User.customer_id'), 'db_results_daily');

        $packageUsed = $package['limit'][$variationName]['used'];
        $packagePeak = $package['limit'][$variationName]['peak'];
        $packageReached = $package['limit'][$variationName]['reached'];

        // if (!$packageReached)
        //     return;

        $progressBar = 'progress-bar-success';

        if ($packagePeak == '-1') {
            $perc = 100;
            $perc_label = __('No Limits');
        }

        if ($packagePeak != '-1') {
            $perc = round($packageUsed/$packagePeak * 100);
            $perc_label = $perc.' %';
        }

        if ($perc >= 100) {
            $perc = 100;
            $progressBar = 'progress-bar-danger';
        }

        //if ($packageReached == false) return;

        $infos = $this->getInfo($variationName);

        $html = null;

        $html .= "<div class=\"hpanel\">";
        $html .= "<div class=\"panel-body\">";

        $html .= "<div class=\"row\">";
        $html .= "<div class=\"col-md-12 text-left\">";
        $html .= "<h2>".__('Limit Reached for: ').$infos->display_name."</h2>";

        $html .= "<div class=\"m\">";
        $html .= "<div class=\"progress m-t-xs full progress-striped\">";
        $html .= "<div style=\"width: ".$perc."%\" aria-valuemax=\"100\" aria-valuemin=\"0\" aria-valuenow=\"".$perc."\" role=\"progressbar\" class=\"progress-bar ".$progressBar."\">";
        $html .= $perc_label;
        $html .= "</div>";
        $html .= "</div>";
        $html .= "</div>";
        $html .= "</div>";

        $html .= "</div>";
        $html .= "</div>";

        $html .= "</div>";

        return $html;
    }

    private function getInfo($variations) {

        $customer_id = $this->request->session()->read('Auth.User.customer_id');

        $variationsTable = TableRegistry::get('Variations');
        $variation = $variationsTable->find()->select(['display_name'])->where(['name' => $variations])->first();



        return $variation;

    }
}
