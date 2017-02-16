<?php

namespace ByJG\AnyDataset\Dataset;

use ByJG\AnyDataset\Exception\IteratorException;
use InvalidArgumentException;

class JsonIterator extends GenericIterator
{

    /**
     * @var object
     */
    private $jsonObject;

    /**
     * Enter description here...
     *
     * @var int
     */
    private $current = 0;

    public function __construct($jsonObject, $path = "", $throwErr = false)
    {
        if (!is_array($jsonObject)) {
            throw new InvalidArgumentException("Invalid JSON object");
        }

        if ($path != "") {
            if ($path[0] == "/") {
                $path = substr($path, 1);
            }

            $pathAr = explode("/", $path);

            $newjsonObject = $jsonObject;

            foreach ($pathAr as $key) {
                if (array_key_exists($key, $newjsonObject)) {
                    $newjsonObject = $newjsonObject[$key];
                } elseif ($throwErr) {
                    throw new IteratorException("Invalid path '$path' in JSON Object");
                } else {
                    $newjsonObject = array();
                    break;
                }
            }
            $this->jsonObject = $newjsonObject;
        } else {
            $this->jsonObject = $jsonObject;
        }

        $this->current = 0;
    }

    public function count()
    {
        return (count($this->jsonObject));
    }

    /**
     * @access public
     * @return bool
     */
    public function hasNext()
    {
        if ($this->current < $this->count()) {
            return true;
        }

        return false;
    }

    /**
     * @access public
     * @return SingleRow
     * @throws IteratorException
     */
    public function moveNext()
    {
        if (!$this->hasNext()) {
            throw new IteratorException("No more records. Did you used hasNext() before moveNext()?");
        }

        $sr = new SingleRow($this->jsonObject[$this->current++]);

        return $sr;
    }

    public function key()
    {
        return $this->current;
    }
}
