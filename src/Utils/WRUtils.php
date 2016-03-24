<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 15/03/2016
 * Time: 11:26
 */

namespace S3FileManager\Utils;


class WRUtils
{

    public static function guessKindOfFile($filename)
    {
        $mime_types = array(
            // text
            'text/plain' => 'text',
            'text/html' => 'text',
            'text/css' => 'text',
            'application/pdf' => 'text',

            // image
            'image/png' => 'image',
            'image/jpeg' => 'image',
            'image/gif' => 'image',
            'image/bmp' => 'image',
            'image/vnd.microsoft.icon' => 'image',
            'image/tiff' => 'image',
            'image/svg+xml' => 'image',

            // other
            'application/zip' => 'other',
            'application/x-rar-compressed' => 'other',
            'application/x-msdownload' => 'other',
            'application/vnd.ms-cab-compressed' => 'other',
            'application/javascript' => 'other',
            'application/json' => 'other',
            'application/xml' => 'other',
            'application/x-shockwave-flash' => 'other',
            'video/x-flv' => 'other',
            'audio/mpeg' => 'other',
            'video/quicktime' => 'other',
            'image/vnd.adobe.photoshop' => 'other',
            'application/postscript' => 'other',
            'application/msword' => 'other',
            'application/rtf' => 'other',
            'application/vnd.ms-excel' => 'other',
            'application/vnd.ms-powerpoint' => 'other',
            'application/vnd.oasis.opendocument.text' => 'other',
            'application/vnd.oasis.opendocument.spreadsheet' => 'other',
        );


        $mime = strtolower(self::guessMimeType($filename));
        if (array_key_exists($mime, $mime_types)) {
            return $mime_types[$mime];
        } else {
            return 'other';
        }
    }

    public static function guessMimeType($filename) {

        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $fileArray = explode('.', $filename);
        $ext = strtolower(array_pop($fileArray));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        } else {
            return 'application/octet-stream';
        }
    }




    public static function getPreviewFileIcon($filename) {

        $icons = array(


            'txt' => '<i class="fa fa-file-text text-muted generic-file-icon "></i>',
            'htm' => '<i class="fa fa-file-code-o text-muted generic-file-icon "></i>',
            'html' => '<i class="fa fa-file-code-o text-muted generic-file-icon "></i>',
            'php' => '<i class="fa fa-file-code-o text-muted generic-file-icon "></i>',
            'css' => '<i class="fa fa-file-code-o text-muted generic-file-icon "></i>',
            'js' => '<i class="fa fa-file-code-o text-muted generic-file-icon "></i>',
            'json' => '<i class="fa fa-file-code-o text-muted generic-file-icon "></i>',
            'xml' => '<i class="fa fa-file-code-o text-muted generic-file-icon "></i>',
            'swf' => '<i class="fa fa-file-movie-o text-muted generic-file-icon "></i>',
            'flv' => '<i class="fa fa-file-movie-o text-muted generic-file-icon "></i>',

            // images
            'png' => '<i class="fa fa-file-picture-o text-muted generic-file-icon "></i>',
            'jpe' => '<i class="fa fa-file-picture-o text-muted generic-file-icon "></i>',
            'jpeg' => '<i class="fa fa-file-picture-o text-muted generic-file-icon "></i>',
            'jpg' => '<i class="fa fa-file-picture-o text-muted generic-file-icon "></i>',
            'gif' => '<i class="fa fa-file-picture-o text-muted generic-file-icon "></i>',
            'bmp' => '<i class="fa fa-file-picture-o text-muted generic-file-icon "></i>',
            'ico' => '<i class="fa fa-file-picture-o text-muted generic-file-icon "></i>',
            'tiff' => '<i class="fa fa-file-picture-o text-muted generic-file-icon "></i>',
            'tif' => '<i class="fa fa-file-picture-o text-muted generic-file-icon "></i>',
            'svg' => '<i class="fa fa-file-picture-o text-muted generic-file-icon "></i>',
            'svgz' => '<i class="fa fa-file-picture-o text-muted generic-file-icon "></i>',

            // archives
            'zip' => '<i class="fa fa-file-archive-o text-muted generic-file-icon "></i>',
            'rar' => '<i class="fa fa-file-archive-o text-muted generic-file-icon "></i>',
            'exe' => '<i class="fa fa-file text-muted generic-file-icon "></i>',
            'msi' => '<i class="fa fa-file-archive-o text-muted generic-file-icon "></i>',
            'cab' => '<i class="fa fa-file-archive-o text-muted generic-file-icon "></i>',

            // audio/video
            'mp3' => '<i class="fa fa-file-sound-o text-muted generic-file-icon "></i>',
            'qt' => '<i class="fa fa-file-movie-o text-muted generic-file-icon "></i>',
            'mov' => '<i class="fa fa-file-movie-o text-muted generic-file-icon "></i>',

            // adobe
            'pdf' => '<i class="fa fa-file-pdf-o text-danger generic-file-icon "></i>',
            'psd' => '<i class="fa fa-file-picture-o text-muted generic-file-icon "></i>',
            'ai' => '<i class="fa fa-file-picture-o text-muted generic-file-icon "></i>',
            'eps' => '<i class="fa fa-file-picture-o text-muted generic-file-icon "></i>',
            'ps' => '<i class="fa fa-file-picture-o text-muted generic-file-icon "></i>',

            // ms office
            'doc' => '<i class="fa fa-file-word-o text-primary generic-file-icon "></i>',
            'docx' => '<i class="fa fa-file-word-o text-primary generic-file-icon "></i>',
            'rtf' => '<i class="fa fa-file-text text-muted generic-file-icon "></i>',
            'xls' => '<i class="fa fa-file-excel-o text-success generic-file-icon "></i>',
            'xlsx' => '<i class="fa fa-file-excel-o text-success generic-file-icon "></i>',
            'ppt' => '<i class="fa fa-file-powerpoint-o text-danger generic-file-icon "></i>',
            'pptx' => '<i class="fa fa-file-powerpoint-o text-danger generic-file-icon "></i>',

            // open office
            'odt' => '<i class="fa fa-file-word-o text-primary generic-file-icon "></i>',
            'ods' => '<i class="fa fa-file-excel-o text-success generic-file-icon "></i>',
        );

        $fileArray = explode('.', $filename);
        $ext = strtolower(array_pop($fileArray));
        if (array_key_exists($ext, $icons)) {
            return $icons[$ext];
        } else {
            return '<i class="fa fa-file-text text-muted generic-file-icon "></i>';
        }
    }


}