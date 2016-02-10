<?php
namespace S3FileManager\Model\Entity;

use Cake\ORM\Entity;

/**
 * Folder Entity.
 *
 * @property int $id
 * @property string $name
 * @property int $lft
 * @property int $rght
 * @property int $parent_id
 * @property \S3FileManager\Model\Entity\ParentFolder $parent_folder
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 * @property \S3FileManager\Model\Entity\File[] $files
 * @property \S3FileManager\Model\Entity\ChildFolder[] $child_folders
 */
class Folder extends Entity
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
}
