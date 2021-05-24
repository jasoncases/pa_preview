<?php

namespace Proaction\System\Resource\Templater;

use Proaction\System\Resource\Tooltip\TooltipFactory;

class CompoundValueFactory
{
    private $data;
    private $string;

    public static function parse($string, $data)
    {
        return (new static)->_create($string, $data);
    }

    private function _create($string, $data)
    {
        try {

            $varArr = explode(":", $string);
            if (strtolower($varArr[0]) == 'tooltip') {
                return TooltipFactory::create(trim($varArr[1]));
            } else {
                $this->data = $data;
                $this->string = $string;
                return $this->_parse($varArr[0], trim($varArr[1]));
            }
        } catch (\Exception\IllegalValueException $e) {
            return '{' . $this->string . '}';
        }
    }

    private function _parse($arrayName, $propName)
    {

        // if there is a default value, trim it from the propName value
        if ($this->_hasDefaultValue()) {
            $propName = trim(explode('||', $propName)[0]);
        }

        // get the retrn value from the data array
        if (!is_array($this->data)) {
            throw new \Exception\IllegalValueException('array');
        }

        if (!is_array($this->data[$arrayName])) {
            throw new \Exception\IllegalValueException('missing');
        }

        $returnValue = $this->data[$arrayName][$propName];
        // $returnValue = $data[$arrayName];

        // if returnValue comes back null, check for a default value, if not, return the original string
        if (is_null($returnValue)) {
            if ($this->_hasDefaultValue()) {
                return $this->_returnDefaultValue(); // return the stated default
            }
            throw new \Exception\IllegalValueException();
        }
        return $returnValue; // return the found value
    }

    private function _returnDefaultValue()
    {
        return trim(explode('||', $this->string)[1]);
    }

    /**
     *
     * @param string $string
     *
     * @return boolean
     */
    private function _hasDefaultValue()
    {
        return strpos($this->string, '||') != false;
    }
}
