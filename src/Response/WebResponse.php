<?php

namespace SypherLev\Chassis\Response;

class WebResponse implements ResponseInterface
{
    private $template;
    private $data = [];
    private $template_dir = '../templates';
    private $cache_dir = '../cache';

    public function setTemplate($template) {
        $this->template = $template;
    }

    public function insertBatchData(Array $batch) {
        foreach ($batch as $idx => $val) {
            $this->insertOutputData($idx, $val);
        }
    }

    public function insertOutputData($label, $data)
    {
        $this->data[$label] = $data;
    }

    public function setTemplateDirectory($directory) {
        $this->template_dir = $directory;
    }

    public function setCacheDirectory($directory) {
        $this->cache_dir = $directory;
    }

    public function out() {
        $loader = new \Twig_Loader_Filesystem($this->template_dir);
        $twig = new \Twig_Environment($loader, array(
            'cache' => $this->cache_dir,
            'debug' => $_ENV['devmode']
        ));
        $template = $twig->load($this->template);
        echo $template->render($this->data);
    }
}