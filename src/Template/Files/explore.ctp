<?= $this->Html->css('S3FileManager.fileinput.min.css') ?>

<?= $this->fetch('css') ?>

<div>

    <!-- Nav tabs -->
    <ul class="nav nav-tabs" role="tablist" id="exploreTab">
        <li role="presentation"><a href="#uploadTab" aria-controls="uploadTab" role="tab" data-toggle="tab">Upload</a></li>
        <li role="presentation" class="active"><a href="#fileListTab" aria-controls="fileListTab" role="tab" data-toggle="tab">File list</a></li>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane fade" id="uploadTab">
            <div class="kv-main">
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
        <div role="tabpanel" class="tab-pane fade in active" id="fileListTab">
            <div class="large-8 medium-8 columns content">
                <label class="control-label">Preview</label>
                <input id="preview" name="preview[]" type="file" multiple class="file-loading">
            </div>
            <div class="large-4 medium-4 columns content">
                <label class="control-label">Info</label>
                <div id="info-div" style="word-wrap:break-word;"></div>
            </div>
        </div>
    </div>
</div>


<?= $this->Html->script([
//'S3FileManager.jquery-2.0.0.min.js',
'S3FileManager.plugins/canvas-to-blob.min.js',
'S3FileManager.fileinput.min.js',
//'S3FileManager.bootstrap.min.js',
'S3FileManager.fileinput_locale_it.js'
], ['block' => 'script']); ?>


<?= $this->fetch('script') ?>

<script>

    var initialPreviewFiles = [
    <?php foreach ($files as $file): ?>
    '<?= $this->S3File->filePreview($file->file, [ 'filename' => $file->file, 'title' => $file->name, 'description' => $file->name, 'originalFilename' => $file->originalFilename ]); ?>',
    <?php endforeach; ?>
    ];


    var initialPreviewConfigs = [
    <?php foreach ($files as $file): ?>
    {caption: "<?= $file->file ?>", width: "120px", url: "<?= $this->Url->build(["controller" => "Files", "action" => "deleteFile", "_ext" => "json"]); ?>", key: <?= $file->id ?>},
    <?php endforeach; ?>
    ];


    function insertFile(filePath) {
        $('#myInsertButton').attr('file-path', filePath);
        $('#info-div').html(filePath);
    }

    $(document).ready(function () {
        $('#exploreTab a').click(function (e) {
            e.preventDefault();
            $(this).tab('show');
        })

        $("#myFile").fileinput({
            uploadAsync: true,
            allowedFileTypes: ['image', 'text', 'video', 'object'],
            uploadUrl: '<?= $this->Url->build(["controller" => "Files", "action" => "uploadFile", "_ext" => "json"]); ?>',
            uploadExtraData: {
                img_folder: $('#folder-id option:selected').val()
            }
        })
                .on("filebatchselected", function(event, files) {
                    // trigger upload method immediately after files are selected
                    $("#myFile").fileinput("upload");
                })
                .on('filebatchuploadcomplete', function (event, files, extra) {
                    $.get("<?= $this->Url->build(["controller" => "Files", "action" => "explore", $this->request->session()->read("Auth.User.customer_site"), 'exploreCallback']); ?>", function(data){
                        $('.modal').find('.modal-body').html(data);
                    });
                });

        $("#preview").fileinput({
            initialPreview: initialPreviewFiles,
            initialPreviewConfig: initialPreviewConfigs,
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
            browseClass: "hidden",
            showCaption: false,
            showRemove: false,
            showUpload: false,
            showCancel: false,
            showClose: false,
            disabled: true,
            previewTemplate: {
                image: '<div class="file-preview-frame" id="{previewId}" data-fileindex="{fileindex}">\n' +
                '   <div class="aaa"><img src="{data}" class="file-preview-image" title="{caption}" alt="{caption}"></div>\n' +
                '   {footer}\n' +
                '</div>\n',
            }
            //layoutTemplates: {main1: '{preview}'}
        });
    });
</script>