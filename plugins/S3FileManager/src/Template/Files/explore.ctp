<?= $this->Html->css('S3FileManager.bootstrap.min.css') ?>
<?= $this->Html->css('S3FileManager.fileinput.min.css') ?>

<?= $this->fetch('css') ?>



<div>

    <!-- Nav tabs -->
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation"><a href="#uploadTab" aria-controls="uploadTab" role="tab" data-toggle="tab">Upload</a></li>
        <li role="presentation" class="active"><a href="#fileListTab" aria-controls="fileListTab" role="tab" data-toggle="tab">File list</a></li>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane" id="uploadTab">
            <div class="container kv-main">
                <div class="page-header">
                    <h1>File Manager</h1>
                </div>
                <?= $this->Form->create($file, ['type' => 'file']) ?>
                <?= $this->Form->input('folder_id', ['options' => $folders]); ?>
                <div class="form-group">
                    <?= $this->Form->input('file', [
                    'type' => 'file',
                    'multiple' => 'true',

                    'id' => 'myFile'
                    ]) ?>
                </div>
                <div id="errorBlock" class="help-block"></div>
                <?= $this->Form->end() ?>
            </div>
        </div>
        <div role="tabpanel" class="tab-pane active" id="fileListTab">
            <div class="large-8 medium-8 columns content">
                <label class="control-label">Preview</label>
                <input id="preview" name="preview[]" type="file" multiple class="file-loading">
            </div>
            <div class="large-4 medium-4 columns content">
                <label class="control-label">Info</label>
                <div id="info-div" style="word-wrap:break-word;">Ciao</div>
            </div>
        </div>
    </div>

</div>

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Modal title</h4>
            </div>
            <div class="modal-body">
                ...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>
</div>


<?= $this->Html->script([
'S3FileManager.jquery-2.0.0.min.js',
'S3FileManager.plugins/canvas-to-blob.min.js',
'S3FileManager.fileinput.min.js',
'S3FileManager.bootstrap.min.js',
'S3FileManager.fileinput_locale_it.js'
]); ?>


<?= $this->fetch('script') ?>

<script>

    $(document).ready(function() {
        $('#myTabs a').click(function (e) {
            e.preventDefault()
            $(this).tab('show')
        })

        $("#myFile").fileinput({
            //'showPreview' : true,
            //'multiple': true,
            //'data-preview-file-type': 'any',
            //'allowedFileExtensions' : ['jpg', 'png','gif'],
            //'elErrorContainer': '#errorBlock',
            uploadAsync: true,
            allowedFileTypes: ['image', 'text', 'video', 'object'],
            uploadUrl: 'http://localhost/WhiteRabbitComponents/s3-file-manager/files/uploadFile.json'
        }).on('filebatchuploadcomplete', function(event, files, extra) {
            console.log('File batch upload complete');
            //location.reload();
        }).on('fileselect', function(event, numFiles, label) {
            console.log("change", event, numFiles, label);
        });

        var ip = [
        <?php foreach ($files as $file): ?>
        '<?= $this->S3File->filePreview($file->file, [ 'filename' => $file->file, 'title' => $file->name, 'description' => $file->name, 'originalFilename' => $file->originalFilename ]); ?>',
        <?php endforeach; ?>
        ];


        $("#preview").fileinput({
            initialPreview: ip,
            initialPreviewConfig: [
                <?php foreach ($files as $file): ?>
                {caption: "<?= $file->original_filename ?>", width: "120px", url: "http://localhost/WhiteRabbitComponents/s3-file-manager/files/deleteFile.json", key: <?= $file->id ?>},
                <?php endforeach; ?>
            ],
            overwriteInitial: false,
            initialCaption: "Images in your folder",
                previewFileIcon: '<i class="fa fa-file"></i>',
                allowedPreviewTypes: ['image', 'text'], // allow only preview of image & text files
                previewFileIconSettings: {
                    'docx': '<i class="fa fa-file-word-o text-primary"></i>',
                    'doc': '<i class="fa fa-file-word-o text-primary"></i>',
                    'xlsx': '<i class="fa fa-file-excel-o text-success"></i>',
                    'xls': '<i class="fa fa-file-excel-o text-success"></i>',
                    'pptx': '<i class="fa fa-file-powerpoint-o text-danger"></i>',
                    'ppt': '<i class="fa fa-file-powerpoint-o text-danger"></i>',
                    'pdf': '<i class="fa fa-file-pdf-o text-danger"></i>',
                    'zip': '<i class="fa fa-file-archive-o text-muted"></i>',
                    'rar': '<i class="fa fa-file-archive-o text-muted"></i>',
                    '7z': '<i class="fa fa-file-archive-o text-muted"></i>',
                },
            allowedFileTypes: ['image', 'text', 'video', 'object'],
            previewClass: "bg-warning",
                showUpload: false,
                showCancel: false,
                showClose: false,
                showRemove: false,
                disabled: true,
                previewTemplate: {image: '<div class="file-preview-frame" id="{previewId}" data-fileindex="{fileindex}">\n' +
                                    '   <div class="aaa"><img src="{data}" class="file-preview-image" title="{caption}" alt="{caption}"></div>\n' +
                                    '   {footer}\n' +
                                    '</div>\n',}
        //layoutTemplates: {main1: '{preview}'}
        });
    });
</script>