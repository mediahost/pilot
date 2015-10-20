<?php

namespace ServiceModule;

use Nette\Diagnostics\Debugger,
    Kdyby\Curl\Request,
    Kdyby\Curl\CurlException;

/**
 * XML Parser BasePresenter
 *
 * @author Petr Poupě
 */
abstract class XmlParserPresenter extends BasePresenter
{

    protected $tmpDir = "tmp/services/xml-parser";
    protected $saveDays = 14;

    public function startup()
    {
        parent::startup();
        Debugger::timer("xml-parser");
    }

    protected function beforeRender()
    {
        parent::beforeRender();
        $time = Debugger::timer("xml-parser");
        \Nette\Diagnostics\Debugger::barDump($time, "Execution Time");
    }

    /**
     * Download file from url with cURL
     * @param \Nette\Http\Url $url
     * @param type $response
     * @param type $headers
     * @return boolean
     */
    protected function download(\Nette\Http\Url $url, &$response, &$headers = NULL)
    {
        $request = new Request($url);
        try {
            $get = $request->get();
            $headers = $get->getHeaders();
            $response = $get->getResponse();
            return TRUE;
        } catch (CurlException $e) {
            $response = $e->getMessage();
            return FALSE;
        }
    }

    /**
     * Return SimpleXMLElement from string or filename
     * @param type $source
     * @return boolean|\SimpleXMLElement
     */
    protected function parseXml($source)
    {
        if (is_string($source)) {
            $xml = simplexml_load_string($source);
        } else if (is_file($source)) {
            $xml = simplexml_load_file($source);
        } else {
            return FALSE;
        }
        return $xml;
    }

    /**
     * Archive content to file
     * @param type $content
     * @param type $folder
     * @param type $ext
     */
    protected function archive($content, $folder, $ext = "txt")
    {
        $path = \CommonHelpers::concatStrings("/", $this->tmpDir, $folder);
        $filename = \CommonHelpers::concatStrings("/", $path, time() . "." . $ext);
        \CommonHelpers::dir_exists($path);
        foreach (scandir($path) as $file) { // grease path from old files
            $file = \CommonHelpers::concatStrings("/", $path, $file);
            if (is_file($file)) {
                if (time() - filemtime($file) > $this->saveDays * 24 * 60 * 60) {
                    @unlink($file);
                }
            }
        }
        file_put_contents($filename, $content);
    }

    protected function readLastArchive($folder)
    {
        $path = \CommonHelpers::concatStrings("/", $this->tmpDir, $folder);
        if (is_dir($path)) {
            $files = scandir($path);
            sort($files);
            $last = \CommonHelpers::concatStrings("/", $path, array_pop($files));
            if (file_exists($last)) {
                return file_get_contents($last);
            }
        }
        return FALSE;
    }

}