<?php

namespace SypherLev\Chassis\Response;


class CliResponse implements ResponseInterface
{
    private $data;
    private $message;
    private $outputfile = 'output.log';
    private $outputtype = 'cli';
    private $overwrite = true;

    public function setOutputMessage(string $message) {
        $this->message = $message;
    }

    /**
     * @psalm-suppress MissingParamType
     */
    public function insertOutputData(string $label, $data)
    {
        $this->data[$label] = $data;
    }

    public function setFileOutput(string $filename, bool $overwrite = true) {
        $this->outputfile = $filename;
        $this->outputtype = 'file';
        $this->overwrite = $overwrite;
    }

    public function out() {
        $outstring = "[".date("Y-m-d H:i:s", time())."] Message: ".$this->message."\n";
        if(!empty($this->data)) {
            $outstring .= "Data: \n-------------------------\n";
            foreach ($this->data as $idx => $entity) {
                if(is_array($entity)) {
                    foreach ($entity as $inneridx => $sub) {
                        $outstring .= "$inneridx => " . json_encode($sub) . "\n\n";
                    }
                }
                else {
                    $outstring .= "$idx => " . json_encode($entity) . "\n\n";
                }
            }
        }
        if($this->outputtype == 'cli') {
            echo $outstring;
        }
        else {
            touch($this->outputfile);
            if(file_exists($this->outputfile) && $this->overwrite) {
                file_put_contents($this->outputfile, $outstring."\n\n");
            }
            else if(file_exists($this->outputfile) && !$this->overwrite) {
                file_put_contents($this->outputfile, $outstring."\n\n", FILE_APPEND);
            }
        }
    }
}