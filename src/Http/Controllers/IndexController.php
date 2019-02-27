<?php
namespace AYashenkov\Http\Controllers;

class IndexController extends BaseController
{
    /**
     * index
     */
    public function index()
    {
        $html = $this->renderer->render('index.html');
        $this->response->setContent($html);
    }
}