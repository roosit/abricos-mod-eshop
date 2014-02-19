<?php

/**
 * @package Abricos
 * @subpackage EShop
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin <roosit@abricos.org>
 */
$brick = Brick::$builder->brick;
$p = &$brick->param->param;
$v = &$brick->param->var;

$man = EShopModule::$instance->GetManager()->cManager;

$modCart = Abricos::GetModule('eshopcart');

$imgWidth = bkint($p['imgw']);
$imgHeight = bkint($p['imgh']);

Abricos::GetModule('filemanager')->EnableThumbSize(array(array(
        "w" => $imgWidth,
        "h" => $imgHeight
)));

if (is_object($p['cfg'])) {
    $cfg = $p['cfg'];
} else {
    $cfg = new CatalogElementListConfig();
    $cfg->limit = $p['limit'];
}

if ($p['forcontent'] == 'true') {

    $cat = $man->CatalogByAdress();
    array_push($cfg->catids, $cat->id);

    $cfg->limit = EShopConfig::$instance->productPageCount;

    $adr = Abricos::$adress;
    $page = $adr->dir[count($adr->dir) - 1];

    if (preg_match("/^page[0-9]+/", $page)) {
        $page = intval(substr($page, 4));
        if ($page > 0) {
            $cfg->page = $page;
        }
    }

    if ($p['notchildlist'] == 'false') {
        $catList = $cat->childs;
        for ($i = 0; $i < $catList->Count(); $i++) {
            array_push($cfg->catids, $catList->GetByIndex($i)->id);
        }
    }
} else {

    // return;
}

$elList = $man->ProductList($cfg);
$brick->elementList = $elList;
if (empty($elList)) {
    $brick->content = "";
    return;
}

$lst = "";
$lstz = "";
for ($i = 0; $i < $elList->Count(); $i++) {
    $el = $elList->GetByIndex($i);

    $pTitle = addslashes(htmlspecialchars($el->title));

    $pr_spec = !empty($el->ext['akc']) ? $v['pr_akc'] : "";
    $pr_spec .=!empty($el->ext['new']) ? $v['pr_new'] : "";
    $pr_spec .=!empty($el->ext['hit']) ? $v['pr_hit'] : "";

    $pr_special = "";
    if (!empty($pr_spec)) {
        $pr_special = Brick::ReplaceVar($v["special"], "pr_spec", $pr_spec);
    }

    if (empty($el->foto)) {
        $image = $v["imgempty"];
    } else {
        $image = Brick::ReplaceVarByData($v["img"], array(
                    "src" => $el->FotoSrc($imgWidth, $imgHeight)
        ));
    }
    $image = Brick::ReplaceVarByData($image, array(
                "w" => $imgWidth,
                "h" => $imgHeight
    ));

    $replace = array(
        "imgw" => $imgWidth,
        "imgh" => $imgHeight,
        "special" => $pr_special,
        "buybutton" => "",
        "image" => $image,
        "title" => $pTitle,
        "price" => number_format($el->ext['price'], 2, ',', ' '),
        "link" => $el->URI(),
        "productid" => $el->id
    );

    if (!empty($modCart)) {
        $cartBrick = Brick::$builder->LoadBrickS('eshopcart', 'buybutton', null, array("p" => array(
                "product" => $el
        )));
        $replace["buybutton"] = $cartBrick->content;
    }

    if ($el->ext['price'] > 0) {
        $lst .= Brick::ReplaceVarByData($v['row'], $replace);
    } else {
        $lstz .= Brick::ReplaceVarByData($v['row'], $replace);
    }
}

$result = Brick::ReplaceVarByData($v['table'], array(
		"rows" => $lst.$lstz
	));

if ($p['scroll'] == 'true') {
    $result = Brick::ReplaceVarByData($v["scrolldiv"], array(
                "result" => $result
    ));
}

$brick->content = Brick::ReplaceVarByData($brick->content, array(
            "display" => $p['display'],
            "result" => $result,
            "brickid" => $brick->id
        ));

if (!empty($modCart)) {
    $cartBrick = Brick::$builder->LoadBrickS('eshopcart', 'buybuttonjsinit');
    $brick->content .= $cartBrick->content;
}


// TODO: реализовать СЕО, разные типы строк и возможность добавления через пар-тр своих полей 

return; ///////////////////////// END //////////////////////////////

/*
  $listPage = $listData['listPage'];
  if (intval($p['page'])>0){
  $listPage = intval($p['page']);
  }

  $db = Abricos::$db;
  $mod = EShopModule::$instance;

  $catalog = $mod->GetCatalog();
  $catalogManager = $mod->GetCatalogManager();

  $smMenu = Abricos::GetModule('sitemap')->GetManager()->GetMenu();

  $catItemMenu = $smMenu->menuLine[count($smMenu->menuLine)-1];
  $catItem = $catItemMenu->source;

  $link = $baseUrl = $catItemMenu->link;

  $listData = $mod->GetManager()->GetProductListData();

  $listPage = $listData['listPage'];
  if (intval($p['page'])>0){
  $listPage = intval($p['page']);
  }

  $catids = $listData['catids'];

  if ($p['notchildlist']){
  $catids = array($catItem['id']);
  }

  $tempArr = array();

  $custOrder = empty($p['custorder']) ? "fld_price=0, fld_price" : $p['custorder'];

  if ($p['forcontent'] == 'true'){
  $p['count'] = EShopConfig::$instance->productPageCount;
  }

  $rows = $catalogManager->ElementList($catids, $listPage, bkint($p['count']), $p['custwhere'], $custOrder, $p['overfields']);

  $brick->totalElementCount = $catalogManager->ElementCount($catids, $p['custwhere']);

  $elTypeList = $catalogManager->ElementTypeListArray();

  $lstResult = "";
  $strList = array();

  $etArr0 = $catalogManager->ElementOptionListByType($el['eltid'], true);

  while (($row = $db->fetch_array($rows))){
  $el = $catalogManager->Element($row['id'], true);
  $el['fld_name'] = $el['tl'];

  if (empty($tempArr[$el['catid']])){
  $tempArr[$el['catid']] = $smMenu->FindSource('id', $el['catid']);
  }
  $link = $tempArr[$el['catid']]->link;

  $pTitle = addslashes(htmlspecialchars($el['fld_name']));
  $pTitleSeo = "";
  if (EShopConfig::$instance->seo){
  $pTitleSeo = translateruen($el['fld_name']);
  }

  // Проверка, является ли товар Новинкой, Акцией или Хитом продаж
  $pr_spec = $el['fld_akc'] != 0 ? $v["isakc"] : "";
  $pr_spec .= $el['fld_new'] != 0 ? $v["pr_new"] : "";
  $pr_spec .= $el['fld_hit'] != 0 ? $v["pr_hit"] : "";

  $pr_special = "";
  if (!empty($pr_spec)){
  $pr_special = Brick::ReplaceVar($v["special"], "pr_spec", $pr_spec);
  }

  $imginfo = $db->fetch_array($catalogManager->FotoListThumb($el['elid'], $imgWidth, $imgHeight, 1));

  if (empty($imginfo)){
  $image = Brick::ReplaceVarByData($v["imgempty"], array(
  ));
  }else{
  $thumb = CatalogModule::FotoThumbInfoParse($imginfo['thumb']);

  $imgName = $imginfo['fn'];
  if (EShopConfig::$instance->seo){
  $imgName = $pTitleSeo.".".$imginfo['ext'];
  }

  $image = Brick::ReplaceVarByData($v["img"], array(
  "src" => CatalogModule::FotoThumbLink($imginfo['fid'], $imgWidth, $imgHeight, $imgName),
  "w" => ($thumb['w']>0 ? $thumb['w']."px" : ""),
  "h" => ($thumb['h']>0 ? $thumb['h']."px" : "")
  ));
  }
  $replace = array(
  "special" => $pr_special,
  "tpl_btn" => $v[$el['fld_sklad']==0 ? 'btnnotorder' : 'btnorder'],
  "image" => $image,
  "title" => $pTitle,
  "price" => $el['fld_price'],
  "desc" => $el['fld_desc'],
  "link" => $link."product_".$row['id']."/",
  "productid" => $row['id']
  );


  $etArr = $catalogManager->ElementOptionListByType($el['eltid'], true);
  $etArr = array_merge($etArr0, $etArr);

  foreach ($etArr as $etRow){
  $fld = "fld_".$etRow['nm'];

  // Если опция пуста - пробел, чтобы не рушить верстку
  $el[$fld] = !empty($el[$fld]) ? $el[$fld] : '&nbsp;';
  if ($etRow['nm'] != 'desc'){
  // $el[$fld] = htmlspecialchars($el[$fld]);
  }
  $replace[$fld] = $el[$fld];

  // Если тип опции - таблица (fldtp = 5), то необходимо получить значение опции из таблицы
  // if	($row['fldtp'] == 5){
  // Получаем значение опции 'tl'. '' - т.к. тип товара - default
  // $val = $catalogManager->ElementOptionFieldTableValue('', $row['nm'], $el[$fld]);
  // $replace[$fld] = $val['tl'];
  // }

  $replace["fldnm_".$etRow['nm']] = $etRow['tl'];
  }
  $isChangeType = false;
  $tpRow = $v['row'];
  $elTypeId = $el['eltid'];
  if (!empty($elTypeList[$elTypeId])){
  $elTypeName = $elTypeList[$elTypeId]['nm'];
  if (!empty($v['row-'.$elTypeName])){
  $tpRow = $v['row-'.$elTypeName];
  $isChangeType = true;
  }
  }
  $strList[$isChangeType ? $elTypeId : 0] .= Brick::ReplaceVarByData($tpRow, $replace);
  }

  $lstResult = "";
  foreach ($strList as $key => $value){
  $tpTable = $v['table'];
  $elTypeName = $elTypeList[$key]['nm'];
  if (!empty($v['table-'.$elTypeName])){
  $tpTable = $v['table-'.$elTypeName];
  }
  $lstResult .= Brick::ReplaceVarByData($tpTable, array(
  "page" => $listPage, "rows" => $value
  ));
  }

  $brick->content = Brick::ReplaceVarByData($brick->content, array(
  "display" => $p['display'],
  "result" => $lstResult
  ));
  /* */
?>