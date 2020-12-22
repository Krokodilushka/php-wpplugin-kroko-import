<?php

namespace KrokoImport;

class View {

    private $_dir;

    public function __construct(string $dir) {
        $this->_dir = $dir;
    }

    public function get($template, $data = array()): string {
        $templateFile = $this->_dir . '/' . $template . '.php';
        if (!file_exists($templateFile)) {
            throw new Exception('Error template (' . $templateFile . ')');
        }
        ob_start();
        if (is_array($data) && !empty($data)) {
            extract($data);
        }
        include($templateFile);
        $out = ob_get_contents();
        ob_end_clean();
        return $out;
    }

}
