<?php declare(strict_types = 1);

namespace ProjectFunTime\Controllers;

use Http\Request;
use Http\Response;
use ProjectFunTime\Template\FrontendRenderer;

class Homepage
{
    private $request;
    private $response;
    private $renderer;

    private $templateDir = '';

    public function __construct(Request $request, Response $response, FrontendRenderer $renderer)
    {
       $this->request = $request;
       $this->response = $response;
       $this->renderer = $renderer;
    }

    public function show()
    {
       $data = [
          'name' => $this->request->getParameter('name', 'stranger'),
       ];
       $html = $this->renderer->render($this->templateDir, 'Homepage', $data);
       $this->response->setContent($html);
    }

    public function signout()
    {
       session_destroy();
    }
}