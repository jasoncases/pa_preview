<?php

namespace Proaction\System\Controller;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Proaction\Domain\Clients\Resource\ProactionClient;
use Proaction\System\Resource\Helpers\Arr;
use Proaction\System\Resource\Logger\Log;
use Proaction\System\Resource\Status\Status;
use Proaction\System\Resource\Token\Token;
use Proaction\System\Resource\Upload\FileUpload;

class FileUploadController extends BaseProactionController
{

    protected $logAccess = true;
    private $linkroot = 'https://user-uploads.zerodock.com/';


    public function __invoke(Request $req)
    {

        try {
            $filePath = $this->_generateClientDropzone($req->input('uploadType'));

            $file = $req->file("file");

            $newName = Token::create() . '.' . $file->extension();
            $name = $file->storePubliclyAs($filePath, $newName);

            $fileData = [];
            $fileData['newpath'] = $this->linkroot . $name;
            $fileData['alt'] = $file->getClientOriginalName();
            $fileData['newFileName'] = $newName;
            $fileData['type'] = $file->getClientMimeType();

            (new Status())->aux($fileData)->echo();
        } catch (\Exception $e) {

            Log::error('Upload file error: ' . $e->getMessage());
            (new Status())->aux(['message' => $e->getMessage()])->error();
        }
    }

    private function _generateClientDropzone($type)
    {
        return $type . '/' . substr(md5(ProactionClient::prefix()), 0, 10);
    }
}
