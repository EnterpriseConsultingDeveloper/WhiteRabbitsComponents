<?= $this->Html->css('S3FileManager.fileinput.min.css', ['fullBase' => true]) ?>
<?= $this->Html->css('S3FileManager.jstree/dist/themes/default/style.min.css', ['fullBase' => true]) ?>
<?= $this->Html->css('S3FileManager.fmstyle.css', ['fullBase' => true]) ?>

<?= $this->fetch('css') ?>

<style>
    .buttons-left {
        display: block;
        width: fit-content;
        margin: 10px 10px;
        float: left;
    }

    .contentTree {
        margin: 15px;
    }

    #ctcText {
        background: rgba(0, 0, 0, 0.22);
        border: none;
        width: 90%;
        padding: 1px 5px;
    }
</style>
<div class="row">
    <div class="col-lg-2">
        <strong>Folders</strong>
        <div id="folder-bar" class="m-t-xs" style="display: none;">
            <a href="#" class="btn btn-xs btn-primary" title="Add folder" id="add-subfolder"><i class="fa fa-plus"
                                                                                                aria-hidden="true"></i>
                Add folder</a>
            <a href="#" class="btn btn-xs btn-danger" title="Delete folder" id="delete-subfolder"><i class="fa fa-minus"
                                                                                                     aria-hidden="true"></i>
                Delete folder</a>
        </div>
        <div id="folderListContainer"></div>

    </div>
    <div class="col-lg-7">
        <!-- Nav tabs -->
        <ul class="nav nav-tabs" role="tablist" id="exploreTab">
            <li role="presentation"><a href="#uploadTab" aria-controls="uploadTab" role="tab" data-toggle="tab">Upload
                    new document/media</a></li>
            <li role="presentation" class="active"><a href="#fileListTab" aria-controls="fileListTab" role="tab"
                                                      data-toggle="tab">Document/media list</a></li>
        </ul>

        <!-- Tab panes -->
        <div class="tab-content tab-fixed">
            <div role="tabpanel" class="tab-pane fade" id="uploadTab">
                <div class="kv-main">
                    <div class="tab-title">
                        Uploading files to "<span id="actual-folder-name"><?= $actualFolderName ?></span>". You can
                        upload files with a <strong>maximum size of 10MB</strong>.
                    </div>
                    <?php if ($this->S3File->linkEnabled('repository_space') == true) : ?>
                        <?=$this->S3File->infoLimit('repository_space'); ?>
                    <?php endif; ?>
                    <?php if ($this->S3File->linkEnabled('repository_space') == false) : ?>
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
                    <?php endif; ?>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane fade in active" id="fileListTab">
                <div id="file-bar" class="m-t-xs" style="display: none;">
                    <!-- <a href="#" class="btn btn-xs btn-primary" title="Move selected files" id="move-files"><i class="fa fa-plus" aria-hidden="true"></i> Move selected files</a> -->
                    <a href="#" class="btn btn-xs btn-danger" title="Delete selected files" id="delete-files"><i
                                class="fa fa-minus" aria-hidden="true"></i> Delete selected files</a>
                </div>
                <div class="large-8 medium-8 columns contentTree">
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
//'S3FileManager.jquery-2.0.0.min.js',
    'S3FileManager.plugins/canvas-to-blob.min.js',
    'S3FileManager.fileinput.min.js',
//'S3FileManager.bootstrap.min.js',
    'S3FileManager.fileinput_locale_it.js',
    'S3FileManager.jstree/dist/jstree.min.js'
], ['block' => 'script', 'fullBase' => true]); ?>

<?= $this->fetch('script') ?>

<script>

    $(document).ready(function () {

            var choosenFolder;
    var ip1 = <?=json_encode($initialPreview) ?>;
    var ip2 = <?=json_encode($initialPreviewConfig) ?>;
    var typeImage = '<?=$typeImage ?>';

    var fileInputConfig = {
        initialPreview: ip1,
        initialPreviewConfig: ip2,
        overwriteInitial: true,
        initialCaption: "Files in your folder",
        previewFileIcon: '<i class="fa fa-file"></i>',
        //La preview e' stata fatta cambiare in icona
        //Tornare indietro. Adesso si vuole la preview al posto dell'icona (06.03.2017)
        //allowedPreviewTypes: ['image', 'text'], // allow only preview of image & text files
        /*previewFileIconSettings: {
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
        },*/
        //allowedFileTypes: ['image', 'text', 'video', 'object'],
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
        },
        otherActionButtons:
        '<button type="button" ' +
        'class="kv-file-select btn btn-xs btn-default" ' +
        'title="Select this file" {dataKey}>\n' + // the {dataKey} tag will be auto replaced
        '<i class="glyphicon glyphicon-unchecked"></i>\n' +
        '</button>\n' +
        '<button type="button" ' +
        'class="kv-file-download btn btn-xs btn-default" ' +
        'title="Download this file" {dataKey}>\n' +
        '<i class="fa fa-download" aria-hidden="true"></i>\n' +
        '</button>\n' +
        '<button type="button" ' +
        'class="kv-file-edit btn btn-xs btn-default" ' +
        'title="Change status" {dataKey}>\n' +
        '<i class="fa fa-unlock" style="color: #999999"></i>\n' +
        '</button>\n'
    }
    
        $(document).off('mousedown', '.file-selectable');
        $(document).off('click', '.kv-file-edit');
        $(document).off('click', '.kv-file-select');
        $(document).off('click', '.kv-file-download');
        $(document).off('click', '#delete-files');


        var selectedFiles = new Array(0);
        var selectedImages = new Array(0);
        var refresh = false;
        var jsonFiles = {};
        $('#myInsertButton').attr('disabled', true);

        /**
         * jstree custom context menu
         * Set the context menu with Create, Rename and Remove folder voices
         * Those voices are available only for non root node. Root node have only Create
         */
        function context_menu(node) {
            var tree = $('#folderListContainer').jstree(true);

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

                        event.preventDefault();

                        swal({
                                title: "Are you sure?",
                                text: "If you delete a folder, you will lose ALL files in it!",
                                type: "warning",
                                showCancelButton: true,
                                confirmButtonColor: "#DD6B55",
                                confirmButtonText: "Yes",
                                cancelButtonText: "No",
                                closeOnConfirm: true,
                                closeOnCancel: true
                            },
                            function (isConfirm) {
                                if (isConfirm) {
                                    tree.delete_node(node);
                                }
                            });


                    }
                }
            };

            if (tree.get_parent(node) == '#') { // No delete for root
                delete items.Remove;
                delete items.Rename;
            }

            return items;
        }

        $("#add-subfolder").on("click", function () {
            $('#folderListContainer').jstree().create_node(choosenFolder, {"text": "New Folder"}, "last", function () {
                console.log("done on " + choosenFolder);
            });
        });

        $("#delete-subfolder").on("click", function () {

            event.preventDefault();

            swal({
                    title: "Are you sure?",
                    text: "If you delete a folder, you will lose ALL files in it!",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Yes",
                    cancelButtonText: "No",
                    closeOnConfirm: true,
                    closeOnCancel: true
                },
                function (isConfirm) {
                    if (isConfirm) {
                        $('#folderListContainer').jstree().delete_node(choosenFolder, function () {
                            console.log("done on " + choosenFolder);
                        });
                    }
                });
        });


        /**
         * Create the jstree for folder tree
         */
        var myTree = $('#folderListContainer');
        $('#folderListContainer').jstree({
            'core': {
                'data': {
                    'url': '<?= $this->Url->build(["controller" => "Folders", "action" => "folderList", "?" => ["site" => $this->request->session()->read("Auth.User.fc_customer_site")], "_ext" => "json"], true); ?>',
                    'type': 'POST'
                },
                'check_callback': function (operation, node, node_parent, node_position, more) {
                    // operation can be 'create_node', 'rename_node', 'delete_node', 'move_node' or 'copy_node'
                    // in case of 'rename_node' node_position is filled with the new node name
                    if (operation === 'rename_node') { // Prevent rename on just created nodes
                        var nodeId = node.id + '';
                        //console.log('nodeId', nodeId);
                        if (nodeId.substr(0, 1) == 'j') {
                            return false;
                        } else {
                            return true;
                        }
                    } else {
                        return true;
                    }
                }
            },
            'plugins': ["contextmenu", "dnd"],
            'contextmenu': {
                items: context_menu
            },
            'dnd': {
                is_draggable: false
            },
        })
            .on("loaded.jstree", function () {
                myTree.jstree('open_all');
                updateStatusPublic();
            })
            .on("changed.jstree", function (e, data) {
                choosenFolder = data.selected[0];

                if (choosenFolder != null) {
                    var url = '<?= $this->Url->build(["controller" => "Files", "action" => "getActualFolderFiles"]); ?>/' + choosenFolder;
                    $.get(url, function (response) {
                        var data = $.parseJSON(response);

                        updateFiles(data);
                        updateStatusPublic(choosenFolder);
                        resetInfo();
                    });

                    // Open bar for rename, delete and add subfolder
                    $('#folder-bar').val = choosenFolder;
                    $('#folder-bar').show();


                }

            })
            .on("create_node.jstree", function (e, data) {
                var node = $.extend(true, {}, data.node);
                node.type = "Folder";
                node.name = node.text;
                node.parentId = data.node.parent;

                $.ajax({
                    url: '<?= $this->Url->build(["controller" => "Folders", "action" => "addFolder", "_ext" => "json"]); ?>',
                    data: {
                        pId: data.node.parent,
                        text: data.node.text
                    },
                    type: 'post',
                    success: function (output) {
                        data.instance.set_id(node, output.id);
                        data.instance.edit(output.id);
                    },
                    error: function (jqXHR, error, errorThrown) {
                        console.log('jqXHR: ', jqXHR);
                    }
                });
            })
            .on("rename_node.jstree", function (e, data) {
                if (data.node.id != null) {
                    console.log('rename_node', data.node.id);
                    $.ajax({
                        url: '<?= $this->Url->build(["controller" => "Folders", "action" => "rename", "_ext" => "json"]); ?>',
                        data: {
                            id: data.node.id,
                            text: data.text
                        },
                        type: 'post',
                        success: function (output) {
                        },
                        error: function (jqXHR, error, errorThrown) {
                            console.log('jqXHR: ', jqXHR);
                        }
                    });
                }
            })
            .on("delete_node.jstree", function (e, data) {
                $.ajax({
                    url: '<?= $this->Url->build(["controller" => "Folders", "action" => "deleteFolder", "_ext" => "json"]); ?>',
                    data: {
                        id: data.node.id,
                    },
                    type: 'post',
                    success: function (output) {
                        console.log('output: ', output);
                    },
                    error: function (jqXHR, error, errorThrown) {
                        console.log('jqXHR: ', jqXHR);
                    }
                });
            })
            .on('copy_node.jstree', function (e, data) {
                // Prevent folder creation on file move
                var myTree = $('#folderListContainer').jstree(true);
                myTree.delete_node(data.node);
            });

        $(document).on('mousedown', ".file-selectable", function (e) {
            return $.vakata.dnd.start(e,
                {
                    'jstree': true,
                    'obj': $(this),
                    'nodes': [
                        {
                            id: true,
                            text: $(this).text()
                        }
                    ]
                },
                '<div id="jstree-dnd" class="jstree-default"><i class="jstree-icon jstree-er"></i>' + $(this).text() + '</div>');
        });

        $(document)
            .on('dnd_move.vakata', function (e, data) {
                var t = $(data.event.target);
                if (t.closest('.jstree-node').length) {
                    data.helper.find('.jstree-icon').removeClass('jstree-er').addClass('jstree-ok');
                } else {
                    data.helper.find('.jstree-icon').removeClass('jstree-ok').addClass('jstree-er');
                }
            })
            .on('dnd_stop.vakata', function (e, data) {
                var t = $(data.event.target);
                if (t.closest('.jstree-node').length) {
                    choosenFolder = (t.context.id).substring(0, (t.context.id).indexOf('_'));  // remove '_anchor'
                    var fileid = data.element.attributes['my-data-key'].value;

                    $.ajax({
                        url: '<?= $this->Url->build(["controller" => "Files", "action" => "changeFolder", "_ext" => "json"]); ?>',
                        data: {
                            id: fileid,
                            folder: choosenFolder
                        },
                        type: 'post',
                        success: function (output) {
                            var myTree = $('#folderListContainer').jstree(true);
                            var myNode = myTree.get_node(t.context.id);
                            myTree.deselect_all();
                            myTree.select_node(myNode, false, false);

                            updateFiles(output);
                            resetInfo();
                            myTree.open_node(myNode, false, true);
                        },
                        error: function (jqXHR, error, errorThrown) {
                            console.log('jqXHR: ', jqXHR);
                        }
                    });
                }
            });


        /**
         * Create the tabs for upload / list files
         */
        $('#exploreTab a').click(function (e) {
            e.preventDefault();
            $(this).tab('show');
            resetInfo();
            resetUpload();
        })


        /**
         * Create the bootstrap fileinput for files upload
         */
        $("#myFile").fileinput({
            overwriteInitial: true,
            uploadAsync: true,
            //allowedFileTypes: ['image', 'text', 'video', 'object'],
            uploadUrl: '<?= $this->Url->build(["controller" => "Files", "action" => "uploadFile", "_ext" => "json"]); ?>',
            uploadExtraData: function () {
                var id = $('#folder-id').val();
                var info = {'img_folder': id};
                return info;
            }
        })
            .on("filebatchselected", function (event, files) {
                $("#myFile").fileinput("upload");
            })
            .on('filebatchuploadcomplete', function (event, files, extra) {

                $.get('<?= $this->Url->build(["controller" => "Files", "action" => "getActualFolderFiles"]); ?>/' + choosenFolder, function (response) {
                    var data = $.parseJSON(response);
                    fileInputConfig.initialPreview = data.initialPreview;
                    fileInputConfig.initialPreviewConfig = data.initialPreviewConfig;

                    $el.fileinput('refresh', fileInputConfig);

                    //Probably a chenge request would not to switch immediately
                    $('#exploreTab li:eq(1) a').tab('show') // Select third tab (0-indexed)
                });
            })
            .on('filebatchuploaderror', function (event, data, msg) {
                var form = data.form, files = data.files, extra = data.extra,
                    response = data.response, reader = data.reader;
                console.log('File batch upload error');
                // get message
                alert(msg);
            });


        /**
         * Create the bootstrap fileinput for file list
         */
        var $el = $('#preview'), initPlugin = function () {
            $el.fileinput(fileInputConfig)
                .off('filepreupload').on('filepreupload', function () {
                // console.log("Initial pre-upload message!");
            })
                .on("filepredelete", function (jqXHR) {
                    var abort = true;
                    if (confirm("Are you sure you want to delete this image?")) {
                        refresh = true;
                        abort = false;
                    } else {
                        refresh = false;
                    }

                    return abort; // you can also send any data/object that you can receive on `filecustomerror` event

                });
        };
        initPlugin();


        /**
         * Function for the public / private button
         */
        //$('.kv-file-edit').on('click', function () {
        $(document).on('click', ".kv-file-edit", function () {
            $(this).removeData("key");
            var key = $(this).data('key'); // get the file key

            $.ajax({
                url: '<?= $this->Url->build(["controller" => "Files", "action" => "changeStatus", "_ext" => "json"]); ?>',
                data: {
                    id: key,
                },
                type: 'post',
                success: function (output) {
                    $("button.kv-file-edit[ data-key='" + key + "']")
                        .removeClass(
                            function (index, css) {
                                return (css.match(/(^|\s)access-\S+/g) || []).join(' ');
                            })
                        .addClass('access-' + output)
                        .blur();

                    /* icon live change*/
                    if (output == true) {
                        $(".kv-file-edit[data-key='" + key + "']").html('<i class="fa fa-unlock" aria-hidden="true" style="color: #e90000"></i>');
                    } else {
                        $(".kv-file-edit[data-key='" + key + "']").html('<i class="fa fa-lock" aria-hidden="true" style="color: #62cb31"></i>');
                    }
                    updateInfo(key);
                },
                error: function (jqXHR, error, errorThrown) {
                    console.log('jqXHR: ', jqXHR);
                }
            });

        });


        $(document).on('dblclick', ".file-selectable", function () {

            console.log($(this));

            var key = $(this).attr('my-data-key');
            var objData = updateInfo(key);
            //console.log("key: ", key);
            //console.log("public: ", objData.public);
            selectedImages.forEach(function (entry) {
                var State = $(".kv-file-select[data-key='" + entry + "']").html() == '<i class="glyphicon glyphicon-check" style="color: #62cb31;"></i>' ? 1 : 0;
                //console.log(State);
                if (State == 1) {
                    $(".file-selectable[my-data-key='" + entry + "']").css('outline', '3px solid #62cb31');
                } else {
                    $(".file-selectable[my-data-key='" + entry + "']").css('outline', '0px');

                }
                selectedImages.splice(entry, 1);
            });
            selectedImages[key] = key;
            $(".file-selectable[my-data-key='" + key + "']").css('outline', '5px solid #00C0FF');

            if (objData.public === 0) {
                delete jsonFiles[key];
                $('#myInsertButton').attr('disabled', true);
                $('#myInsertButton').attr('file-path', ''); //data.path);
                $('#myInsertButton').attr('file-id', ''); //data.id);
                $('#myInsertButton').attr('file-name', ''); //data.id);
                $('#myInsertButton').attr('file-path-partial', ''); //data.path);

            } else {
                jsonFiles[key] = objData;
                $('#myInsertButton').attr('disabled', false);
                $('#myInsertButton').attr('file-path', objData.url); //data.path);
                $('#myInsertButton').attr('file-id', key); //data.id);
                $('#myInsertButton').attr('file-name', objData.name); //data.id);
                $('#myInsertButton').attr('file-path-partial', objData.partialUrl); //data.path);
            }
                
            $('#myInsertButton').attr('file-type-image', typeImage); //data.path);

            // Create the JSON object to pass within an attribute on the button
            $('#myInsertButton').attr('files', JSON.stringify(jsonFiles)); // contains id and path of the selected files

        });


        $(document).on('click', ".kv-file-remove", function () {
            $(this).removeData("key");
            var key = $(this).data('key');
            if (refresh == true) {
                selectedImages.forEach(function (entry) {
                    if (entry == key) {
                        delete selectedFiles[key];
                        resetInfo();
                    }
                });

            }
        });


        /**
         * Function for the select file button
         */
        //$(".kv-file-select").on('click', function () {
        $(document).on('click', ".kv-file-select", function () {
            $(this).removeData("key");
            var key = $(this).data('key'); // get the file key
            var objData = getInfo(key);

            var cssStyles = ["<i class='glyphicon glyphicon-unchecked'></i>", "<i class='glyphicon glyphicon-check' style='color: #62cb31;'></i>"];
            var newState = $(".kv-file-select[data-key='" + key + "']").html() == '<i class="glyphicon glyphicon-check" style="color: #62cb31;"></i>' ? 0 : 1;

            $(".kv-file-select[data-key='" + key + "']").html(cssStyles[newState]);
            $(".file-selectable[my-data-key='" + key + "']").css('outline', '3px solid #62cb31');


            //console.log("newState: ", newState);
            //  console.log("public: ", objData.public);

            if (newState === 0) { // It was already selected, so it's time to remove it!!!
                delete selectedFiles[key];
                delete jsonFiles[key];
                $(".file-selectable[my-data-key='" + key + "']").css('outline', '');
                $('#myInsertButton').attr('file-path', ''); //data.path);
                $('#myInsertButton').attr('file-id', ''); //data.id);
                $('#myInsertButton').attr('file-name', ''); //data.id);
                $('#myInsertButton').attr('file-path-partial', ''); //data.path);


            } else {
                selectedFiles[key] = [objData.name, objData.url];
                jsonFiles[key] = objData;

                $('#myInsertButton').attr('file-path', objData.url); //data.path);
                $('#myInsertButton').attr('file-id', key); //data.id);
                $('#myInsertButton').attr('file-name', objData.name); //data.id);
                $('#myInsertButton').attr('file-path-partial', objData.partialUrl); //data.path);

            }

            $(".file-selectable[my-data-key='" + selectedImages[key] + "']").css('outline', '5px solid #00C0FF');

            var countSelectedFiles = 0;
            for (i in selectedFiles) // in returns key, not object
                if (selectedFiles[i] != undefined)
                    countSelectedFiles++;

            if (countSelectedFiles > 0) {
                $("#file-bar").show();
            } else {
                $("#file-bar").hide();
            }
            /*  if(objData.public === 0){
                  $('#myInsertButton').attr('disabled', true);

              }else{
                  $('#myInsertButton').attr('disabled', false);
              }

               $('#myInsertButton').attr('file-type-image', typeImage); //data.path);

               if(selectedFiles.length > 0) {
                   $("#file-bar").show();
               } else {
                   $("#file-bar").hide();
               }

               // Create the JSON object to pass within an attribute on the button
               $('#myInsertButton').attr('files', JSON.stringify(jsonFiles)); // contains id and path of the selected files*/

            //console.log("selectedFiles: ", selectedFiles);

        });

        /**
         * Function for the download file button
         */
        $(document).on('click', ".kv-file-download", function () {
            $(this).removeData("key");
            var key = $(this).data('key'); // get the file key
            $(".file-selectable[my-data-key='" + key + "']").css('border', '0px solid #3498db');
            var url = '<?= $this->Url->build(["controller" => "Files", "action" => "mediaInfo", "_ext" => "json"]); ?>';
            $.ajax({
                url: url,
                data: {
                    id: key,
                },
                type: 'post',
                async: false,
                success: function (data) {
                    var completeUrl = '<?= $completeUrl ?>/s3_file_manager/Files/media_auth' + data.path;
                    window.open(completeUrl, '_blank');

                },
                error: function (jqXHR, error, errorThrown) {
                    console.log('jqXHR: ', jqXHR);
                }
            });
            return objData;

        });

        /**
         * Delete a single file
         * Edit Mugnano Duplicate?*/

        //function deleteFile(item, index) {
        //    $.ajax({
        //        url: '<?//= $this->Url->build(["controller" => "Files", "action" => "deleteFile", "_ext" => "json"]); ?>//',
        //        data: {
        //            key: index,
        //        },
        //        type: 'post',
        //        async: false,
        //        success: function (output) {
        //            //console.log('output: ', output);
        //            $("#file-bar").hide();
        //        },
        //        error: function (jqXHR, error, errorThrown) {
        //            console.log('jqXHR: ', jqXHR);
        //        }
        //    });
        //    //demoP.innerHTML = demoP.innerHTML + "index[" + index + "]: " + item + "<br>";
        //}

        function getInfo(key) {
            var objData = {};
            var url = '<?= $this->Url->build(["controller" => "Files", "action" => "mediaInfo", "_ext" => "json"]); ?>';
            $.ajax({
                url: url,
                data: {
                    id: key,
                },
                type: 'post',
                async: false,
                success: function (data) {
                    /*   var public = '';
                       var isDisplayed = 'display: none';
                       var notIsDisplayed = 'display: block';
                       if (data.public == '1') {
                           public = '<i class="fa fa-unlock" aria-hidden="true" style="color: #e90000;"></i> Public';
                           isDisplayed = 'display: block';
                           notIsDisplayed = 'display: none';
                       } else {
                           public = '<i class="fa fa-lock" aria-hidden="true" style="color: #62cb31;"></i> Private';
                           isDisplayed = 'display: none';
                           notIsDisplayed = 'display: block';
                       }

                       var htmlInfo = '<ul class="info-list">';
                       htmlInfo += '<li><strong>Name</strong>: ' + data.name + '</li>';
                       htmlInfo += '<li><strong>Path</strong>: ' + data.path + '</li>';
                       htmlInfo += '<li><strong>Status</strong>: ' + public + '</li>';
                       htmlInfo += '<li><strong>Type</strong>: ' + data.type + '</li>';
                       htmlInfo += '<li><strong>Size</strong>: ' + data.size + 'kB </li>';
                       htmlInfo += '<li style="' + notIsDisplayed + '"><a class="btn btn-primary btn-xs buttons-left" style="margin: 10px 0;" href="' + completeUrl + '" target="_blank"><i class="fa fa-download" aria-hidden="true"></i> Download</a></li>';
                       htmlInfo += '<li style="' + isDisplayed + '"><strong>URL</strong>: <input type="text" id="ctcText" readonly="readonly" value="' + completeUrl + '"/> <a class="btn btn-success btn-xs buttons-left" style="margin: 10px 0;" href="#" id="ctcBtn"><i class="fa fa-copy" aria-hidden="true"></i> Copy URL to clipboard</a> <a class="btn btn-primary btn-xs buttons-left" href="' + completeUrl + '" target="_blank"><i class="fa fa-download" aria-hidden="true"></i> Download</a></li>';
                       htmlInfo += '</ul>';

                       $('#info-div').html(htmlInfo);*/

                    var completeUrl = '<?= $completeUrl ?>/s3_file_manager/Files/media_auth' + data.path;
                    objData = {'name': data.name, 'url': completeUrl, 'partialUrl': data.path, 'public': data.public};

                },
                error: function (jqXHR, error, errorThrown) {
                    console.log('jqXHR: ', jqXHR);
                }
            });
            return objData;
        }


        function updateInfo(key) {
            var objData = {};
            var url = '<?= $this->Url->build(["controller" => "Files", "action" => "mediaInfo", "_ext" => "json"]); ?>';
            $.ajax({
                url: url,
                data: {
                    id: key,
                },
                type: 'post',
                async: false,
                success: function (data) {
                    var public = '';
                    var isDisplayed = 'display: none';
                    var notIsDisplayed = 'display: block';
                    if (data.public == '1') {
                        $('#myInsertButton').attr('disabled', false );
                        public = '<i class="fa fa-unlock" aria-hidden="true" style="color: #e90000;"></i> Public';
                        isDisplayed = 'display: block';
                        notIsDisplayed = 'display: none';
                    } else {
                        $('#myInsertButton').attr('disabled', true);
                        public = '<i class="fa fa-lock" aria-hidden="true" style="color: #62cb31;"></i> Private';
                        isDisplayed = 'display: none';
                        notIsDisplayed = 'display: block';
                    }
                    var completeUrl = '<?= $completeUrl ?>/s3_file_manager/Files/media_auth' + data.path;
                    var htmlInfo = '<ul class="info-list">';
                    htmlInfo += '<li><strong>Name</strong>: ' + data.name + '</li>';
                    htmlInfo += '<li><strong>Path</strong>: ' + data.path + '</li>';
                    htmlInfo += '<li><strong>Status</strong>: ' + public + '</li>';
                    htmlInfo += '<li><strong>Type</strong>: ' + data.type + '</li>';
                    htmlInfo += '<li><strong>Size</strong>: ' + data.size + 'kB </li>';
                    htmlInfo += '<li style="' + notIsDisplayed + '"><a class="btn btn-primary buttons-left" style="margin: 10px 0;" href="' + completeUrl + '" target="_blank"><i class="fa fa-download" aria-hidden="true"></i> Download</a></li>';
                    htmlInfo += '<li style="' + isDisplayed + '"><strong>URL</strong>: <input type="text" id="ctcText" readonly="readonly" value="' + completeUrl + '"/> <a class="btn btn-success buttons-left" style="margin: 10px 0;" href="#" id="ctcBtn"><i class="fa fa-copy" aria-hidden="true"></i> Copy URL to clipboard</a> <a class="btn btn-primary buttons-left" href="' + completeUrl + '" target="_blank"><i class="fa fa-download" aria-hidden="true"></i> Download</a></li>';
                    htmlInfo += '</ul>';

                    $('#info-div').html(htmlInfo);


                    objData = {'name': data.name, 'url': completeUrl, 'partialUrl': data.path, 'public': data.public};

                },
                error: function (jqXHR, error, errorThrown) {
                    console.log('jqXHR: ', jqXHR);
                }
            });
            return objData;
        }

        /**
         * function copyToClipboard
         *
         */
        $(document).on('click', "#ctcBtn", function () {
            var toCopy = document.querySelector('#ctcText');
            toCopy.select();

            try {
                var successful = document.execCommand('copy');
                var msg = successful ? 'successful' : 'unsuccessful';
                //console.log('Copying text command was ' + msg);
            } catch (err) {
                console.log('Oops, unable to copy');
            }


        });


        /**
         * Delete a single file
         */
        function deleteFile(item, index) {
            $.ajax({
                url: '<?= $this->Url->build(["controller" => "Files", "action" => "deleteFile", "_ext" => "json"]); ?>',
                data: {
                    key: index,
                },
                type: 'post',
                async: false, // Useful to update the viewer component
                success: function (output) {
                    //console.log('output: ', output);
                    $("#file-bar").hide();
                },
                error: function (jqXHR, error, errorThrown) {
                    console.log('jqXHR: ', jqXHR);
                }
            });
        }

        /**
         * Function for the delete file button
         */
        $(document).on('click', "#delete-files", function () {
            if (!confirm(('Are you sure you want to delete selected files?'))) {
                return;
            }

            selectedFiles.forEach(deleteFile);
            selectedFiles = []; // Empty selected array files
            selectedImages = []; // Empty selected array files
            jsonFiles = {};
            $.get('<?= $this->Url->build(["controller" => "Files", "action" => "getActualFolderFiles"]); ?>/' + choosenFolder, function (response) {
                var data = $.parseJSON(response);
                updateFiles(data);
                resetInfo();
            });
        });

        /**
         * Reset info box
         */
        function resetInfo() {
            $('#info-div').html('');
            $('#myInsertButton').attr('disabled', true);
        }

        /**
         * Reset upload box
         */
        function resetUpload() {
            //console.log($("#myFile"));
            $("#myFile").fileinput('reset');
            $("#myFile").fileinput('refresh');

        }

        /**
         * updateFiles function
         * @param data
         */
        function updateFiles(data) {
            $('#folder-id').val(choosenFolder);
            $('#actual-folder-name').text($('.jstree-clicked').text());

            fileInputConfig.initialPreview = data.initialPreview;
            fileInputConfig.initialPreviewConfig = data.initialPreviewConfig;
            //console.log(data);
            if (data.initialPreview.length == 0) {
                fileInputConfig.initialPreview = ['<span style="color: #000; font-size: 1.8em"><span class="fa-stack fa-lg"> <i class="fa fa-file-o fa-stack-1x"></i> <i class="fa fa-ban fa-stack-2x text-danger"></i></span><br>No file in this folder!</span>'];
            }
            $el.fileinput('destroy');
            $el.fileinput('refresh', fileInputConfig);
            selectedFiles = []; // Empty selected array files
            jsonFiles = {};
        }

        function getFileStatus() {
            return 'lock';
        }

        function updateStatusPublic(choosenFolder) {
            // console.log("go updateStatus") ;
            // console.log(choosenFolder) ;
            $.ajax({
                url: '<?= $this->Url->build(["controller" => "Files", "action" => "getFilesExplore", "_ext" => "json"]); ?>',
                data: {
                    id_folder: choosenFolder
                },
                type: 'post',
                success: function (output) {
                    //   console.log(output.files) ;
                    Object.keys(output.files).forEach(function (key) {
                        var public = (output.files[key].public);
                        var datakey = (output.files[key].id);
                        //   console.log(public) ;
                        //  console.log(datakey) ;
                        if (public == true) {
                            $(".kv-file-edit[data-key='" + datakey + "']").html('<i class="fa fa-unlock" aria-hidden="true" style="color: #e90000"></i>');
                            // console.log("unlock") ;
                        } else {
                            $(".kv-file-edit[data-key='" + datakey + "']").html('<i class="fa fa-lock" aria-hidden="true" style="color: #62cb31"></i>');
                            //console.log("lock") ;
                        }
                    });

                },
                error: function (jqXHR, error, errorThrown) {
                    console.log('jqXHR: ', jqXHR);
                }
            });
        }


    });
</script>