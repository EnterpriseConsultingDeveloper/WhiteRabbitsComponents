<?php
namespace S3FileManager\Controller;

use Cake\Core\Exception\Exception;
use Cake\Event\Event;
use Cake\Filesystem\File;
use Cake\Network\Exception\NotFoundException;
use Cake\Routing\Router;
use S3FileManager\Utils\WRS3Client;

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
            $this->Auth->allow(['media']);
        }
        if (!($event->subject()->request->params['action'] == 'media')) {
            parent::beforeFilter($event);
        }

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
    public function explore($site, $actualFolder = null)
    {
        $this->viewBuilder()->layout('ajax'); // Vista per ajax
        $this->request->session()->write('Auth.User.customer_site', $site);
        $completeUrl = '//' . $site . '.' . SUITE_DOMAIN_THIRD_LEVELS . $this->request->domain();

        $file = $this->Files->newEntity();

        $folderList = $this->Files->Folders->find('treeList', [
            'conditions' => ['bucket' => $site]]);

        $rootFolder = $this->Files->Folders->getRootFolder($site);

        if($rootFolder == null) { //Creating root Folder for this site
            $folder = $this->Files->Folders->newEntity();
            $folder->name = '/';
            $folder->bucket = $site;
            $this->Files->Folders->save($folder);
            $rootFolder = $folder;
        }

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
            'initialPreview', 'initialPreviewConfig', 'completeUrl'));

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
        $file->folder_id = $_POST['img_folder'];

        $path = $_FILES['file']['name'];
        $file->extension = pathinfo($path, PATHINFO_EXTENSION);
        $file->public = 0;
        $file->original_filename = $path;

        $file->path = $this->getFolderPath($file);

        if ($this->Files->save($file)) {
            header('Content-Type: application/json');
            echo json_encode('Loaded...');

        } else {
            //$this->Flash->error(__('The file could not be saved. Please, try again.'));
        }
    }


    /**
     * uploadFileToResizeFolder function
     */
    public function uploadFileToResizeFolder()
    {
        $this->viewBuilder()->layout('ajax'); // Vista per ajax

        $file = $this->Files->newEntity();
        $file->file = $_FILES['file'];

        $site = $this->extractSite();
        $folderId = $this->getFolderResized($site);

        $this->loadModel('Files'); // It's necessary because the name "media" was reserved
        try {
            $file->folder_id = $folderId;

            $path = $_FILES['file']['name'];
            $file->extension = pathinfo($path, PATHINFO_EXTENSION);
            $file->public = 1;
            $file->original_filename = $path;

            $file->path = $this->getFolderPath($file);

            if ($this->Files->save($file)) {
                header('Content-Type: application/json');
                echo json_encode(__('Saved!'));
            } else {
                header('Content-Type: application/json');
                echo json_encode(__('The file could not be saved. Please, try again.'));
            }

        } catch (Exception $e) {
            echo 'Error: ', $e->getMessage();
        }
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
        $site = $this->request->session()->read('Auth.User.customer_site');

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
        $site = $this->request->session()->read('Auth.User.customer_site');

        $actualFolder = $this->Files->Folders->find('all', [
            'conditions' => ['bucket' => $site],
            'order' => ['id' => 'ASC']
        ])->first();

        $files = $this->Files->findAllByFolderId($actualFolder->id);
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
        $site = $this->request->session()->read('Auth.User.customer_site');

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

        if (isset($_GET['first_read']) && isset($_GET['ref_id'])) {
            $ref_id= $_GET['ref_id'];
            $MtNewsletters= $this->loadModel("MarketingTools.MtNewsletters");
            $MtNewsletters->readImg($ref_id);
            return;
        }

        if ($completePath == null) {
            throw new NotFoundException('File not found.');
        }

        $site = $this->extractSite();

        $lastSlashPos = strrpos($completePath , '/');
        $firstSlashPos = strpos($completePath , '/');
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

            if (!$file) {
                throw new NotFoundException('File not found.');
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
     * @return mixed
     */
    private function getFolderResized($site) {
        $this->loadModel('Folders');
        try {
            $rootFolder = $this->Folders->find('all')
                ->select(['id'])
                ->where(['Folders.bucket' => $site])
                ->where(['Folders.parent_id' => null])
                ->first();

            $rootFolderId = $rootFolder->id;

            $folder = $this->Folders->find('all')
                ->select(['id'])
                ->where(['Folders.bucket' => $site])
                ->where(['Folders.name' => 'Resized'])
                ->where(['Folders.parent_id' => $rootFolderId])
                ->first();

            if($folder != null) {
                return $folder->id;
            } else {
                // Create folder 'Resized'
                $folder = $this->Folders->newEntity();

                $folder->name = 'Resized';
                $folder->parent_id = $rootFolderId;
                $folder->bucket = $site;

                if ($this->Folders->save($folder)) {
                    $this->Folders->recover(); // Need to recover folders tree
                    return $folder->id;
                }
            }

        } catch (Exception $e) {
            echo 'Error: ', $e->getMessage();
        }
        return null;
    }

    /**
     * @return mixed
     */
    private function extractSite() {
        $subdomains = $this->request->subdomains();
        $site = $subdomains[0];
        if ($site == 'content' || $site == 'editorial') {
            $site = $this->request->session()->read('Auth.User.customer_site');
        }
        return $site;
    }


    /**
     * mediaInfo method
     * Return file information and the correct link for proxy
     *
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
    private function getProxyPath($file) {
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

        $tempPath = ROOT .DS. 'tmp' . DS . 'cache' . DS . $fileId . $fileName;

        $file = new File($tempPath, true, 0644);
        $file->write(@file_get_contents($plainUrl));

        $this->response->file($tempPath);

        return $this->response;
    }



    /**
     * @param $files
     * @param $site
     * @param $initialPreview
     * @param $initialPreviewConfig
     *
     * Setup initial file previews
     */
    private function doInitialPreviews($files, $site, &$initialPreview, &$initialPreviewConfig) {
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

    private function getFolderPath ($file) {
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
}

