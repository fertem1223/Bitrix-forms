<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
use Bitrix\Main\Loader;
use Fertem\SmartForms\FormTable;
Loader::includeModule('fertem.smartforms');
if (!$USER->IsAdmin()) { $APPLICATION->AuthForm('Доступ запрещён'); }
if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid() && isset($_POST['delete'])) { FormTable::delete((int)$_POST['delete']); }
$APPLICATION->SetTitle('Умные формы');
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
?>
<p><a class="adm-btn adm-btn-save" href="fertem_smartforms_edit.php?lang=<?= LANGUAGE_ID ?>">Создать форму</a></p>
<table class="adm-list-table" style="width:100%">
<thead><tr class="adm-list-table-header"><td>ID</td><td>Название</td><td>Код</td><td>Активна</td><td>Действия</td></tr></thead><tbody>
<?php foreach (FormTable::getList(['order' => ['ID' => 'DESC']])->fetchAll() as $item): ?>
<tr class="adm-list-table-row"><td><?= (int)$item['ID'] ?></td><td><?= htmlspecialcharsbx($item['NAME']) ?></td><td><?= htmlspecialcharsbx($item['CODE']) ?></td><td><?= $item['ACTIVE'] ?></td><td>
<a href="fertem_smartforms_edit.php?lang=<?= LANGUAGE_ID ?>&ID=<?= (int)$item['ID'] ?>">Изменить</a> ·
<a href="fertem_smartforms_submissions.php?lang=<?= LANGUAGE_ID ?>&FORM_ID=<?= (int)$item['ID'] ?>">Заявки</a>
<form method="post" style="display:inline" onsubmit="return confirm('Удалить форму?')"><?= bitrix_sessid_post() ?><button class="adm-btn" name="delete" value="<?= (int)$item['ID'] ?>">Удалить</button></form>
</td></tr><?php endforeach; ?>
</tbody></table>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php'; ?>

