<?php

namespace Proaction\System\Resource\Upload;

use Proaction\System\Resource\Helpers\Arr;

class FileArray
{

    public static function normalize($fileObj)
    {
        return (new static)->_normalizeFileArray($fileObj);
    }

    private function _normalizeFileArray($fileObj)
    {
        $c = [];

        Arr::pre($fileObj);
        die();
        foreach ($fileObj as $key => $file) {

            if (!is_array($file["name"])) {
                $c[$key][] = $file;
                continue;
            }

            foreach ($file["name"] as $k => $name) {
                $c[$key][$k] = [
                    'name' => $name,
                    'type' => $file["type"][$k],
                    'tmp_name' => $file["tmp_name"][$k],
                    'error' => $file["error"][$k],
                    'size' => $file["size"][$k],
                ];
            }
        }

        return $this->_combine($c);
    }

    private function _combine($fileObj)
    {
        $c = [];
        foreach ($fileObj as $index => $file) {
            foreach ($file as $key => $fileProps) {
                $ui = base64_encode(time() * rand(1, 1000));
                $c[$ui] = $fileProps;
            }
        }
        return $c;
    }
}
