<?php


namespace KrokoImport\Controller;


use KrokoImport\Exceptions\Exception;

class Error_Controller extends Controller
{
    public function error(Exception $e): string
    {
        return $this->getHolder()->getView()->get('view-error', array(
            'message' => $e->getMessage()
        ));
    }
}