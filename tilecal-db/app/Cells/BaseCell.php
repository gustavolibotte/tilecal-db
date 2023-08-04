<?php

namespace App\Cells;

class BaseCell
{
    protected function getJs($filepath)
    {
        $jsUrl = base_url('assets/js/cells/' . $filepath);
        $script = sprintf('<script type="text/javascript" src="%s" ></script>', $jsUrl);
        return $script;
    }
}
