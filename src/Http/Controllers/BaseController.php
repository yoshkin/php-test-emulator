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

    /**
     * Return Json Response
     * @param $result
     * @return string
     */
    protected function json($result)
    {
        return json_encode(
            array(
                'success' => $result['success'],
                'data' => $result['data']
            )
        );
    }


    /**
     * Валидация диапазона (min - max)
     * @param $range
     * @return array
     */
    protected function validateRange($range)
    {
        try {
            $min = isset($range['min']) ? (int)$range['min'] : 0;
            $max = isset($range['max']) ? (int)$range['max'] : 0;

            if ($min <= $max && $min >= 0 && $max <= 100) {
                return array('min' => $min, 'max' => $max);
            } else {
                throw new \LogicException('Допустимы значения от 0 до 100');
            }
        } catch (\LogicException $e) {
            return array(
                'success' => false,
                'data' => $e->getMessage()
            );
        }
    }
}