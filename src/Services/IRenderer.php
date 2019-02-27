<?php
namespace AYashenkov\Services;

interface IRenderer
{
    /**
     * Render template to frontend
     * @param $template (for example 'index.html')
     * @param array $data
     * @return string
     */
    public function render($template, $data = []);
}