<?php
namespace S3FileManager\Controller;

use Cake\Core\Exception\Exception;
use Cake\Filesystem\File;
use Cake\Network\Exception\NotFoundException;
use Cake\Network\Http\Client;
use Cake\Routing\Router;
use S3FileManager\Controller\AppController;
use Aws\S3\S3Client;
use Aws\Credentials\Credentials;
use S3FileManager\Utils\WRS3Client;
use S3FileManager\Utils\WRUtils;

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
    public function explore($site, $actualFolder = null)
    {
        $this->viewBuilder()->layout('ajax'); // Vista per ajax
        $this->request->session()->write('Auth.User.customer_site', $site);

        $file = $this->Files->newEntity();

        $folderList = $this->Files->Folders->find('treeList', [
            'conditions' => ['bucket' => $site]]);

        $rootFolder = $this->Files->Folders->getRootFolder($site);

        if($rootFolder == null) {
            $this->Flash->error(__('Please first add a Folder, then upload images!'));
        } else {
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
                                'initialPreview', 'initialPreviewConfig'));

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

        $path = $_FILES['file']['name'];
        $file->extension = pathinfo($path, PATHINFO_EXTENSION);
        $file->public = 0;
        $file->original_filename = $path;

        $this->Files->Folders->recover(); // Need to recover folders tree
        $crumbs = $this->Files->Folders->find('path', ['for' => $file->folder_id]);
        $actualFolderPath = '';
        foreach ($crumbs as $crumb) {
            if ($crumb->name == '/')
                $actualFolderPath .= $crumb->name;
            else
                $actualFolderPath .= $crumb->name . '/';
        }

        $file->path = $actualFolderPath;

        if ($this->Files->save($file)) {
            header('Content-Type: application/json');
            echo json_encode('Loaded...');

        } else {
            //$this->Flash->error(__('The file could not be saved. Please, try again.'));
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
     * files/media/21/sadsadasdas/iutry/image.png
     * where image path is /21/sadsadasdas/iutry/image.png
     * 21 = folder id
     * sadsadasdas/iutry/ = complete folder path
     * image.png = image name and extension
     */
    public function media($completePath = null)
    {
        $this->viewBuilder()->layout('ajax'); // Vista per ajax



        if ($completePath == null) {
            throw new NotFoundException('File not found.');
        }

        $lastSlashPos = strrpos($completePath , '/');
        $firstSlashPos = strpos($completePath , '/');
        $fileName = substr($completePath, $lastSlashPos + 1, strlen($completePath));
        $path = substr($completePath, 0, $lastSlashPos + 1);

        $folderId = substr($completePath, 0, $firstSlashPos);

        try {
            $this->loadModel('Files'); // It's necessary because the name "media" was reserved
            $file = $this->Files->find('all')
                ->where(['Files.folder_id' => $folderId])
                ->where(['Files.original_filename' => $fileName])
                ->first();

            if (!$file) {
                throw new NotFoundException('File not found.');
            }

            return $this->sendFile($file->file, $fileName, $file->id);

        } catch (Exception $e) {
            echo 'Error: ', $e->getMessage();
        }

    }



    /**
     * getMediaLink method
     * Return correct link for proxy
     *
     */
    public function mediaInfo()
    {
        // ie return
        // files/media/21/sadsadasdas/iutry/image.png
        $this->viewBuilder()->layout('ajax'); // Vista per ajax
        $fileId = $this->request->data('id');

        $this->loadModel('Files');
        $file = $this->Files->get($fileId);

        $proxyPath = $file->folder_id . $file->path . $file->original_filename;

        $info = [];
        $info['path'] = $proxyPath;
        $info['public'] = $file->public;
        $info['type'] = $file->mime_type;
        $info['size'] = $file->size;
        $info['ext'] = $file->extension;
        $info['name'] = $file->original_filename;

        header('Content-Type: application/json');
        echo json_encode($info);
    }

    private function sendFile($path, $fileName, $fileId)
    {
        $S3Client = new WRS3Client();
        $bucketName = $this->request->session()->read('Auth.User.customer_site');
        $plainUrl = $S3Client->getObjectUrl($bucketName, $path);

        //TODO: inserire un metodo di caching qui

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
                    'title' => $file->name,
                    'description' => $file->name,
                    'originalFilename' => $file->originalFilename,
                    'id' => $file->id
                ]
            );

            $initialPreviewConfig[$i] = ['caption' => "{$file->file}", 'width' => '120px', 'url' => $deleteUrl, 'key' => $key];
            $i++;
        }

        $this->set(compact('file', 'files', 'folders', 'folderList',
            'callbackFunction', 'actualFolder', 'actualFolderName',
            'initialPreview', 'initialPreviewConfig'));

        $this->set('_serialize', ['file', 'files']);
    }

}

