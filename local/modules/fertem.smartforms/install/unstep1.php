<?php
if (!check_bitrix_sessid()) { return; }
?>
<form method="post" action="<?= $APPLICATION->GetCurPage() ?>">
    <?= bitrix_sessid_post() ?>
    <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
    <input type="hidden" name="id" value="fertem.smartforms">
    <input type="hidden" name="uninstall" value="Y">
    <input type="hidden" name="step" value="2">
    <p><label><input type="checkbox" name="save_data" value="Y" checked> Сохранить формы и заявки</label></p>
    <input type="submit" value="Удалить модуль">
</form>

