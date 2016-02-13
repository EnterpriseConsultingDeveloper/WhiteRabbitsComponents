<?php
namespace S3FileManager\Controller;

use S3FileManager\Controller\AppController;
use Aws\S3\S3Client;
use Aws\Credentials\Credentials;
require_once(ROOT .DS. 'src' . DS . 'Lib' . DS . 'aws' . DS .'aws-autoloader.php');
/**
 * Files Controller
 *
 * @property \S3FileManager\Model\Table\FilesTable $Files
 */
class FilesController extends AppController
{

    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('RequestHandler');
    }

    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['Folders']
        ];
        $files = $this->paginate($this->Files);

        $this->set(compact('files'));
        $this->set('_serialize', ['files']);
    }

    /**
     * View method
     *
     * @param string|null $id File id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $file = $this->Files->get($id, [
            'contain' => ['Folders']
        ]);

        $this->set('file', $file);
        $this->set('_serialize', ['file']);
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $file = $this->Files->newEntity();
        if ($this->request->is('post')) {
            $file = $this->Files->patchEntity($file, $this->request->data);
            if ($this->Files->save($file)) {
                $this->Flash->success(__('The file has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The file could not be saved. Please, try again.'));
            }
        }
        $folders = $this->Files->Folders->find('list', ['limit' => 200]);
        $this->set(compact('file', 'folders'));
        $this->set('_serialize', ['file']);
    }

    /**
     * Edit method
     *
     * @param string|null $id File id.
     * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $file = $this->Files->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $file = $this->Files->patchEntity($file, $this->request->data);
            if ($this->Files->save($file)) {
                $this->Flash->success(__('The file has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The file could not be saved. Please, try again.'));
            }
        }
        $folders = $this->Files->Folders->find('list', ['limit' => 200]);
        $this->set(compact('file', 'folders'));
        $this->set('_serialize', ['file']);
    }

    /**
     * Delete method
     *
     * @param string|null $id File id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $file = $this->Files->get($id);
        if ($this->Files->delete($file)) {
            $this->Flash->success(__('The file has been deleted.'));
        } else {
            $this->Flash->error(__('The file could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'index']);
    }

    /**
     * explore method
     *
     * @param string $bucket Bucket name.
     * @return \Cake\Network\Response|null
     *
     */
    public function explore($site, $formField)
    {
        //$this->viewBuilder()->layout('blank'); // Vista per blank
        $this->viewBuilder()->layout('ajax'); // Vista per ajax
        $this->request->session()->write('Auth.User.customer_site', $site);
        //$this->request->session()->write('targetFormFieldName', $formField);

        $file = $this->Files->newEntity();

        $folders = $this->Files->Folders->find('list', [
            'conditions' => array('bucket' => $site),
            'limit' => 200
        ]);

        $actualFolder = $this->Files->Folders->find('all', [
            'conditions' => ['bucket' => $site],
            'order' => ['id' => 'ASC']
        ])->first();

        if($actualFolder == null) {
            $this->Flash->error(__('Please first add a Folder, then upload images!'));
        } else {
            $files = $this->Files->findAllByFolderId($actualFolder->id);
            $this->set(compact('file', 'files', 'folders'));
            $this->set('_serialize', ['file', 'files']);
        }
    }


    /**
     * uploadFile method
     *
     * @param string $bucket Bucket name.
     * @return \Cake\Network\Response|null
     *
     */
    public function uploadFile()
    {
        $file = $this->Files->newEntity();
        $file->file = $_FILES['file'];
        $file->folder_id = $_POST['img_folder'];

        if ($this->Files->save($file)) {
            //$this->Flash->success(__('The file has been saved.'));
            return $this->redirect(['action' => 'index']);
        } else {
            //$this->Flash->error(__('The file could not be saved. Please, try again.'));
        }

        $folders = $this->Files->Folders->find('list', ['limit' => 200]);
        $this->set(compact('file', 'folders'));
        $this->set('_serialize', ['file']);
    }


    /**
     * deleteFile method
     *
     * @param string $bucket Bucket name.
     * @return \Cake\Network\Response|null
     *
     */
    public function deleteFile()
    {
        $id = $this->request->data['key'];
        $this->request->allowMethod(['post', 'delete']);
        $file = $this->Files->get($id);
        if ($this->Files->delete($file)) {
            //$this->Flash->success(__('The file has been deleted.'));
        } else {
            //$this->Flash->error(__('The file could not be deleted. Please, try again.'));
        }

        $folders = $this->Files->Folders->find('list', ['limit' => 200]);
        $this->set(compact('file', 'folders'));
        $this->set('_serialize', ['file']);

        //return $this->redirect(['action' => 'explore']);
    }


    public function getFiles()
    {
        $site = $this->request->session()->read('Auth.User.customer_site');
        //$this->request->session()->write('targetFormFieldName', $formField);


        $folders = $this->Files->Folders->find('list', [
            'conditions' => array('bucket' => $site),
            'limit' => 200
        ]);

        $actualFolder = $this->Files->Folders->find('all', [
            'conditions' => ['bucket' => $site],
            'order' => ['id' => 'ASC']
        ])->first();

        $files = $this->Files->findAllByFolderId($actualFolder->id);
        $this->set(compact('files'));
        $this->set('_serialize', ['files']);

    }

}

