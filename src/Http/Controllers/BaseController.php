<?php

namespace AYashenkov\Http\Controllers;

use AYashenkov\Services\IRenderer;
use AYashenkov\Services\Emulator;
use Http\Request;
use Http\Response;

class BaseController
{
    /** @var Response */
    protected $response;

    /** @var Request */
    protected $request;

    /** @var Emulator */
    protected $service;

    /** @var IRenderer */
    protected $renderer;
    /**
     * IndexController constructor.
     * @param Response $response
     * @param Request $request
     * @param Emulator $service
     * @param IRenderer $renderer
     */
    public function __construct(
        Response $response,
        Request $request,
        Emulator $service,
        IRenderer $renderer
    )
    {
        $this->response = $response;
        $this->request = $request;
        $this->service = $service;
        $this->renderer = $renderer;
    }

    protected function json($result)
    {
        return json_encode(
            array(
                'success' => $result['success'],
                'data' => $result['data']
            )
        );
    }
}