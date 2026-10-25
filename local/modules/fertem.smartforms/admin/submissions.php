<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
use Bitrix\Main\Loader; use Fertem\SmartForms\SubmissionTable; use Fertem\SmartForms\FormTable;
Loader::includeModule('fertem.smartforms'); if (!$USER->IsAdmin()) { $APPLICATION->AuthForm('Доступ запрещён'); }
$formId=(int)($_REQUEST['FORM_ID']??0); $filter=$formId?['=FORM_ID'=>$formId]:[];
if (isset($_GET['export']) && check_bitrix_sessid()) { require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_js.php'; header('Content-Type: text/csv; charset=UTF-8'); header('Content-Disposition: attachment; filename="smartforms.csv"'); echo "\xEF\xBB\xBF"; $out=fopen('php://output','w'); fputcsv($out,['ID','FORM_ID','STATUS','DATE','DATA'], ';'); foreach(SubmissionTable::getList(['filter'=>$filter,'order'=>['ID'=>'DESC']])->fetchAll() as $r){fputcsv($out,[$r['ID'],$r['FORM_ID'],$r['STATUS'],(string)$r['DATE_CREATE'],json_encode($r['DATA'],JSON_UNESCAPED_UNICODE)],';');} fclose($out); die(); }
$forms=[]; foreach(FormTable::getList()->fetchAll() as $f){$forms[$f['ID']]=$f['NAME'];}
$APPLICATION->SetTitle('Заявки'); require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php';
?>
<p><a class="adm-btn" href="?lang=<?= LANGUAGE_ID ?>&FORM_ID=<?= $formId ?>&export=Y&<?= bitrix_sessid_get() ?>">Экспорт CSV</a></p>
<table class="adm-list-table" style="width:100%"><thead><tr class="adm-list-table-header"><td>ID</td><td>Форма</td><td>Дата</td><td>Статус</td><td>Данные</td><td>Источник</td></tr></thead><tbody>
<?php foreach(SubmissionTable::getList(['filter'=>$filter,'order'=>['ID'=>'DESC'],'limit'=>500])->fetchAll() as $r): ?><tr class="adm-list-table-row"><td><?= $r['ID'] ?></td><td><?= htmlspecialcharsbx($forms[$r['FORM_ID']]??('#'.$r['FORM_ID'])) ?></td><td><?= $r['DATE_CREATE'] ?></td><td><?= htmlspecialcharsbx($r['STATUS']) ?></td><td><?php foreach((array)$r['DATA'] as $k=>$v): ?><b><?= htmlspecialcharsbx($k) ?>:</b> <?= htmlspecialcharsbx($v) ?><br><?php endforeach ?></td><td><?= htmlspecialcharsbx($r['META']['utm_source']??$r['META']['referer']??'') ?></td></tr><?php endforeach ?>
</tbody></table>
<?php require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php'; ?>

