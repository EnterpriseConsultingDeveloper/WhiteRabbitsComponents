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

}