<?php
namespace App\Controllers;

use App\Libraries\EquipmentSearch;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class Search extends APIController
{
    public function index(): ResponseInterface
    {
        try {
            $options = EquipmentSearch::options($this->request->getGet());
            $counts = array_fill_keys(EquipmentSearch::CATEGORIES, 0);
            $pageSize = 25;
            if ($options['q'] === '') return $this->buildAPIResponse(200, [
                'results'=>[], 'counts'=>$counts, 'total'=>0, 'page'=>1, 'page_size'=>$pageSize
            ]);
            $db = db_connect();
            if ($this->request->getGet('preview') === '1') {
                $options['category'] = 'equipment';
                $plan = EquipmentSearch::plan($options, 'equipment');
                $rows = $db->query($plan['sql'] . ' LIMIT 6', $plan['bindings'])->getResultArray();
                return $this->buildAPIResponse(200, ['results'=>array_map(fn($row)=>EquipmentSearch::result($row, $plan, $options), $rows)]);
            }
            foreach (EquipmentSearch::CATEGORIES as $category) {
                $plan = EquipmentSearch::plan($options, $category);
                $counts[$category] = (int)$db->query($plan['count_sql'], $plan['count_bindings'])->getRowArray()['total'];
            }
            $total = $counts[$options['category']];
            $page = min($options['page'], max(1, (int)ceil($total / $pageSize)));
            $plan = EquipmentSearch::plan($options, $options['category']);
            $offset = ($page - 1) * $pageSize;
            $rows = $db->query($plan['sql'] . " LIMIT $pageSize OFFSET $offset", $plan['bindings'])->getResultArray();
            $results = array_map(fn($row)=>EquipmentSearch::result($row, $plan, $options), $rows);
            return $this->buildAPIResponse(200, ['results'=>$results, 'counts'=>$counts,
                'total'=>$total, 'page'=>$page, 'page_size'=>$pageSize]);
        } catch (\InvalidArgumentException $e) {
            return $this->buildAPIResponse(400, null, $e->getMessage());
        } catch (Throwable $e) {
            log_message('error', 'Global search failed: {message}', ['message'=>$e->getMessage()]);
            return $this->buildAPIResponse(500, null, 'Search could not be completed. Please retry.');
        }
    }
}
