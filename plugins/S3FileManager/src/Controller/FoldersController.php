<?php
namespace S3FileManager\Controller;

use S3FileManager\Controller\AppController;

/**
 * Folders Controller
 *
 * @property \S3FileManager\Model\Table\FoldersTable $Folders
 */
class FoldersController extends AppController
{

    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['ParentFolders']
        ];
        $folders = $this->paginate($this->Folders);

        $this->set(compact('folders'));
        $this->set('_serialize', ['folders']);
    }

    /**
     * View method
     *
     * @param string|null $id Folder id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $folder = $this->Folders->get($id, [
            'contain' => ['ParentFolders', 'Files', 'ChildFolders']
        ]);

        $this->set('folder', $folder);
        $this->set('_serialize', ['folder']);
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $folder = $this->Folders->newEntity();
        if ($this->request->is('post')) {
            $folder = $this->Folders->patchEntity($folder, $this->request->data);
            if ($this->Folders->save($folder)) {
                $this->Flash->success(__('The folder has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The folder could not be saved. Please, try again.'));
            }
        }
        $parentFolders = $this->Folders->ParentFolders->find('list', ['limit' => 200]);
        $this->set(compact('folder', 'parentFolders'));
        $this->set('_serialize', ['folder']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Folder id.
     * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $folder = $this->Folders->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $folder = $this->Folders->patchEntity($folder, $this->request->data);
            if ($this->Folders->save($folder)) {
                $this->Flash->success(__('The folder has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The folder could not be saved. Please, try again.'));
            }
        }
        $parentFolders = $this->Folders->ParentFolders->find('list', ['limit' => 200]);
        $this->set(compact('folder', 'parentFolders'));
        $this->set('_serialize', ['folder']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Folder id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $folder = $this->Folders->get($id);
        if ($this->Folders->delete($folder)) {
            $this->Flash->success(__('The folder has been deleted.'));
        } else {
            $this->Flash->error(__('The folder could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'index']);
    }
}
