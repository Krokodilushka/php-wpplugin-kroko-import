<?php

namespace KrokoImport\Model;

use Exception;

class View {

    private $_dir;

    public function __construct(string $dir) {
        $this->_dir = $dir;
    }

    public function get($template, $data = array()): string {
        $template_file = $this->_dir . '/' . $template . '.php';
        if (!file_exists($template_file)) {
            throw new Exception('Error template (' . $template_file . ')');
        }
        ob_start();
        if (is_array($data) && !empty($data)) {
            extract($data);
        }
        include($template_file);
        $out = ob_get_contents();
        ob_end_clean();
        return $out;
    }

}
