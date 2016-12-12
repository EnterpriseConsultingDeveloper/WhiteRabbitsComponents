<?php
namespace S3FileManager\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use S3FileManager\Model\Entity\File;
use Cake\Network\Session;

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

    /**
     * afterSave callback
     *
     * @param \Cake\Event\Event $event Event.
     * @param \Cake\ORM\Entity $entity The Entity who has been saved.
     * @param array $options Options.
     * @return void
     */
    public function afterDelete($event, $entity, $options)
    {

//      $entity->name = 982;

    }

    /**
     * Make a file public or private
     *
     * @param $id The file to be modified.
     * @return \Cake\Datasource\EntityInterface
     */
    public function changeStatus($id)
    {
        $file = $this->get($id);
        $file->public = !$file->public;
        return $this->save($file);
    }
}
