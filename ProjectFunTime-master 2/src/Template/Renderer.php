<?php declare(strict_types = 1);

namespace ProjectFunTime\Template;

interface Renderer
{
   public function render($dir, $template, $data = []) : string;
}