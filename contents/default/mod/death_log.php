<?php
$tpl_death = Util::newTpl($this, 'mod/death_log', 'death_log');

$page = fRequest::get('p', 'int', 1);
$limit = 10;

$death_log = fORMDatabase::retrieve('name:' . DB_TYPE)->translatedQuery(
    '
    SELECT SQL_CALC_FOUND_ROWS material_id, entity_id AS id1, player_id AS id2, time, player_killed
    FROM prefix_detailed_pve_kills
    UNION ALL
    SELECT material_id, player_id AS id1, victim_id AS id2, time, NULL AS player_killed
    FROM prefix_detailed_pvp_kills
    ORDER BY time DESC
    LIMIT %i,%i
    ',
    $limit * ($page - 1),
    $limit
);

$pages = fORMDatabase::retrieve('name:' . DB_TYPE)->translatedQuery('SELECT FOUND_ROWS()')->fetchScalar();

$tpl_death->set('death_log', $death_log);
$tpl_death->set('death_log_page', $page);
$tpl_death->set('death_log_pages', ceil($pages / $limit));

if(fRequest::isAjax()) {
    $tpl_death->place();
    die();
}