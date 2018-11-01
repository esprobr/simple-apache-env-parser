<?php
namespace Espro\SimpleApacheEnvParser;

class Environment
{
    protected $file;
    protected $locked;
    protected $variables = [];
    protected $unprocessed = [];
    protected $includes = [];

    public function __construct($_file)
    {
        $this->file = $_file;
        $this->locked = false;
    }

    public function lock()
    {
        $this->locked = true;
    }

    public function addVariable($_key, $_value, $_filePath)
    {
        if(!$this->locked) {
            $this->variables[$_filePath][$_key] = $_value;
        }

        return $this;
    }

    public function addUnprocessed($_value, $_filePath)
    {
        if(!$this->locked) {
            $this->unprocessed[$_filePath][] = $_value;
        }

        return $this;
    }

    public function addInclude($_value)
    {
        if(!$this->locked) {
            $this->includes[] = $_value;
        }

        return $this;
    }

    public function getVariables()
    {
        return $this->variables;
    }

    public function getIncludes()
    {
        return $this->includes;
    }

    public function getUnprocessed()
    {
        return $this->unprocessed;
    }

    public function getFile()
    {
        return $this->file;
    }
}