<?php
namespace S3FileManager\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use S3FileManager\Model\Entity\File;

/**
 * Files Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Folders
 */
class FilesTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('files');
        $this->displayField('name');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->addBehavior('S3FileManager.Uploadable', [
            'file' => [
                'fields' => [
                    'type' => 'mime_type',
                    'size' => 'size',
                    'originalFilename' => 'original_filename',
                ],
                'fileName' => '{GENERATEDKEY}',
                'field' => 'id',
                'path' => ''
            ],
        ]);

        $this->belongsTo('Folders', [
            'foreignKey' => 'folder_id',
            'joinType' => 'INNER',
            'className' => 'S3FileManager.Folders'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->add('id', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('id', 'create');

        $validator
            ->requirePresence('name', 'create')
            ->notEmpty('name');

        $validator
            ->allowEmpty('original_filename');

        $validator
            ->allowEmpty('mime_type');

        $validator
            ->allowEmpty('size');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['folder_id'], 'Folders'));
        return $rules;
    }
}
