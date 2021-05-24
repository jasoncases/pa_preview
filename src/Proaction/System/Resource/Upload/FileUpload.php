<?php


namespace Proaction\System\Resource\Upload;

use getID3;
use Illuminate\Http\UploadedFile;
use Proaction\System\Model\MetaGlobal;
use Proaction\System\Resource\Helpers\Arr;
use Proaction\System\Resource\Logger\Log;
use Proaction\System\Resource\Token\Token;

class FileUpload
{

    private $maxSize;
    private $retObj = [];
    private $allowedTypes;
    private $allowedExtensions;


    /**
     * [test:Symfony\Component\HttpFoundation\File\UploadedFile:private] =>
     *     [originalName:Symfony\Component\HttpFoundation\File\UploadedFile:private] => Screen Shot 2021-04-14 at 8.40.53 AM.png
     *     [mimeType:Symfony\Component\HttpFoundation\File\UploadedFile:private] => image/png
     *     [error:Symfony\Component\HttpFoundation\File\UploadedFile:private] => 0
     *     [hashName:protected] =>
     *     [pathName:SplFileInfo:private] => /tmp/phpq58Pva
     *     [fileName:SplFileInfo:private] => phpq58Pva
     */


    public static function process($path, UploadedFile $file)
    {
        return (new static)->_process($path, $file);
    }

    private function _registerMaxUploadSize()
    {
        $max = ini_get("upload_max_filesize");
        return  preg_replace('/[a-zA-Z\.]/', '', $max);
    }

    private function _process($path, $file)
    {
        $this->allowedTypes = $this->_registerFileTypes();
        $this->allowedExtensions = $this->_registerFileExtensions();
        $this->maxSize = $this->_registerMaxUploadSize();

        $path = $this->_validatePath($path);
        $file = $this->_validateFile($file);
        return $this->_upload($path, $file);
    }

    private function _registerFileTypes()
    {
        return MetaGlobal::getBatch('allowed_upload_file_type');
    }

    private function _registerFileExtensions()
    {
        $ext = MetaGlobal::get('allowed_extensions');
        return array_filter(explode(',', $ext));
    }

    private function _upload($path, UploadedFile $file)
    {
        $ext = end(explode('.', $file->originalName));
        $newFileName = Token::create() . ".$ext";
        $newFilePath = $path . '/' . $newFileName;

        $this->_buildDataObject('resolution', $this->_getFileWidth($file));
        $this->_buildDataObject('alt', $file->originalName);
        $this->_buildDataObject('type', $file->mimeType);
        // $this->_buildDataObject('size', $this->_fmtSize($file));
        $this->_buildDataObject('newFileName', $newFileName);

        if (!move_uploaded_file($file->fileName, $newFilePath)) {
            throw new \Exception\FileUploadException('Error `move_uploaded_file`');
        }
        Log::info('New file upload: ' . $newFilePath);
        return $this->retObj;
    }

    private function _buildDataObject($key, $value)
    {
        $this->retObj[$key] = $value;
    }

    private function _fmtSize($file)
    {
        return number_format($file / 1024 / 1024, 2, '.', '');
    }

    private function _getFileWidth(UploadedFile $file)
    {
        require_once('./vendor/getID3/getid3/getid3.php');
        $getID3 = new getID3();
        $gid3File = $getID3->analyze($file->fileName);

        if ($gid3File['fileformat'] != 'pdf') {
            $mediasize = $gid3File['video'];
            return [
                'x' => $mediasize['resolution_x'],
                'y' => $mediasize['resolution_y'],
            ];
        }
        return [];
    }

    private function _validatePath($path)
    {
        if (!is_dir($path)) {
            if (!mkdir(rtrim($path, "/"), 0755)) {
                throw new \Exception\FileUploadException('Error creating path');
            }
        }
        return $path;
    }

    private function _validateFile(UploadedFile $file)
    {
        // if ($file["size"] / 1024 / 1024 > $this->maxSize) {
        //     throw new \Exception\FileUploadException('File too large');
        // }
        if (!in_array($file->mimeType, $this->allowedTypes)) {
            throw new \Exception\FileUploadException('Incorrect file type');
        }
        $ext = end(explode('.', $file->originalName));
        if (!in_array(strtolower($ext), $this->allowedExtensions)) {
            throw new \Exception\FileUploadException('Incorrect file extension');
        }
        return $file;
    }
}
