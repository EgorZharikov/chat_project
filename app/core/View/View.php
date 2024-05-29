<?php
namespace App\core\View;

class View
{
    public function render($content_view, $template_view = null, $payload = null)
    {
        if($template_view) {
            include_once LAYOUT . $template_view;
        } else {
            include_once VIEWS . $content_view;
        }

    }
}