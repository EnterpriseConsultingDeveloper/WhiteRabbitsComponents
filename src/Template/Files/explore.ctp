<?= $this->Html->css('S3FileManager.fileinput.min.css') ?>
<?= $this->Html->css('S3FileManager.jstree/dist/themes/default/style.min.css') ?>
<?= $this->Html->css('S3FileManager.style.css') ?>

<?= $this->fetch('css') ?>

<div class="row">
    <div class="col-lg-2">
        <strong>Document and media manager</strong>

        <div id="folderListContainer"></div>

    </div>
    <div class="col-lg-7">
        <!-- Nav tabs -->
        <ul class="nav nav-tabs" role="tablist" id="exploreTab">
            <li role="presentation"><a href="#uploadTab" aria-controls="uploadTab" role="tab" data-toggle="tab">Upload new document/media</a></li>
            <li role="presentation" class="active"><a href="#fileListTab" aria-controls="fileListTab" role="tab" data-toggle="tab">Document/media list</a></li>
        </ul>

        <!-- Tab panes -->
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane fade" id="uploadTab">
                <div class="kv-main">
                    <div class="page-header">
                        <h1>Document and media manager</h1>
                    </div>
                    <div>
                        <h3>Uploading files to <span id="actual-folder-name"><?=$actualFolderName ?></span></h3>
                    </div>
                    <?= $this->Form->create($file, ['type' => 'file']) ?>
                    <?= $this->Form->hidden('folder_id', ['id' => 'folder-id', 'value' => $actualFolder]); ?>
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
                    <input id="preview" name="preview[]" type="file" multiple class="file-loading">
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <label class="control-label">Selected file info</label>
        <div style="margin-top: 15px;border-top: 1px solid #ddd; padding-top: 24px;">
            <div id="info-div" style="">None selected...</div>
        </div>
    </div>
</div>


<?= $this->Html->script([
'S3FileManager.jquery-2.0.0.min.js',
'S3FileManager.plugins/canvas-to-blob.min.js',
'S3FileManager.fileinput.min.js',
'S3FileManager.bootstrap.min.js',
'S3FileManager.fileinput_locale_it.js',
'S3FileManager.jstree/dist/jstree.min.js'
], ['block' => 'script']); ?>



<?= $this->fetch('script') ?>

<script>

    var choosenFolder;

    var ip1 = <?=json_encode($initialPreview) ?>;
    var ip2 = <?=json_encode($initialPreviewConfig) ?>;

    var fileInputConfig = {
        initialPreview: ip1,
        initialPreviewConfig: ip2,
        overwriteInitial: true,
        initialCaption: "Files in your folder",
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
            image:  '<div class="file-preview-frame" id="{previewId}" data-fileindex="{fileindex}">\n' +
            '   <div class="aaa"><img src="{data}" class="file-preview-image" title="{caption}" alt="{caption}"></div>\n' +
            '   {footer}\n' +
            '</div>\n',
        },
        otherActionButtons: '<button type="button" ' +
        'class="kv-file-edit btn btn-xs btn-default" ' +
        'title="Change status" {dataKey}>\n' + // the {dataKey} tag will be auto replaced
        '<i class="glyphicon glyphicon-edit"></i>\n' +
        '</button>\n' +
        '<button type="button" ' +
        'class="kv-file-select btn btn-xs btn-default" ' +
        'title="Select this file" {dataKey}>\n' + // the {dataKey} tag will be auto replaced
        '<i class="glyphicon glyphicon-check"></i>\n' +
        '</button>\n'
    }



    $.noConflict();
    jQuery(document).ready(function () {

        /**
         * jstree custom context menu
         * Set the context menu with Create, Rename and Remove folder voices
         * Those voices are available only for non root node. Root node have only Create
         *
         */
        function context_menu(node){
            var tree = jQuery('#folderListContainer').jstree(true);

            // The default set of all items
            var items = {
                "Create": {
                    "separator_before": false,
                    "separator_after": false,
                    "label": "Create",
                    "action": function (obj) {
                        var jQuerynode = tree.create_node(node);
                        tree.edit(jQuerynode);
                    }
                },
                "Rename": {
                    "separator_before": false,
                    "separator_after": false,
                    "label": "Rename",
                    "action": function (obj) {
                        tree.edit(node);
                    }
                },
                "Remove": {
                    "separator_before": true,
                    "separator_after": false,
                    "label": "Remove",
                    "action": function (obj) {
                        if(confirm('Are you sure to remove this category?')){
                            tree.delete_node(node);
                        }
                    }
                }
            };

            if (tree.get_parent(node) == '#') { // No delete for root
                delete items.Remove;
                delete items.Rename;
            }

            return items;
        }


        /**
         * Create the jstree for folder tree
         *
         */
        jQuery('#folderListContainer').jstree({
                'core' : {
                    'data' : {
                        'url' : '<?= $this->Url->build(["controller" => "Folders", "action" => "folderList", "?" => ["site" => $this->request->session()->read("Auth.User.customer_site")], "_ext" => "json"]); ?>',
                        'type' : 'POST'
                    },
                    'check_callback' : true, // enable all modifications
                },
                'plugins' : ["contextmenu"], contextmenu: {items: context_menu}
            })
            .on("changed.jstree", function (e, data) {
                choosenFolder = data.selected[0];

                var url = '<?= $this->Url->build(["controller" => "Files", "action" => "getActualFolderFiles"]); ?>/' + choosenFolder;
                jQuery.get(url, function(response){
                    var data = jQuery.parseJSON(response);

                    jQuery('#folder-id').val(choosenFolder);
                    jQuery('#actual-folder-name').text(jQuery('.jstree-clicked').text());

                    fileInputConfig.initialPreview = data.initialPreview;
                    fileInputConfig.initialPreviewConfig = data.initialPreviewConfig;
                    console.log(fileInputConfig);
                    jQuery("#preview").fileinput('destroy');
                    jQuery("#preview").fileinput('refresh', fileInputConfig);
                });

            })
            .on("create_node.jstree", function (e, data) {
                var node = data.node;
                jQuery.ajax({ url: '<?= $this->Url->build(["controller" => "Folders", "action" => "addFolder", "_ext" => "json"]); ?>',
                    data: {
                        pId: node.parent,
                        text: node.text
                    },
                    type: 'post',
                    success: function(output) {
                        console.log('output: ', output.id);
                        node.id = output.id;
                    },
                    error: function(jqXHR, error, errorThrown) {
                        console.log('jqXHR: ', jqXHR);
                    }
                });
            })
            .on("rename_node.jstree", function (e, data) {
                jQuery.ajax({ url: '<?= $this->Url->build(["controller" => "Folders", "action" => "rename", "_ext" => "json"]); ?>',
                    data: {
                        id: data.node.id,
                        text: data.text
                    },
                    type: 'post',
                    success: function(output) {
                        console.log('output: ', output);
                    },
                    error: function(jqXHR, error, errorThrown) {
                        console.log('jqXHR: ', jqXHR);
                    }
                });
            })
            .on("delete_node.jstree", function (e, data) {
                jQuery.ajax({ url: '<?= $this->Url->build(["controller" => "Folders", "action" => "deleteFolder", "_ext" => "json"]); ?>',
                    data: {
                        id: data.node.id,
                    },
                    type: 'post',
                    success: function(output) {
                        console.log('output: ', output);
                    },
                    error: function(jqXHR, error, errorThrown) {
                        console.log('jqXHR: ', jqXHR);
                    }
                });
            });


        /**
         * Create the tabs for upload / list files
         */
        jQuery('#exploreTab a').click(function (e) {
            e.preventDefault();
            jQuery(this).tab('show');
        })


        /**
         * Create the bootstrap fileinput for files upload
         */
        jQuery("#myFile").fileinput({
            uploadAsync: true,
            allowedFileTypes: ['image', 'text', 'video', 'object'],
            uploadUrl: '<?= $this->Url->build(["controller" => "Files", "action" => "uploadFile", "_ext" => "json"]); ?>',
            uploadExtraData: function() {
                var id = jQuery('#folder-id').val();
                var info = {'img_folder': id};
                return info;
            }
        })
        .on("filebatchselected", function(event, files) {
            jQuery("#myFile").fileinput("upload");
        })
        .on('filebatchuploadcomplete', function (event, files, extra) {
            jQuery.get('<?= $this->Url->build(["controller" => "Files", "action" => "getActualFolderFiles"]); ?>/' + choosenFolder, function(response){
                var data = jQuery.parseJSON(response);
                fileInputConfig.initialPreview = data.initialPreview;
                fileInputConfig.initialPreviewConfig = data.initialPreviewConfig;
                jQuery("#preview").fileinput('refresh', fileInputConfig);

                jQuery('#exploreTab li:eq(1) a').tab('show') // Select third tab (0-indexed)
            });

        });


        /**
         * Create the bootstrap fileinput for file list
         */
        jQuery("#preview").fileinput(fileInputConfig);


        /**
         * Function for the public / private button
         */
        jQuery(function() {
            jQuery('.kv-file-edit').on('click', function() {
                var key = jQuery(this).data('key'); // get the file key
                jQuery.ajax({ url: '<?= $this->Url->build(["controller" => "Files", "action" => "changeStatus", "_ext" => "json"]); ?>',
                    data: {
                        id: key,
                    },
                    type: 'post',
                    success: function(output) {
                        console.log('output: ', output);
                        jQuery("button.kv-file-edit[ data-key='" + key + "']")
                                .removeClass(
                                    function (index, css) {
                                        return (css.match (/(^|\s)access-\S+/g) || []).join(' ');
                                    })
                                .addClass('access-' + output)
                                .blur();
                    },
                    error: function(jqXHR, error, errorThrown) {
                        console.log('jqXHR: ', jqXHR);
                    }
                });
            });
        });


        /**
         * Function for the select file button
         */
        jQuery(function() {
            jQuery('.kv-file-select').on('click', function() {
                var key = jQuery(this).data('key'); // get the file key
                var url = '<?= $this->Url->build(["controller" => "Files", "action" => "mediaInfo", "_ext" => "json"]); ?>';
                jQuery.ajax({ url: url,
                    data: {
                        id: key,
                    },
                    type: 'post',
                    success: function(data) {
                        console.log(data);
                        jQuery('#myInsertButton').attr('file-path', data.path);
                        var htmlInfo = '<ul class="info-list">';
                        htmlInfo += '<li><strong>Name</strong>: ' + data.name + '</li>';
                        htmlInfo += '<li><strong>Path</strong>: ' + data.path + '</li>';
                        htmlInfo += '<li><strong>Public</strong>: ' + data.public + '</li>';
                        htmlInfo += '<li><strong>Type</strong>: ' + data.type + '</li>';
                        htmlInfo += '<li><strong>Size</strong>: ' + data.size + 'kB </li>';
                        htmlInfo += '<li><strong>Extension</strong>: ' + data.ext + '</li>';
                        htmlInfo += '</ul>';

                        jQuery('#info-div').html(htmlInfo);
                    },
                    error: function(jqXHR, error, errorThrown) {
                        console.log('jqXHR: ', jqXHR);
                    }
                });

            });
        });
    });
</script>