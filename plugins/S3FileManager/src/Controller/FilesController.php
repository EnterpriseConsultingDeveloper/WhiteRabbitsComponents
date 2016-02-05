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
    public function explore($bucket)
    {
        $file = $this->Files->newEntity();
        $folders = $this->Files->Folders->find('list', ['limit' => 200]);
        $this->set(compact('file', 'folders'));
        $this->set('_serialize', ['file']);


        $folders = $this->Files->Folders->find('list', ['limit' => 200]);
        $files = $this->Files->findAllByFolderId(1);

        $this->set(compact('file', 'files', 'folders'));
        $this->set('_serialize', ['file', 'files']);
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
        $file->folder_id = 1;

        if ($this->Files->save($file)) {
            $this->Flash->success(__('The file has been saved.'));
            return $this->redirect(['action' => 'index']);
        } else {
            $this->Flash->error(__('The file could not be saved. Please, try again.'));
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
            $this->Flash->success(__('The file has been deleted.'));
        } else {
            $this->Flash->error(__('The file could not be deleted. Please, try again.'));
        }

        $folders = $this->Files->Folders->find('list', ['limit' => 200]);
        $this->set(compact('file', 'folders'));
        $this->set('_serialize', ['file']);

        //return $this->redirect(['action' => 'explore']);

    }
}


/*
        * // upload.php
// 'images' refers to your file input name attribute
if (empty($_FILES['images'])) {
   echo json_encode(['error'=>'No files found for upload.']);
   // or you can throw an exception
   return; // terminate
}

// get the files posted
$images = $_FILES['images'];

// get user id posted
$userid = empty($_POST['userid']) ? '' : $_POST['userid'];

// get user name posted
$username = empty($_POST['username']) ? '' : $_POST['username'];

// a flag to see if everything is ok
$success = null;

// file paths to store
$paths= [];

// get file names
$filenames = $images['name'];

// loop and process files
for($i=0; $i < count($filenames); $i++){
   $ext = explode('.', basename($filenames[$i]));
   $target = "uploads" . DIRECTORY_SEPARATOR . md5(uniqid()) . "." . array_pop($ext);
   if(move_uploaded_file($images['tmp_name'][$i], $target)) {
       $success = true;
       $paths[] = $target;
   } else {
       $success = false;
       break;
   }
}

// check and process based on successful status
if ($success === true) {
   // call the function to save all data to database
   // code for the following function `save_data` is not
   // mentioned in this example
   save_data($userid, $username, $paths);

   // store a successful response (default at least an empty array). You
   // could return any additional response info you need to the plugin for
   // advanced implementations.
   $output = [];
   // for example you can get the list of files uploaded this way
   // $output = ['uploaded' => $paths];
} elseif ($success === false) {
   $output = ['error'=>'Error while uploading images. Contact the system administrator'];
   // delete any uploaded files
   foreach ($paths as $file) {
       unlink($file);
   }
} else {
   $output = ['error'=>'No files were processed.'];
}

// return a json encoded response for plugin to process successfully
echo json_encode($output);
        */