<?php declare(strict_types = 1);

namespace ProjectFunTime\Template;

use ProjectFunTime\Menu\MenuReader;
use ProjectFunTime\Session\SessionWrapper;

class FrontendTwigRenderer implements FrontendRenderer
{
   private $menuReader;
   private $renderer;
   private $session;

   public function __construct(MenuReader $menuReader, Renderer $renderer, SessionWrapper $session)
   {
      $this->menuReader = $menuReader;
      $this->renderer = $renderer;
      $this->session = $session;
   }

   public function render($dir, $template, $data = []) : string
   {
      $accType = $this->session->getValue('accType');

      $data = array_merge($data, [
         'menuItems' => $this->menuReader->readMenu($accType),
         'accType' => $accType
      ]);

      return $this->renderer->render($dir, $template, $data);
   }
}