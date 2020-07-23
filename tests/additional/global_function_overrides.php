<?php

namespace SypherLev\Chassis {
    function header($string) {
        echo $string;
    }
}

namespace SypherLev\Chassis\Request {
    function file_get_contents($filename, $use_include_path = false, $context = null, $offset = 0, $maxlen = null) {
        if($filename === 'php://input') {
            $json_test = [
                'one' => 'variable1',
                'two' => 'variable2'
            ];
            return json_encode($json_test);
        }
        return \file_get_contents($filename, $use_include_path, $context, $offset, $maxlen);
    }

    function getopt($options, array $longopts = null, &$optind = null) {
        global $argv;
        $options = str_replace(":", "", $options);
        foreach ($argv as $opt) {
            $split = explode(" ", $opt);
            if($split[0] == "-".$options && count($split) > 1) {
                return [$options => $split[1]];
            }
        }
        return false;
    }
}

namespace SypherLev\Chassis\Response {

    function readfile($filepath, $use_include_path = null, $context = null) {
        file_put_contents($filepath, "\nReadfile success", FILE_APPEND);
    }

    function ob_get_level() {
        $ob_counter = getenv('ob_counter');
        return $ob_counter;
    }

    function ob_end_clean() {
        $ob_counter = getenv('ob_counter');
        $ob_counter--;
        putenv('ob_counter='.$ob_counter);
    }

    function curl_exec ($ch) {
        if(getenv("curl_throw_exception") == 'true') {
            return false;
        }
        $info = curl_getinfo($ch);
        echo $info['url'];
        return true;
    }
}

namespace SypherLev\Chassis\Migrate {
    function shell_exec($cmd) {
        if (strpos($cmd, 'error') !== false) {
            return "ERROR";
        }
        return null;
    }
}