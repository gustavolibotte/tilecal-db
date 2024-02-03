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

    protected function getBool(string|int $key, array $params): bool
    {
        $value = dot_array_search($key, $params);
        if (is_null($value)){
            return false;
        } else {
            return $value;
        }
    }
}
