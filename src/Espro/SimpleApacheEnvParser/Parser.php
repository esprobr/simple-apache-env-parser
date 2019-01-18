<?php
namespace Espro\SimpleApacheEnvParser;

use Gobie\Regex\Wrappers\RegexFacade;

/**
 * Class Parser
 * @package Espro\SimpleApacheEnvParser
 */
class Parser
{
    const INCLUDE_PATTERN = '@^include\s+(?P<file>[\w\-_/\.]+)@i';
    const VARIABLE_PATTERN = '/^setenv\s+(?:"|\')?(?P<key>[_a-zA-Z]{1}[\w\d\._]*)(?:"|\')?\s+(?:"|\')?(?P<value>[\s\w\d\.\@\!\#\$\%\¨\&\*\(\)_\-\+\=\[\{\}\]\?\/\:\\\|àáâãåäæèéêëìíîïòóôõöøùúûüç§]*)(?:"|\')?(?:\n|\r)*$/iu';

    protected $regex;
    protected $putEnv;
    /**
     * @var Environment
     */
    protected $environment;

    public function __construct()
    {
        $this->regex = new RegexFacade(RegexFacade::PCRE);
    }

    /**
     * @param string $_filePath
     * @param bool $_putEnv
     * @throws \Gobie\Regex\Wrappers\RegexException
     * @return Environment
     */
    public function parse($_filePath, $_putEnv = true)
    {
        $this->environment = new Environment($_filePath);
        $this->putEnv = $_putEnv;

        self::processFile($_filePath);
        $this->environment->lock();
        return $this->environment;
    }

    /**
     * @param $_filePath
     * @throws \Gobie\Regex\Wrappers\RegexException
     */
    protected function processFile($_filePath)
    {
        if(file_exists($_filePath)) {
            $lines = file($_filePath, FILE_USE_INCLUDE_PATH);

            if(count($lines) > 0) {
                foreach($lines as $line) {
                    $include = $this->regex->get(self::INCLUDE_PATTERN, $line);
                    if(isset($include['file'])) {
                        $dirName = dirname($include['file']);
                        if($dirName.str_replace($dirName, '', $include['file']) != $include['file']) {
                            $include['file'] = dirname($_filePath) . '/' . $include['file'];
                        }

                        $this->environment->addInclude($include['file']);

                        self::processFile($include['file']);
                    } else {
                        $var = $this->regex->get(self::VARIABLE_PATTERN, $line);
                        if(count(array_intersect_key([ 'key', 'value' ], $var)) >= 2) {
                            $key = trim($var['key'], " \t\n\r\0\x0B\"'");
                            $value = trim($var['value'], " \t\n\r\0\x0B\"'");

                            $this->environment->addVariable($key, $value, $_filePath);

                            if($this->putEnv) {
                                putenv("{$key}={$value}");
                            }
                        } else {
                            $this->environment->addUnprocessed(trim($line, " \t\n\r\0\x0B"), $_filePath);
                        }
                    }
                }
            }
        } else {
            throw new Exception("File \"{$_filePath}\"not found!", 0);
        };
    }
}