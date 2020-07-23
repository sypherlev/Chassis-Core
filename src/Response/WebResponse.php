<?php

namespace SypherLev\Chassis\Response;

class WebResponse implements ResponseInterface
{
    private $template;
    private $data = [];
    private $template_dir = '../templates';
    private $cache_dir = '../cache';

    public function setTemplate(string $template) {
        $this->template = $template;
    }

    /**
     * @psalm-suppress MissingParamType
     */
    public function insertBatchData(Array $batch) {
        foreach ($batch as $idx => $val) {
            $this->insertOutputData($idx, $val);
        }
    }

    /**
     * @psalm-suppress MissingParamType
     */
    public function insertOutputData(string $label, $data)
    {
        $this->data[$label] = $data;
    }

    public function setTemplateDirectory(string $directory) {
        $this->template_dir = $directory;
    }

    public function setCacheDirectory(string $directory) {
        $this->cache_dir = $directory;
    }

    public function out() {
        $loader = new \Twig_Loader_Filesystem($this->template_dir);
        if (getenv('devmode') === 'true') {
            $twig_config = [
                'cache' => false,
                'debug' => true,
                'auto_reload' => true
            ];
        }
        else {
            $twig_config = [
                'cache' => $this->cache_dir,
                'debug' => false
            ];
        }
        $twig = new \Twig_Environment($loader, $twig_config);
        $template = $twig->load($this->template);
        echo $template->render($this->data);
    }
}