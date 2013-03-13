<?php
$tpl_blocks = Util::newTpl($this, 'mod/total_blocks', 'total_blocks');

switch(fRequest::get('order_by', 'int')) {
    case 1:
        $type = 'tp_name';
        break;
    default:
    case 2:
        $type = 'SUM(destroyed)';
        break;
    case 3:
        $type = 'SUM(placed)';
        break;
}

$page = fRequest::get('page', 'int', 1);

$blocks = fRecordSet::buildFromSQL(
    'Material',
    array(
         '
        SELECT m.* FROM "prefix_materials" m
        LEFT JOIN "prefix_total_blocks" b ON m.material_id = b.material_id
        GROUP BY b.material_id
        ORDER BY ' . $type . ' ' . fRequest::get('order_sort', 'string', 'desc') . '
        LIMIT %i,20
    ',
         ($page - 1) * 20
    ),
    '
    SELECT COUNT(*) FROM (SELECT m.* FROM "prefix_materials" m
    RIGHT JOIN "prefix_total_blocks" b ON m.material_id = b.material_id
    GROUP BY b.material_id) c
    ',
    20,
    $page
);

$tpl_blocks->set('block_list', $blocks);

if(fRequest::isAjax()) {
    $tpl_blocks->place();
    die();
}