<?php
namespace S3FileManager\Controller;

use Cake\Core\Exception\Exception;
use Cake\Event\Event;
use Cake\Filesystem\File;
use Cake\Network\Exception\NotFoundException;
use Cake\Routing\Router;
use S3FileManager\Utils\WRS3Client;
use S3FileManager\Utils\WRClient;
use \Gumlet\ImageResize;

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

    public function beforeFilter(Event $event)
    {
        // Allow only the view and index actions.
        //debug($event->subject()->request->params['action']); die;
        if ($this->Auth != null) {
            $this->Auth->allow(['media', 'media_auth', 'createProjectFolder', 'createProjectFolder', 'uploadFileToResizeFolder', 'uploadFile']);
            // $this->Auth->allow(['explore']);
        }
        if (!($event->subject()->request->params['action'] == 'media') || ($event->subject()->request->params['action'] == 'media_auth')) {
            parent::beforeFilter($event);
        }

    }


    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
//  public function index()
//  {
//    $this->paginate = [
//        'contain' => ['Folders']
//    ];
//    $files = $this->paginate($this->Files);
//
//    $this->set(compact('files'));
//    $this->set('_serialize', ['files']);
//  }

    /**
     * View method
     *
     * @param string|null $id File id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
//  public function view($id = null)
//  {
//    $file = $this->Files->get($id, [
//        'contain' => ['Folders']
//    ]);
//
//    $this->set('file', $file);
//    $this->set('_serialize', ['file']);
//  }

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
//  public function edit($id = null)
//  {
//    $file = $this->Files->get($id, [
//        'contain' => []
//    ]);
//    if ($this->request->is(['patch', 'post', 'put'])) {
//      $file = $this->Files->patchEntity($file, $this->request->data);
//      if ($this->Files->save($file)) {
//        $this->Flash->success(__('The file has been saved.'));
//        return $this->redirect(['action' => 'index']);
//      } else {
//        $this->Flash->error(__('The file could not be saved. Please, try again.'));
//      }
//    }
//    $folders = $this->Files->Folders->find('list', ['limit' => 200]);
//    $this->set(compact('file', 'folders'));
//    $this->set('_serialize', ['file']);
//  }

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
    public function explore($site, $actualFolder = null)
    {
        $this->viewBuilder()->layout('ajax'); // Vista per ajax
        $this->request->session()->write('Auth.User.fc_customer_site', $site);
        $completeUrl = 'https://' . $site . SUITE_DOMAIN_THIRD_LEVELS . $this->request->domain();
        $typeImage = (isset($this->request->query['box'])) ? $this->request->query['box'] : null; // serve per far funzionare corretamente il plugin grafico delle landing e newsletter

        $file = $this->Files->newEntity();

        $folderList = $this->Files->Folders->find('treeList', [
            'conditions' => ['bucket' => $site]]);

        $rootFolder = $this->Files->Folders->getRootFolder($site);

        if ($actualFolder == null) {
            $actualFolder = $rootFolder->id;
        }
        $actualFolderEntity = $this->Files->Folders->get($actualFolder);
        $actualFolderName = $actualFolderEntity->name;
        $files = $this->Files->findAllByFolderId($actualFolder);

        $initialPreview = $initialPreviewConfig = [];
        $this->doInitialPreviews($files, $site, $initialPreview, $initialPreviewConfig);

        $this->set(compact('file', 'files', 'folders',
            'folderList', 'actualFolder', 'actualFolderName',
            'initialPreview', 'initialPreviewConfig', 'completeUrl', 'typeImage'));

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

        $this->viewBuilder()->layout('ajax'); // Vista per ajax

        if ($this->request->data['session']) {

            foreach ($this->request->data['session'] as $key => $value) {
                $this->request->session()->write('Auth.User.' . $key, $value);
            }

        }


        // Undefined | Multiple Files | $_FILES Corruption Attack
        // If this request falls under any of them, treat it invalid.
        if (
            !isset($_FILES['file']['error']) ||
            is_array($_FILES['file']['error'])
        ) {
            header('HTTP/1.0 400 Bad error');
            echo json_encode('Error loading file...');
            throw new \RuntimeException('Invalid parameters.');
        }

        /*
        try {
          throw new Exception("Some error message", 30);
        } catch(Exception $e) {
          echo "The exception code is: " . $e->getCode();
        }
    */
        // Check $_FILES['upfile']['error'] value.
        switch ($_FILES['file']['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                header('HTTP/1.0 400 Bad error');
                echo json_encode('Error loading file...');
                throw new \RuntimeException('No file sent.');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                header('HTTP/1.0 400 Bad error');
                echo json_encode('Error loading file...');
                throw new \RuntimeException('Exceeded filesize limit.');
            default:
                header('HTTP/1.0 400 Bad error');
                echo json_encode('Error loading file...');
                throw new \RuntimeException('Unknown errors.');
        }

        // You should also check filesize here. 10MB?
        if ($_FILES['file']['size'] > 10000000) {
            throw new \RuntimeException('Exceeded filesize limit.');
        }

        $file->file = $_FILES['file'];
        $site = $this->extractSite();
        $file->folder_id = ($_POST['img_folder'] == 'resized') ? $this->getFolderResized($site) : $_POST['img_folder'];

        $path = $_FILES['file']['name'];
        $file->extension = pathinfo($path, PATHINFO_EXTENSION);
        $file->public = 1;
        $file->original_filename = $path;

        $file->path = '/Resized/'; //$this->getFolderPath($file);
        $file->name = $path;

        $saved = $this->Files->save($file);
        if ($saved) {

            $crmManager = new FoldersController();
            $site = $this->request->session()->read('Auth.User.fc_customer_site');
            $limit = $crmManager->folderSize($site);
//      $http = new WRClient();
//      $response = $http->post(API_PATH . API_METHOD_SET_LIMITS, [
//          'customerID' => $this->request->session()->read('Auth.User.customer_id'),
//          'limit' => 'repository_space',
//          'value' => $limit + $_FILES['file']['size'],
//          'globalValue' => true
//      ],
//          [
//              'headers' => ['Authorization' => 'Bearer '.$this->request->session()->read('Auth.User.token'), 'Accept' => 'application/json']
//          ]);

            // distruggo le sessioni create e evito il render del CTP
            if (isset($this->request->data['render'])) {
                if ($this->request->data['render'] == false) {
                    $this->autoRender = false;
                    echo json_encode($saved);
                    exit;
                }
            }

            header('Content-Type: application/json');
            echo json_encode('Loaded...');

        } else {
            header('HTTP/1.0 400 Bad error');
            echo json_encode('Error loading file...');
        }


    }

    /**
     * uploadFileToResizeFolder function
     */
    public function uploadFileToResizeFolder()
    {
        $this->viewBuilder()->layout('ajax'); // Vista per ajax

        $site = $this->extractSite();
        $folderId = $this->getFolderResized($site);

        $imgName = $this->request->data('imgName');

        // Check duplicate name
        $fileNameExists = $this->Files->find('all')
            ->select(['id'])
            ->where(['Files.folder_id' => $folderId])
            ->where(['Files.original_filename' => $imgName]);

        if ($fileNameExists) {
            $file = $this->Files->newEntity();
            $file->file = $this->request->data('imgData');
            $this->loadModel('Files'); // It's necessary because the name "media" was reserved

            try {
                $file->folder_id = $folderId;

                $path = $imgName;
                $file->extension = pathinfo($path, PATHINFO_EXTENSION);
                $file->public = 1;
                $file->original_filename = $path;
                $file->name = $path;

                $file->path = '/Resized/';


                $idFIle = $this->Files->save($file);
                echo json_encode($idFIle);
                exit;

//        if () {
//          // Return path + name
//          echo json_encode(__('Saved!'));
//          exit;
//        } else {
//          echo json_encode(__('The file could not be saved. Please, try again.'));
//          exit;
//        }

            } catch (Exception $e) {
                echo 'Error: ', $e->getMessage();
                exit;
            }

        } else {
            echo 'An error occurred.';
            exit;
        }
        return;
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
        $size = $file->size;
        $folderId = $file->folder_id;

        if ($this->Files->delete($file)) {

            $fc = new FoldersController();
            $site = $this->request->session()->read('Auth.User.fc_customer_site');
            $limit = $fc->folderSize($site);

            $http = new WRClient();
            $response = $http->post(API_PATH . API_METHOD_SET_LIMITS, [
                'customerID' => $this->request->session()->read('Auth.User.customer_id'),
                'limit' => 'repository_space',
                'value' => $limit - $size,
                'globalValue' => true
            ],
                [
                    'headers' => ['Authorization' => 'Bearer ' . $this->request->session()->read('Auth.User.token'), 'Accept' => 'application/json']
                ]);

//      $files = $this->Files->findAllByFolderId($folderId);
//      $initialPreview = $initialPreviewConfig = [];
//      $this->doInitialPreviews($files, $site, $initialPreview, $initialPreviewConfig);
//
//      $this->set(compact('files', 'initialPreview', 'initialPreviewConfig'));
//      $this->set('_serialize', ['file', 'files']);

            header('Content-Type: application/json');
            echo json_encode('File deleted...');
        } else {
            //error
        }
    }


    /**
     * deleteFile method
     *
     * @param string $bucket Bucket name.
     * @return \Cake\Network\Response|null
     *
     */
    public function changeFolder()
    {
        $site = $this->request->session()->read('Auth.User.fc_customer_site');

        $id = $this->request->data['id'];
        $newFolderId = $this->request->data['folder'];

        if ($newFolderId == '') { // Moved on root folder
            $rootFolder = $this->Files->Folders->getRootFolder($site);
            $newFolderId = $rootFolder->id;
        }

        $file = $this->Files->get($id);
        $file->folder_id = $newFolderId;

        $file->path = $this->getFolderPath($file);

        if ($this->Files->save($file)) {
            $files = $this->Files->findAllByFolderId($newFolderId);
            $initialPreview = $initialPreviewConfig = [];
            $this->doInitialPreviews($files, $site, $initialPreview, $initialPreviewConfig);

            $this->set(compact('files', 'initialPreview', 'initialPreviewConfig'));
            $this->set('_serialize', ['file', 'files']);
        } else {
            //error
        }
    }


    /**
     * makePublic method
     *
     * @param string $bucket Bucket name.
     * @return \Cake\Network\Response|null
     *
     */
    public function changeStatus()
    {
        $id = $this->request->data('id');
        $file = $this->Files->changeStatus($id);

        if ($file) {
            header('Content-Type: application/json');
            echo json_encode($file->public);
        } else {
            //Error...
        }

    }

    public function getFiles()
    {
        $site = $this->request->session()->read('Auth.User.fc_customer_site');

        $actualFolder = $this->Files->Folders->find('all', [
            'conditions' => ['bucket' => $site],
            'order' => ['id' => 'ASC']
        ])->first();

        $files = $this->Files->findAllByFolderId($actualFolder->id);
        $this->set(compact('files'));
        $this->set('_serialize', ['files']);

    }

    /**
     * Restituisce i file S3FileManager della root (o di una folder se id_folder viene passato come params) da chiamato AJAX
     * @param ajax call
     * @return array
     * @author 09/02/2018 Fabio Mugnano <mugnano@enterprise-consulting.it>
     */

    public function getFilesExplore()
    {
        $id_folder = $this->request->data('id_folder');
        $site = $this->request->session()->read('Auth.User.fc_customer_site');
        if ($id_folder == null || $id_folder == 'undefined') {
            $actualFolder = $this->Files->Folders->find('all', [
                'conditions' => ['bucket' => $site],
                'order' => ['id' => 'ASC']
            ])->first();
            $id_folder = $actualFolder->id;
        }

        $files = $this->Files->findAllByFolderId($id_folder);
        $this->set(compact('files'));
        $this->set('_serialize', ['files']);

    }


    /**
     * getActualFolderFiles method
     * @param null $actualFolder
     *
     * Useful for retrieve files in ajax
     *
     */
    public function getActualFolderFiles($actualFolder = null)
    {
        $this->viewBuilder()->layout('ajax'); // Vista per ajax
        $site = $this->request->session()->read('Auth.User.fc_customer_site');

        if ($actualFolder == null || $actualFolder == 'undefined') {
            $actualFolder = $this->Files->Folders->find('all', [
                'conditions' => ['bucket' => $site],
                'order' => ['id' => 'ASC']
            ])->first()->id;
        }

        $files = $this->Files->findAllByFolderId($actualFolder);

        $initialPreview = $initialPreviewConfig = [];
        $this->doInitialPreviews($files, $site, $initialPreview, $initialPreviewConfig);
        $this->set(compact('files', 'initialPreview', 'initialPreviewConfig'));
    }


    /**
     * Media method
     * Realize proxy functions
     *
     * @param string|null $completePath File path with filename.
     * @return \Cake\Network\Response|null
     * @throws NotFoundException When file not found.
     *
     * expect
     * files/media/sadsadasdas/iutry/image.png
     * where image path is /sadsadasdas/iutry/image.png
     * sadsadasdas/iutry/ = complete folder path
     * image.png = image name and extension
     */
    public function media($completePath = null)
    {

        $this->viewBuilder()->layout('ajax'); // Vista per ajax

//        if (isset($_GET['first_read']) && isset($_GET['ref_id'])) {
//            $ref_id = $_GET['ref_id'];
//            $MtNewsletters = $this->loadModel("MarketingTools.MtNewsletters");
//            $MtNewsletters->readImg($ref_id);
//            return;
//        }

        if ($completePath == null) {
            throw new NotFoundException('File not found.');
        }

        $site = $this->extractSite();
        $lastSlashPos = strrpos($completePath, '/');
        $firstSlashPos = strpos($completePath, '/');
        if (!$lastSlashPos && !$firstSlashPos) { // File saved in root ("/" folder)
            $fileName = $completePath;
            $path = '/';
        } else {
            $fileName = substr($completePath, $lastSlashPos + 1, strlen($completePath));
            $path = '/' . substr($completePath, 0, $lastSlashPos + 1);
        }

        $this->loadModel('Files'); // It's necessary because the name "media" was reserved
        $this->loadModel('Folders');
        try {
            $folders = $this->Folders->find('all')
                ->select(['id'])
                ->where(['Folders.bucket' => $site]);

            $file = $this->Files->find()
                ->where(['Files.path' => $path])
                ->where(['Files.original_filename' => $fileName])
                ->where(['folder_id IN' => $folders])
                ->first();
            //debug($path);debug($fileName);debug($folders->toArray());debug($file->toArray()); die;
            if (!$file) {
                throw new NotFoundException('File not found.');
            }

            if ($file->public == 0) {
                throw new NotFoundException('File not public.');
            }

            $localFile = $this->sendFile($file->file, $fileName, $file->id, $site);

            //$this->request->trustProxy = true;
            // Set a single header
            //$this->response->header('Location', 'http://giangionet.whiterabbit.online/s3_file_manager/Files/media');
            //$this->response->header('Location', 'http://giangionet.whiterabbit.online/s3_file_manager/Files/media');
            //debug($localFile->header([])); die;

            return $localFile; //$this->sendFile2($file->file, $fileName, $file->id, $site);

        } catch (Exception $e) {
            echo 'Error: ', $e->getMessage();
        }

    }

    /**
     * media_auth method m
     * Messa pezza sotto indicazione di Raf prima di deicdere bene i permessi. La funzione è una copia di media ma invece di controllare se
     * file è pubblico controlla solo se si è loggati nella suite e permette il download ai soli utenti loggati
     *
     * @param string|null $completePath File path with filename.
     * @return \Cake\Network\Response|null
     * @throws NotFoundException When file not found.
     * @author 09/02/2018 Fabio Mugnano <mugnano@enterprise-consulting.it>
     *
     * expect
     * files/media/sadsadasdas/iutry/image.png
     * where image path is /sadsadasdas/iutry/image.png
     * sadsadasdas/iutry/ = complete folder path
     * image.png = image name and extension
     */
    public function media_auth($completePath = null)
    {
        $this->viewBuilder()->layout('ajax'); // Vista per ajax
//        if (isset($_GET['first_read']) && isset($_GET['ref_id'])) {
//            $ref_id = $_GET['ref_id'];
//            $MtNewsletters = $this->loadModel("MarketingTools.MtNewsletters");
//            $MtNewsletters->readImg($ref_id);
//            return;
//        }

        if ($completePath == null) {
            throw new NotFoundException('File not found.');
            return;
        }

        $site = $this->extractSite();
        $lastSlashPos = strrpos($completePath, '/');
        $firstSlashPos = strpos($completePath, '/');
        if (!$lastSlashPos && !$firstSlashPos) { // File saved in root ("/" folder)
            $fileName = $completePath;
            $path = '/';
        } else {
            $fileName = substr($completePath, $lastSlashPos + 1, strlen($completePath));
            $path = '/' . substr($completePath, 0, $lastSlashPos + 1);
        }

        $this->loadModel('Files'); // It's necessary because the name "media" was reserved
        $this->loadModel('Folders');
        try {
            $folders = $this->Folders->find('all')
                ->select(['id'])
                ->where(['Folders.bucket' => $site]);

            $file = $this->Files->find()
                ->where(['Files.path' => $path])
                ->where(['Files.original_filename' => $fileName])
                ->where(['folder_id IN' => $folders])
                ->first();
            //debug($path);debug($fileName);debug($folders->toArray());debug($file->toArray()); die;

            if (!$file) {
                throw new NotFoundException('File not found.');
            }

            //controllo la connessione sulla suite


            if ($file->public == 0) {
                if (!$this->request->session()->read('Auth.User.customer_id')) {
                    throw new Exception('No Auth ');
                }
            }


            $localFile = $this->sendFile($file->file, $fileName, $file->id, $site);

            return $localFile; //$this->sendFile2($file->file, $fileName, $file->id, $site);

        } catch (Exception $e) {
            echo 'Error: ', $e->getMessage();
        }

    }


    /**
     * @return mixed
     */
    private function getFolderResized($site)
    {
        $this->loadModel('S3FileManager.Folders');
        try {
            $rootFolder = $this->Folders->getRootFolder($site);
            $rootFolderId = $rootFolder->id;

            $folder = $this->Folders->find('all')
                ->select(['id'])
                ->where(['Folders.bucket' => $site])
                ->where(['Folders.name' => 'Resized'])
                ->where(['Folders.parent_id' => $rootFolderId])
                ->first();

            if ($folder != null) {
                return $folder->id;
            } else {
                // Create folder 'Resized'
                $folder = $this->Folders->newEntity();

                $folder->name = 'Resized';
                $folder->parent_id = $rootFolderId;
                $folder->bucket = $site;

                if ($this->Folders->save($folder)) {
                    //$this->Folders->recover(); // Need to recover folders tree
                    return $folder->id;
                }
            }

        } catch (Exception $e) {
            echo 'Error: ', $e->getMessage();
        }
        return null;
    }


    /**
     * createProjectFolder function
     * Create a folder under the Project Folders
     */
    public function createProjectFolder()
    {
        $this->viewBuilder()->layout('ajax'); // Vista per ajax

        try {
            $site = $this->request->query('s');
            $projectName = $this->request->query('p');
            $folderId = $this->getFolderProject($site, $projectName);

            $this->set(compact('folderId'));
            header('Content-Type: application/json');
            echo json_encode($folderId);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode('Error');
        }
    }

    /**
     * @return mixed
     */
    private function getFolderProject($site, $projectName)
    {
        //debug($site);debug($projectName); die;
        $this->loadModel('Folders');
        try {
            $rootFolder = $this->Files->Folders->getRootFolder($site);
            $rootFolderId = $rootFolder->id;

            $projectFolder = $this->Folders->find('all')
                ->select(['id'])
                ->where(['Folders.bucket' => $site])
                ->where(['Folders.name' => 'Projects folder'])
                ->where(['Folders.parent_id' => $rootFolderId])
                ->first();

            if (empty($projectFolder)) {
                // Create folder 'Projects folder'
                $projectFolder = $this->Folders->newEntity();

                $projectFolder->name = 'Projects folder';
                $projectFolder->parent_id = $rootFolderId;
                $projectFolder->bucket = $site;

                if (!$this->Folders->save($projectFolder)) {
                    throw new Exception('Cannot create Projects folder.');
                }
            }

            $folder = $this->Folders->find('all')
                ->select(['id'])
                ->where(['Folders.bucket' => $site])
                ->where(['Folders.name' => $projectName])
                ->where(['Folders.parent_id' => $projectFolder->id])
                ->first();

            if ($folder != null) {
                return $folder->id;
            } else {
                // Create folder 'Project'
                $folder = $this->Folders->newEntity();

                $folder->name = $projectName;
                $folder->parent_id = $projectFolder->id;
                $folder->bucket = $site;

                if ($this->Folders->save($folder)) {
                    return $folder->id;
                } else {
                    throw new Exception('Cannot create folder ' . $projectName . '.');
                }
            }

        } catch (Exception $e) {
            //echo 'Error: ', $e->getMessage();
            return null;
        }
        return null;
    }

    /**
     * @return mixed
     */
    private function extractSite()
    {
        $subdomains = $this->request->subdomains();
        $site = $subdomains[0];
        if ($site == 'content' || $site == 'editorial') {
            $site = $this->request->session()->read('Auth.User.fc_customer_site');
        }
        return $site;
    }


    /**
     * mediaInfo method
     * Return file information and the correct link for proxy
     */
    public function mediaInfo()
    {
        // ie return
        // files/media/sadsadasdas/iutry/image.png

        $this->viewBuilder()->layout('ajax'); // Vista per ajax
        $fileId = $this->request->data('id');

        $this->loadModel('Files');
        $file = $this->Files->get($fileId);

        $info = [];
        $info['id'] = $file->id;
        $info['path'] = $this->getProxyPath($file);
        $info['public'] = $file->public;
        $info['type'] = $file->mime_type;
        $info['size'] = intval($file->size / 1000);
        $info['ext'] = $file->extension;
        $info['name'] = $file->original_filename;

        header('Content-Type: application/json');
        echo json_encode($info);
    }


    /**
     * @param $file
     * @return string
     */
    private function getProxyPath($file)
    {
        return $file->path . $file->original_filename;
    }

    /**
     * @param $path
     * @param $fileName
     * @param $fileId
     * @return \Cake\Network\Response|null
     */
    private function sendFile($path, $fileName, $fileId, $bucketName)
    {
        $S3Client = new WRS3Client();
        $plainUrl = $S3Client->getObjectUrl($bucketName, $path);
        
        $image = $this->resizeImageFromString(@file_get_contents($plainUrl));
        
        $this->response = $this->response->withType(image_type_to_mime_type(exif_imagetype($plainUrl)));
        
        $this->response->body(function () use ($image) {
            return $image;
        });
        return $this->response;
/*
        // $tempPath = ROOT . DS . 'tmp' . DS . 'cache' . DS . $fileId . $fileName;
        $tempPath = tempnam(ROOT . DS . 'tmp' . DS . 'cache', $fileId );
        rename($tempPath, $tempPath.$fileName);
        $tempPath = $tempPath.$fileName;
        
        $file = new File($tempPath, false, 0644);
        $file->write(@file_get_contents($plainUrl));

        $tempPathResize = $this->resizeImage($tempPath, $fileName, $fileId);
        if ($tempPathResize != false) {
          $file->delete();
          $tempPath = $tempPathResize;
        }

        //debug($this->request->query['resize']);
        $this->response->file($tempPath);

        return $this->response;
 */
    }

        /**
     * Metodo per fare il resize delle immagini
     * esempio:
     * - urlimage?w=100 ridimensione l'immagine a 100 di width mantenendo il ratio
     * - urlimage?h=100 ridimensione l'immagine a 100 di height mantenendo il ratio
     * - urlimage?r=100x50 ridimensione l'immagine a 100 di width e 50 di height
     * - urlimage?s=50 ridimensione l'immagine e la scala del 50%
     *
     * @param $rawimage string immagine originale
     * @return string immagine ridimensionata
     */
    private function resizeImageFromString($rawimage)
    {

      $query = $this->request->query;

      if (count($query) == 0) {
        return $rawimage;
      }

      $valueAccepted = ['w' => null, 'h' => null, 'r' => null, 's' => null];
      foreach ($query as $key => $value) {
        if (!array_key_exists($key, $valueAccepted)) {
          return $rawimage;
        }
      }

      $image = ImageResize::createFromString($rawimage);

      // ridimensiona witdh
      if (isset($query['w'])) {
        $image->resizeToWidth($query['w']);
      }

      // ridimensiona height
      if (isset($query['h'])) {
        $image->resizeToHeight($query['h']);
      }

      // ridimensiona witdh x height
      if (isset($query['r'])) {
        $resize = explode("x", $query['r']);
        if (count($resize) == 2) {
        	$image->resize($resize[0], $resize[1]);
        }
      }

      // ridimensiona in scale
      if (isset($query['s'])) {
        $image->scale($query['s']);
      }

      return $image->getImageAsString();
    }
    
    /**
     * Metodo per fare il resize delle immagini
     * esempio:
     * - urlimage?w=100 ridimensione l'immagine a 100 di width mantenendo il ratio
     * - urlimage?h=100 ridimensione l'immagine a 100 di height mantenendo il ratio
     * - urlimage?r=100x50 ridimensione l'immagine a 100 di width e 50 di height
     * - urlimage?s=50 ridimensione l'immagine e la scala del 50%
     *
     * @param $tempPath
     * @param $fileName
     * @param $fileId
     * @return $tempPath url fisico del file
     */
    private function resizeImage($tempPath, $fileName, $fileId)
    {

      // $tempPathResize = ROOT . DS . 'tmp' . DS . 'cache' . DS . 'cache'. $fileId .'_resized_'. $fileName;
      $tempPathResize = tempnam(ROOT . DS . 'tmp' . DS . 'cache', $fileId );
      rename($tempPathResize, $tempPathResize.'_resized_'. $fileName);
      $tempPathResize = $tempPathResize.'_resized_'. $fileName;
      
      $query = $this->request->query;

      if ((count($query) == 0) || (!$tempPath)) {
        return;
      }

      $valueAccepted = ['w' => null, 'h' => null, 'r' => null, 's' => null];
      foreach ($query as $key => $value) {
        if (!array_key_exists($key, $valueAccepted)) {
          return;
        }
      }

      $image = new ImageResize($tempPath);

      // ridimensiona witdh
      if (isset($query['w'])) {
        $image->resizeToWidth($query['w']);
      }

      // ridimensiona height
      if (isset($query['h'])) {
        $image->resizeToHeight($query['h']);
      }

      // ridimensiona witdh x height
      if (isset($query['r'])) {
        $resize = explode("x", $query['r']);
        if (count($resize) == 2) {
        	$image->resize($resize[0], $resize[1]);
        }
      }

      // ridimensiona in scale
      if (isset($query['s'])) {
        $image->scale($query['s']);
      }

      $file = new File($tempPath, true, 0644);
      $file->write(@file_get_contents($image->save($tempPathResize)));

      return $tempPathResize;
    }


    /**
     * @param $files
     * @param $site
     * @param $initialPreview
     * @param $initialPreviewConfig
     *
     * Setup initial file previews
     */
    private function doInitialPreviews($files, $site, &$initialPreview, &$initialPreviewConfig)
    {
        $deleteUrl = Router::url([
            'controller' => 'Files',
            'action' => 'deleteFile',
            '_ext' => 'json'
        ]);

        $s3client = new WRS3Client();

        $i = 0;
        foreach ($files as $file) {
            $key = $file->id;
            $initialPreview[$i] = $s3client->createFilePreview($file->file, $site, [
                    'filename' => $file->file,
                    'title' => $file->original_filename,
                    'description' => $file->original_filename,
                    'originalFilename' => $file->original_filename,
                    'id' => $file->id
                ]
            );

            $initialPreviewConfig[$i] = ['caption' => "{$file->original_filename}", 'width' => '120px', 'url' => $deleteUrl, 'key' => $key];
            $i++;
        }

        $this->set(compact('file', 'files', 'folders', 'folderList',
            'callbackFunction', 'actualFolder', 'actualFolderName',
            'initialPreview', 'initialPreviewConfig'));

        $this->set('_serialize', ['file', 'files']);
    }


    /**
     * @param $file
     * @return string
     */

    private function getFolderPath($file)
    {
        $crumbs = $this->Files->Folders->find('path', ['for' => $file->folder_id]);
        $actualFolderPath = '';
        foreach ($crumbs as $crumb) {
            if ($crumb->name == '/')
                $actualFolderPath .= $crumb->name;
            else
                $actualFolderPath .= $crumb->name . '/';
        }
        return $actualFolderPath;
    }

    /**
     * @param $string base64
     * @return string url
     */

    public function convertBase64toImg($base64)
    {

        $re = '/^data:image\/([a-zA-Z]+[a-zA-Z]+);/';
        preg_match_all($re, $base64, $matches, PREG_SET_ORDER, 0);

        $extension = isset($matches[0][1]) ? $matches[0][1] : 'image/jpeg';

        $img = $base64;
        $img = str_replace("data:image/" . $extension . ";base64,", '', $img);
        $img = str_replace(' ', '+', $img);
        $data = base64_decode($img);
        $image = TMP . uniqid() . '.' . $extension;
        $fileUploaded = file_put_contents($image, $data);

        $mime_tipe = mime_content_type($image);
        $fileName = basename($image);
        $fileSize = filesize($image);

        return [
            'name' => $fileName,
            'type' => $mime_tipe,
            'size' => $fileSize,
            'tmp_name' => $image,
        ];

    }

}
