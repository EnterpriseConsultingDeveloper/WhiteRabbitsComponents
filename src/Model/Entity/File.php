<?php
namespace S3FileManager\Model\Entity;

use Cake\ORM\Entity;

/**
 * File Entity.
 *
 * @property int $id
 * @property string $name
 * @property string $original_filename
 * @property string $mime_type
 * @property string $size
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 * @property int $folder_id
 * @property \S3FileManager\Model\Entity\Folder $folder
 */
class File extends Entity
{

	/**
	 * Fields that can be mass assigned using newEntity() or patchEntity().
	 *
	 * Note that when '*' is set to true, this allows all unspecified fields to
	 * be mass assigned. For security purposes, it is advised to set '*' to false
	 * (or remove it), and explicitly make individual fields accessible as needed.
	 *
	 * @var array
	 */
	protected $_accessible = [
		'*' => true,
		'id' => false,
	];

	protected function _setName($name)
	{
		return str_replace(" ", "", $name);
	}

	protected function _setOriginalFilename($original_filename)
	{
		return str_replace(" ", "", $original_filename);
	}

}
