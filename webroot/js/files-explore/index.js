$(function () {

    var loadingHtml = null;

    $('#modalExplore').on('hidden.bs.modal', function (e) {
        $("#modalExplore .modal-content").html(loadingHtml);
    });



    function callModal(id, initialPreviewFiles, initialPreviewConfigs) {
        $.get("/s3_file_manager/files/explore/" + id, function (data) {
            loadingHtml = $("#modalExplore .modal-content").html();
            $("#modalExplore .modal-content").html(data);


            $('#myTabs a').click(function (e) {
                e.preventDefault()
                $(this).tab('show')
            })

            $("#myFile").fileinput({
                uploadAsync: true,
                allowedFileTypes: ['image', 'text', 'video', 'object'],
                uploadUrl: '<?= $this->Url->build(["controller" => "Files", "action" => "uploadFile", "_ext" => "json"]); ?>',
            }).on('filebatchuploadcomplete', function (event, files, extra) {
                console.log('File batch upload complete');
                //location.reload();
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
                showUpload: false,
                showCancel: false,
                showClose: false,
                showRemove: false,
                disabled: true,
                previewTemplate: {
                    image: '<div class="file-preview-frame" id="{previewId}" data-fileindex="{fileindex}">\n' +
                    '   <div class="aaa"><img src="{data}" class="file-preview-image" title="{caption}" alt="{caption}"></div>\n' +
                    '   {footer}\n' +
                    '</div>\n',
                }
            });
        });
        $("#modalExplore").modal();
    }
});






