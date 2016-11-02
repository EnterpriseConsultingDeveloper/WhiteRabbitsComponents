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
                    <div class="tab-title">
                        Uploading files to "<span id="actual-folder-name"><?=$actualFolderName ?></span>"
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
//'S3FileManager.jquery-2.0.0.min.js',
'S3FileManager.plugins/canvas-to-blob.min.js',
'S3FileManager.fileinput.min.js',
//'S3FileManager.bootstrap.min.js',
'S3FileManager.fileinput_locale_it.js',
'S3FileManager.jstree/dist/jstree.min.js'
], ['block' => 'script']); ?>



<?= $this->fetch('script') ?>

<script>

    var choosenFolder;

    var ip1 = <?=json_encode($initialPreview) ?>;
    var ip2 = <?=json_encode($initialPreviewConfig) ?>;

    console.log(ip1, ip2);
    var fileInputConfig = {
        initialPreview: ip1,
        initialPreviewConfig: ip2,
        overwriteInitial: true,
        initialCaption: "Files in your folder",
        previewFileIcon: '<i class="fa fa-file"></i>',
        //allowedPreviewTypes: ['image', 'text'], // allow only preview of image & text files
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



    $(document).ready(function () {
        /**
         * jstree custom context menu
         * Set the context menu with Create, Rename and Remove folder voices
         * Those voices are available only for non root node. Root node have only Create
         */
        function context_menu(node){
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
        var myTree = $('#folderListContainer');
        $('#folderListContainer').jstree({
            'core' : {
                'data' : {
                    'url' : '<?= $this->Url->build(["controller" => "Folders", "action" => "folderList", "?" => ["site" => $this->request->session()->read("Auth.User.customer_site")], "_ext" => "json"]); ?>',
                    'type' : 'POST'
                },
                'check_callback' : function (operation, node, node_parent, node_position, more) {
                    // operation can be 'create_node', 'rename_node', 'delete_node', 'move_node' or 'copy_node'
                    // in case of 'rename_node' node_position is filled with the new node name
                    if (operation === 'rename_node') { // Prevent rename on just created nodes
                        var nodeId = node.id + '';
                        console.log('nodeId', nodeId);
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
            'plugins' : ["contextmenu", "dnd"],
            'contextmenu': {
                items: context_menu
            },
            'dnd' : {
                is_draggable: false
            },
        })
                .on("loaded.jstree", function() {
                    myTree.jstree('open_all');
                })
                .on("changed.jstree", function (e, data) {
                    choosenFolder = data.selected[0];

                    if (choosenFolder != null) {
                        var url = '<?= $this->Url->build(["controller" => "Files", "action" => "getActualFolderFiles"]); ?>/' + choosenFolder;
                        $.get(url, function(response){
                            var data = $.parseJSON(response);

                            updateFiles(data);
                            resetInfo();
                        });
                    }

                })
                .on("create_node.jstree", function (e, data) {
                    var node = $.extend(true, {}, data.node);
                    node.type = "Folder";
                    node.name = node.text;
                    node.parentId = data.node.parent;

                    $.ajax({ url: '<?= $this->Url->build(["controller" => "Folders", "action" => "addFolder", "_ext" => "json"]); ?>',
                        data: {
                            pId: data.node.parent,
                            text: data.node.text
                        },
                        type: 'post',
                        success: function(output) {
                            data.instance.set_id(node, output.id);
                            data.instance.edit(output.id);
                        },
                        error: function(jqXHR, error, errorThrown) {
                            console.log('jqXHR: ', jqXHR);
                        }
                    });
                })
                .on("rename_node.jstree", function (e, data) {
                    if (data.node.id != null ) {
                        console.log('rename_node', data.node.id);
                        $.ajax({ url: '<?= $this->Url->build(["controller" => "Folders", "action" => "rename", "_ext" => "json"]); ?>',
                            data: {
                                id: data.node.id,
                                text: data.text
                            },
                            type: 'post',
                            success: function(output) {
                            },
                            error: function(jqXHR, error, errorThrown) {
                                console.log('jqXHR: ', jqXHR);
                            }
                        });
                    }
                })
                .on("delete_node.jstree", function (e, data) {
                    $.ajax({ url: '<?= $this->Url->build(["controller" => "Folders", "action" => "deleteFolder", "_ext" => "json"]); ?>',
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
                })
                .on('copy_node.jstree', function (e, data) {
                    // Prevent folder creation on file move
                    var myTree = $('#folderListContainer').jstree(true);
                    myTree.delete_node(data.node);
                });


        $(document).on('mousedown', ".file-selectable", function (e) {
            return $.vakata.dnd.start(e,
                    {
                        'jstree' : true,
                        'obj' : $(this),
                        'nodes' : [
                            {
                                id : true,
                                text: $(this).text()
                            }
                        ]
                    },
                    '<div id="jstree-dnd" class="jstree-default"><i class="jstree-icon jstree-er"></i>' + $(this).text() + '</div>');
        });

        $(document)
                .on('dnd_move.vakata', function (e, data) {
                    var t = $(data.event.target);
                    if(t.closest('.jstree-node').length) {
                        data.helper.find('.jstree-icon').removeClass('jstree-er').addClass('jstree-ok');
                    } else {
                        data.helper.find('.jstree-icon').removeClass('jstree-ok').addClass('jstree-er');
                    }
                })
                .on('dnd_stop.vakata', function (e, data) {
                    var t = $(data.event.target);
                    if(t.closest('.jstree-node').length) {
                        choosenFolder =  (t.context.id).substring(0, (t.context.id).indexOf('_'));  // remove '_anchor'
                        var fileid = data.element.attributes['my-data-key'].value;

                        $.ajax({ url: '<?= $this->Url->build(["controller" => "Files", "action" => "changeFolder", "_ext" => "json"]); ?>',
                            data: {
                                id: fileid,
                                folder: choosenFolder
                            },
                            type: 'post',
                            success: function(output) {
                                var myTree = $('#folderListContainer').jstree(true);
                                var myNode = myTree.get_node( t.context.id );
                                myTree.deselect_all();
                                myTree.select_node(myNode, false, false);

                                updateFiles(output);
                                resetInfo();
                                myTree.open_node(myNode, false, true);
                            },
                            error: function(jqXHR, error, errorThrown) {
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
            uploadExtraData: function() {
                var id = $('#folder-id').val();
                var info = {'img_folder': id};
                return info;
            }
        })
                .on("filebatchselected", function(event, files) {
                    $("#myFile").fileinput("upload");
                })
                .on('filebatchuploadcomplete', function (event, files, extra) {
                    $.get('<?= $this->Url->build(["controller" => "Files", "action" => "getActualFolderFiles"]); ?>/' + choosenFolder, function(response){
                        var data = $.parseJSON(response);
                        fileInputConfig.initialPreview = data.initialPreview;
                        fileInputConfig.initialPreviewConfig = data.initialPreviewConfig;

                        $el.fileinput('refresh', fileInputConfig);

                        $('#exploreTab li:eq(1) a').tab('show') // Select third tab (0-indexed)
                    });

                });


        /**
         * Create the bootstrap fileinput for file list
         */
        var $el = $('#preview'), initPlugin = function() {
            $el.fileinput(fileInputConfig)
                    .off('filepreupload').on('filepreupload', function() {
                        console.log("Initial pre-upload message!");
                    });
        };
        initPlugin();

        /**
         * Function for the public / private button
         */
        $(document).on('click', ".kv-file-edit", function () {
            var key = $(this).data('key'); // get the file key

            $.ajax({ url: '<?= $this->Url->build(["controller" => "Files", "action" => "changeStatus", "_ext" => "json"]); ?>',
                data: {
                    id: key,
                },
                type: 'post',
                success: function(output) {
                    console.log('output: ', output);
                    $("button.kv-file-edit[ data-key='" + key + "']")
                            .removeClass(
                            function (index, css) {
                                return (css.match (/(^|\s)access-\S+/g) || []).join(' ');
                            })
                            .addClass('access-' + output)
                            .blur();
                    updateInfo(key);
                },
                error: function(jqXHR, error, errorThrown) {
                    console.log('jqXHR: ', jqXHR);
                }
            });
        });


        /**
         * Function for the select file button
         */
        $(document).on('click', ".kv-file-select", function () {
            var key = $(this).data('key'); // get the file key
            updateInfo(key);
        });


        function updateInfo(key) {
            var url = '<?= $this->Url->build(["controller" => "Files", "action" => "mediaInfo", "_ext" => "json"]); ?>';
            $.ajax({ url: url,
                data: {
                    id: key,
                },
                type: 'post',
                success: function(data) {
                    var completeUrl = '<?= $completeUrl ?>/s3_file_manager/Files/media' + data.path;
                    $('#myInsertButton').attr('file-path', completeUrl); //data.path);
                    $('#myInsertButton').attr('file-id', data.id); //data.id);
                    $('#myInsertButton').attr('file-name', data.name); //data.id);
                    $('#myInsertButton').attr('file-path-partial', data.path); //data.path);
                    var htmlInfo = '<ul class="info-list">';
                    htmlInfo += '<li><strong>Name</strong>: ' + data.name + '</li>';
                    htmlInfo += '<li><strong>Download</strong>: <a href="http://' + completeUrl.slice(2) + '" target="_black">' + data.name + '</a></li>';
                    htmlInfo += '<li><strong>Path</strong>: ' + data.path + '</li>';
                    htmlInfo += '<li><strong>Public</strong>: ' + data.public + '</li>';
                    htmlInfo += '<li><strong>Type</strong>: ' + data.type + '</li>';
                    htmlInfo += '<li><strong>Size</strong>: ' + data.size + 'kB </li>';
                    htmlInfo += '<li><strong>Extension</strong>: ' + data.ext + '</li>';
                    htmlInfo += '</ul>';

                    $('#info-div').html(htmlInfo);
                },
                error: function(jqXHR, error, errorThrown) {
                    console.log('jqXHR: ', jqXHR);
                }
            });
        }

        /**
         * Reset info box
         */
        function resetInfo() {
            $('#info-div').html('');
        }

        /**
         * Reset upload box
         */
        function resetUpload() {
            console.log($("#myFile"));
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
            if(data.initialPreview.length == 0) {
                fileInputConfig.initialPreview = ['<span style="color: #000; font-size: 1.8em"><span class="fa-stack fa-lg"> <i class="fa fa-file-o fa-stack-1x"></i> <i class="fa fa-ban fa-stack-2x text-danger"></i></span><br>No file in this folder!</span>'];
            }
            $el.fileinput('destroy');
            $el.fileinput('refresh', fileInputConfig);
        }


    });
</script>