<?php


namespace KrokoImport\Controller;


use KrokoImport\Exceptions\Exception;

class ErrorController extends Controller
{
    public function error(Exception $e): string
    {
        return $this->getHolder()->getView()->get('Error', array(
            'message' => $e->getMessage()
        ));
    }
}