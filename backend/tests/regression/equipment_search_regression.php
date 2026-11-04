<?php
require __DIR__ . '/../../app/Libraries/EquipmentSearch.php';
use App\Libraries\EquipmentSearch as Search;
$count=0;
function check($ok,$message) {global $count;$count++;if(!$ok)throw new RuntimeException($message);}
foreach (['contains','all_words','word','prefix','exact'] as $mode) {
 $options=Search::options(['q'=>"test beam's 10%_!",'mode'=>$mode]);
 foreach(Search::CATEGORIES as $category) {
  $plan=Search::plan($options,$category);
  check(substr_count($plan['sql'],'?')===count($plan['bindings']),'Bound select placeholders');
  check(substr_count($plan['count_sql'],'?')===count($plan['count_bindings']),'Bound count placeholders');
  check(!str_contains($plan['sql'],"beam's"),'User input outside SQL');
  check(str_contains($plan['sql'],'deleted_at IS NULL'),'Exclude deleted');
 }
}
$b=[];$condition=Search::condition('e.comments','10%_!','contains',$b);
check($b===['%10!%!_!!%'],'Literal LIKE wildcards');
$b=[];Search::condition('e.comments','A.B','word',$b);check(str_contains($b[0],'a\\.b'),'Literal regex punctuation');
$b=[];Search::condition('e.id','20LLCHI1000001','exact',$b);check($b===['20llchi1000001','1000001'],'Full barcode alias');
$b=[];Search::condition('e.comments','20LLCHI1000001','exact',$b);check(count($b)===1,'Barcode alias only identifiers');
$o=Search::options(['q'=>'test','fields'=>'comments']);$p=Search::plan($o,'equipment');
check(array_keys($p['fields'])===['comments'],'Comments-only fields');
check(str_contains(Search::plan($o,'location')['count_sql'],'0=1'),'Comments excludes catalogs');
$o=Search::options(['q'=>'x','fields'=>'identifiers,type,location,status,owner']);$p=Search::plan($o,'equipment');
check(!str_contains($p['sql'],'e.comments'),'Other-fields excludes comment column');
foreach ([['q'=>str_repeat('a',201)],['q'=>[]],['fields'=>'comments,invalid'],['fields'=>''],['mode'=>'bad'],['sort'=>'SQL'],['category'=>'bad'],['page'=>0],['page'=>'1 UNION'],['q'=>str_repeat('x ',11),'mode'=>'all_words']] as $bad) {
 try { Search::options($bad);check(false,'Expected validation failure'); }catch(InvalidArgumentException $e){check(true,'Rejected');}
}
$s=Search::snippet(str_repeat('prefix ',60).'needle'.str_repeat(' suffix',60),['needle']);
check(str_contains($s,'needle') && mb_strlen($s)<=242,'Centered bounded snippet');
check(Search::options(['q'=>'  0  '])['q']==='0','Zero is a searchable term');
$s=Search::snippet('beaming '.str_repeat('other ',60).'beam', ['beam'], 'word');
check(str_ends_with($s,'beam') && !str_contains($s,'beaming'),'Whole-word snippet follows actual match');
echo "Passed $count search assertions. No database access.\n";
