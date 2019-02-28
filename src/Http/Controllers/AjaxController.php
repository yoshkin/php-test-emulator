<?php
namespace AYashenkov\Http\Controllers;

class AjaxController extends BaseController
{
    /**
     * Save settings
     */
    public function saveSettings()
    {
        $range = $this->validateRange($this->request->getBodyParameters());
        $content = $this->json($this->service->saveSettings($range));
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

    /**
     * Run test emulator
     */
    public function runEmulator()
    {
        $range = $this->validateRange($this->request->getBodyParameters());
        $data = $this->service->runEmulator($range);

        $html = $this->renderer->render('result.html', $data['data']);
        $data['data']['questions'] = $html;
        $content = $this->json($data);
        $this->response->setContent($content);
    }
}