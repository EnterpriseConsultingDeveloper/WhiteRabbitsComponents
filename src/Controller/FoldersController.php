<?php
namespace S3FileManager\Controller;

use S3FileManager\Controller\AppController;
use Cake\ORM\TableRegistry;

/**
 * Folders Controller
 *
 * @property \S3FileManager\Model\Table\FoldersTable $Folders
 */
class FoldersController extends AppController
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
  /**
   * folderSize method: return the folder size in bytes occupied by files in it
   * @param $site
   * @return int
   */
  public function folderSize($site)
  {
    $this->viewBuilder()->layout('ajax'); // Vista per ajax

    $resultFolders = $this->Folders->find()
      ->select(['id'])
      ->where(['bucket' => $site]);

    if (!$resultFolders) {
      return 0;
    }

    $folderIDS = null;
    foreach ($resultFolders as $folder) {
      if (isset($folder->id))
        $folderIDS[] = $folder->id;
    }

    $this->loadModel('Files');

    $result = $this->Files->find()->where(['folder_id IN ' => $folderIDS]);
    $result->select(['sum' => $result->func()->sum('size')]);

    $sumSize = $result->toArray();

    return $sumSize[0]->sum;

  }

  /**
   * folderList method (ajax)
   *
   */
  public function folderList()
  {
    $this->viewBuilder()->layout('ajax'); // Vista per ajax
    $site = $this->request->query('site');
    $selectedFolder = $this->request->query('sel');

    if ($selectedFolder == null) {
      $rootFolder = $this->Folders->getRootFolder($site);
      $selectedFolder = $rootFolder->id;
    }

    $this->request->session()->write('Auth.User.fc_customer_site', $site);

    $folderList = $this->Folders->find('threaded', [
      'conditions' => ['bucket' => $site]])->toArray();

    $this->set(compact('folderList', 'selectedFolder'));
  }


  /**
   * rename method (ajax)
   */
  public function rename()
  {
    $this->viewBuilder()->layout('ajax'); // Vista per ajax

    $id = $this->request->data('id');
    $name = $this->request->data('text');

    $folder = $this->Folders->get($id);
    $folder->name = $name;

    if ($this->Folders->save($folder)) {
      header('Content-Type: application/json');
      echo json_encode(array('id' => $id));
    } else {
      //Error
    }
  }

  /**
   * addFolder method (ajax)
   */
  public function addFolder()
  {
    $name = $this->request->data('text');
    $parentId = $this->request->data('pId');

    $parentFolder = $this->Folders->get($parentId);

    $folder = $this->Folders->newEntity();

    $folder->name = $name;
    $folder->parent_id = $parentId;
    $folder->bucket = $parentFolder->bucket;

    if ($this->Folders->save($folder)) {
      $this->Folders->recover(); // Need to recover folders tree
      header('Content-Type: application/json');
      echo json_encode(array('id' => $folder->id));

      //return 'Created folder with id: ' . $folder->id;
    } else {
      //Error
    }
  }


  /**
   * deleteFolder method (ajax)
   */
  public function deleteFolder()
  {
    $id = $this->request->data('id');
    if (substr($id, 0, 1) === "j" ) {
      // New folder on frontend not yet created on DB
      header('Content-Type: application/json');
      echo json_encode(array('id' => $id));
    } else {
      $folder = $this->Folders->get($id);
      if ($this->Folders->delete($folder)) {
        $this->Folders->recover(); // Need to recover folders tree
        header('Content-Type: application/json');
        echo json_encode(array('id' => $folder->id));
      } else {
        //Error
      }
    }

  }


  /**
   * @param $needle
   * @param $haystack
   * @return bool|int|string
   */
  function recursive_array_search($needle, $haystack) {
    foreach($haystack as $key => $value) {
      $current_key = $key;
      if($needle===$value OR (is_array($value) && recursive_array_search($needle,$value) !== false)) {
        return $current_key;
      }
    }
    return false;
  }
}
