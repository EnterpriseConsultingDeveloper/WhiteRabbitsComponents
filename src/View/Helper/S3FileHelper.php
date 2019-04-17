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
        $config = $this->getConfig();

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
     * videoWithDefault
     *
     * Return an video. If image isn't available return default image or default html placeholder
     *
     * ### Example:
     *
     * `$this->S3File->videoWithDefault($path, $options);`
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
    public function videoWithDefault($path, array $options = [])
    {
        $html = '';
        if (
            $path != null && $path != ''
            && WRUtils::guessKindOfFile($path) === 'other'
            // && @getimagesize($path)
        )
        {
            try {
                $html .= $this->Html->media($this->preparePath($path), $options);
            } catch(\Exception $e) {
                $html .= $this->getDefaultVideo($options);
            }
        } else {
            $html .= $this->getDefaultVideo($options);
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

    /**
     * getDefaultVideo
     *
     * Return a default video or html.
     *
     * @param array $options
     * @return string
     */
    private function getDefaultVideo(array $options = []) {
        $html = '';

        if (!isset($options['novideohtml'])) {
            $options['novideohtml'] = '';
        }

        if (!isset($options['novideovideo'])) {
            $options['novideovideo'] = '';
        }

        if ($options['novideovideo'] != '') {
            $html .= $this->Html->media($options['novideovideo'], $options);
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
				$packagePeak = $package['limit'][$variations]['peak'];

				if (($packageReached == true) || ($packagePeak == 0) || ($packagePeak == -1)) {
					return 'disabled';
				}
        return false;

    }


		public function infoLimit($variationNames) {

		// può essere che arrivi una richiesta di una stringa (vecchie release) quindi controllo
		// perchè ora possono anche vedere piò limiti in una pagina
		if (!is_array($variationNames))
			$variationNames = (array)$variationNames;

		$variations = array();
		foreach ($variationNames as $id => $variationName) {

			$package = Cache::read('packages'.$this->request->getSession()->read('Auth.User.customer_id'), 'db_results_daily');

			$packageUsed = $package['limit'][$variationName]['used'];
			$packagePeak = $package['limit'][$variationName]['peak'];
			$packageReached = $package['limit'][$variationName]['reached'];

			$usage = $package['limit'][$variationName];

			if ($usage['peak'] > 0) {
				if ($usage['peak'] != '-1') {
					$perc = round($usage['used'] / $usage['peak'] * 100);
					$perc_label = $perc . ' %';
					$progressBar = 'progress-bar-success';
				}

				if ($perc >= 100) {
					$perc = 100;
					$progressBar = 'progress-bar-danger';
				}

				if ($usage['peak'] == '-1') {
					$perc = 100;
					$perc_label = __('No Limits');
					$progressBar = 'progress-bar-success';
				}
			}

			if ($usage['peak'] == 0) {
				$perc = 100;
				$perc_label = __('No available');
				$progressBar = 'progress-bar-warning';
			}

			$infos = $this->getInfo($variationName);

			// $variations[$id]['display_name'] = $infos->display_name;

			$html = null;
			$html .= __('Limit Reached for: ').$infos->display_name.' ('.$packageUsed.' used)';

			$html .= "<div class=\"m\">";
			$html .= "<div class=\"progress m-t-xs full progress-striped\">";
			$html .= "<div style=\"width: ".$perc."%\" aria-valuemax=\"100\" aria-valuemin=\"0\" aria-valuenow=\"".$perc."\" role=\"progressbar\" class=\"active progress-bar ".$progressBar."\">";
			$html .= $perc_label;
			$html .= "</div>";
			$html .= "</div>";
			$html .= "</div>";

			$variations[$id]['bar'] = $html;

		}

		if (@count($variations) == 0)
			return;


		$html = null;
		$html .= "<div class=\"row\">";

		$html .= "<div class=\"modal fade in\" id=\"packagesModal\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\" style=\"display: block; padding-right: 15px;\">";
		// $html .= "<div class=\"modal hmodal-danger fade in\" id=\"packagesModal\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\" style=\"display: block; padding-right: 15px;\">";
		$html .= "<div class=\"modal-dialog\">";
		$html .= "<div class=\"modal-content\">";
		$html .= "<div class=\"modal-header\">";
		$html .= "<h4 class=\"modal-title text-white\">Limit Reached</h4>";
		$html .= "</div>";
		$html .= "<div class=\"modal-body\">";
		$html .= "<div class=\"row\">";
		$html .= "<div class=\"col-md-12 text-left\">";

		foreach ($variations as $variation) {
			$html .= $variation['bar'];
		}

		$html .= "</div>";
		$html .= "</div>";
		$html .= "<div class=\"row\">";
		$html .= "<div class=\"col-md-12 text-right\">";
		$html .= "<button type=\"button\" class=\"btn btn-default\" data-dismiss=\"modal\">Close</button>";
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
