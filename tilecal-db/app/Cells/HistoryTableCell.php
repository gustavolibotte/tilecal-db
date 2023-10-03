<?php

namespace App\Cells;

class HistoryTableCell extends BaseCell
{
    public function full(array $params)
    {
        helper(['array', 'view']);
        $viewData['histories'] = dot_array_search('histories', $params);
        if (empty($viewData['histories'])){
            $viewData['histories'] = [];
        }
        return default_view('cells/history_table', $viewData);
    }

    public function fulljs(): string
    {
        return $this->getJs('history-table.js');
    }
}
