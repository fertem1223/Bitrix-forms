<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use Fertem\SmartForms\FormTable;
Loader::includeModule('fertem.smartforms');
if (!$USER->IsAdmin()) { $APPLICATION->AuthForm('Доступ запрещён'); }
$id = (int)($_REQUEST['ID'] ?? 0); $form = $id ? FormTable::getById($id)->fetch() : null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid()) {
    $fields = json_decode((string)($_POST['FIELDS_JSON'] ?? '[]'), true);
    if (!is_array($fields)) { $fields = []; }
    $data = [
        'NAME' => trim((string)$_POST['NAME']), 'CODE' => preg_replace('/[^a-z0-9_]/', '', strtolower((string)$_POST['CODE'])),
        'ACTIVE' => isset($_POST['ACTIVE']) ? 'Y' : 'N', 'FIELDS' => $fields, 'DATE_UPDATE' => new DateTime(),
        'SETTINGS' => ['emails' => trim((string)$_POST['EMAILS']), 'success_message' => trim((string)$_POST['SUCCESS_MESSAGE']), 'webhook_url' => trim((string)$_POST['WEBHOOK_URL']), 'webhook_secret' => trim((string)$_POST['WEBHOOK_SECRET']), 'min_seconds' => (int)$_POST['MIN_SECONDS'], 'rate_limit' => (int)$_POST['RATE_LIMIT']],
    ];
    $result = $id ? FormTable::update($id, $data) : FormTable::add($data);
    if ($result->isSuccess()) { LocalRedirect('fertem_smartforms.php?lang=' . LANGUAGE_ID); }
    $error = implode('<br>', $result->getErrorMessages());
}
$settings = (array)($form['SETTINGS'] ?? []); $savedFields = (array)($form['FIELDS'] ?? []);
$APPLICATION->SetTitle($id ? 'Редактирование формы' : 'Новая форма');
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
if (!empty($error)) { CAdminMessage::ShowMessage($error); }
?>
<form method="post" id="sf-editor"><?= bitrix_sessid_post() ?>
<table class="adm-detail-content-table edit-table"><tbody>
<tr><td width="40%">Название *</td><td><input required name="NAME" size="45" value="<?= htmlspecialcharsbx($form['NAME'] ?? '') ?>"></td></tr>
<tr><td>Символьный код *</td><td><input required pattern="[a-z0-9_]+" name="CODE" value="<?= htmlspecialcharsbx($form['CODE'] ?? 'feedback') ?>"></td></tr>
<tr><td>Активна</td><td><input type="checkbox" name="ACTIVE" <?= !$form || $form['ACTIVE'] === 'Y' ? 'checked' : '' ?>></td></tr>
<tr><td>Получатели (через запятую)</td><td><input name="EMAILS" size="45" value="<?= htmlspecialcharsbx($settings['emails'] ?? '') ?>"></td></tr>
<tr><td>Сообщение об успехе</td><td><input name="SUCCESS_MESSAGE" size="60" value="<?= htmlspecialcharsbx($settings['success_message'] ?? 'Спасибо! Мы получили вашу заявку.') ?>"></td></tr>
<tr><td>Webhook URL (только HTTPS)</td><td><input type="url" name="WEBHOOK_URL" size="60" value="<?= htmlspecialcharsbx($settings['webhook_url'] ?? '') ?>"></td></tr>
<tr><td>Секрет webhook</td><td><input name="WEBHOOK_SECRET" size="45" value="<?= htmlspecialcharsbx($settings['webhook_secret'] ?? '') ?>"></td></tr>
<tr><td>Минимальное время заполнения, сек.</td><td><input type="number" min="0" name="MIN_SECONDS" value="<?= (int)($settings['min_seconds'] ?? 2) ?>"></td></tr>
<tr><td>Лимит с одного IP-хеша за 10 минут</td><td><input type="number" min="1" name="RATE_LIMIT" value="<?= (int)($settings['rate_limit'] ?? 5) ?>"></td></tr>
</tbody></table>
<h3>Поля</h3><div id="fields"></div><p><button type="button" class="adm-btn" id="add-field">+ Добавить поле</button></p>
<input type="hidden" name="FIELDS_JSON" id="fields-json"><input type="submit" class="adm-btn adm-btn-save" value="Сохранить">
</form>
<script>
(function(){
 const root=document.getElementById('fields'), initial=<?= CUtil::PhpToJSObject($savedFields) ?>;
 const esc=s=>String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
 function add(f={type:'text',required:'N'}){const d=document.createElement('div');d.className='adm-info-message';d.innerHTML=`<input data-k="label" placeholder="Подпись" value="${esc(f.label)}" required> <input data-k="code" placeholder="code" value="${esc(f.code)}" required> <select data-k="type">${['text','email','tel','textarea','select','checkbox','hidden'].map(x=>`<option ${x===f.type?'selected':''}>${x}</option>`)}</select> <label><input data-k="required" type="checkbox" ${f.required==='Y'?'checked':''}> обязательное</label><br><input data-k="options" size="45" placeholder="Варианты через | для select" value="${esc(f.options)}"> <input data-k="condition_field" placeholder="Поле-условие" value="${esc(f.condition_field)}"> <input data-k="condition_value" placeholder="Значение условия" value="${esc(f.condition_value)}"> <button type="button">Удалить</button>`;d.querySelector('button').onclick=()=>d.remove();root.appendChild(d)}
 (initial.length?initial:[{label:'Имя',code:'name',type:'text',required:'Y'},{label:'Телефон',code:'phone',type:'tel',required:'Y'},{label:'Сообщение',code:'message',type:'textarea',required:'N'}]).forEach(add);
 document.getElementById('add-field').onclick=()=>add(); document.getElementById('sf-editor').onsubmit=()=>{document.getElementById('fields-json').value=JSON.stringify([...root.children].map(d=>{const o={};d.querySelectorAll('[data-k]').forEach(e=>o[e.dataset.k]=e.type==='checkbox'?(e.checked?'Y':'N'):e.value);return o}))};
})();
</script>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php'; ?>

