<?php
namespace S3FileManager\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use S3FileManager\Model\Entity\Folder;

/**
 * Folders Model
 *
 * @property \Cake\ORM\Association\BelongsTo $ParentFolders
 * @property \Cake\ORM\Association\HasMany $Files
 * @property \Cake\ORM\Association\HasMany $ChildFolders
 */
class FoldersTable extends Table
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

        $this->table('folders');
        $this->displayField('name');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Tree');

        $this->belongsTo('ParentFolders', [
            'className' => 'S3FileManager.Folders',
            'foreignKey' => 'parent_id'
        ]);
        $this->hasMany('Files', [
            'foreignKey' => 'folder_id',
            'className' => 'S3FileManager.Files'
        ]);
        $this->hasMany('ChildFolders', [
            'className' => 'S3FileManager.Folders',
            'foreignKey' => 'parent_id'
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
            ->allowEmpty('lft');

        $validator
            ->allowEmpty('rght');

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
        $rules->add($rules->existsIn(['parent_id'], 'ParentFolders'));
        return $rules;
    }

    /**
     * Returns the root folder for a specified site
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function getRootFolder($site)
    {
        $rootFolder = $this->find('all', [
            'conditions' => ['bucket' => $site, 'parent_id IS' => null],
            'order' => ['id' => 'ASC']
        ])->first();

        if($rootFolder == null) { //Creating root Folder for this site
            $folder = $this->newEntity();
            $folder->name = '/';
            $folder->bucket = $site;
            $this->save($folder);
            $rootFolder = $folder;
        }

        return $rootFolder;
    }

}




