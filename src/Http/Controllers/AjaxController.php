<?php
namespace AYashenkov\Http\Controllers;

class AjaxController extends BaseController
{
    /**
     * Save settings
     */
    public function saveSettings()
    {
        $data = $this->service->saveSettings($this->request->getBodyParameters());
        $content = $this->json($data);
        $this->response->setContent($content);
    }

    /**
     * Get tests results history
     */
    public function getHistory()
    {
        $content = $this->renderer->render('history.html', $this->service->getResultsHistory());
        $this->response->setContent($content);
    }

    public function runEmulator()
    {
        $data = $this->service->runEmulator($this->request->getBodyParameters());
        $html = $this->renderer->render('result.html', $data['data']);
        $data['data']['questions'] = $html;

        $content = $this->json($data);
        $this->response->setContent($content);
    }
}