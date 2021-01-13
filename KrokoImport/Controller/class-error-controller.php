<?php


namespace KrokoImport\Controller;


use KrokoImport\Exceptions\Exception;

class Error_Controller extends Controller
{
    public function error(Exception $e): string
    {
        return $this->get_holder()->get_view()->get('view-error', array(
            'message' => $e->getMessage()
        ));
    }
}