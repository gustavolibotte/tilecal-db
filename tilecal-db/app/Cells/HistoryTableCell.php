<?php

namespace App\Cells;

class HistoryTableCell extends BaseCell
{
    public function full(array $params)
    {
        helper('array');
        $histModel = new \App\Models\HistModel();
        $id = dot_array_search('id', $params);
        $type = dot_array_search('type', $params);
        if ($type == 'equipment'){
            $viewData['histories'] = $histModel->getEquipmentHist($id);
        } elseif ($type == 'file'){
            $viewData['histories'] = $histModel->getFileHist($id); 
        } else{
            throw new \Exception(sprintf('Type %s is not supported for history', $type));
        }
        return view('cells/history_table', $viewData);
    }
}
