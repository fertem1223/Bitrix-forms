<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) { die(); }
$this->setFrameMode(true);
$instance = htmlspecialcharsbx($arResult['INSTANCE_ID']);
?>
<div class="fertem-smartform" id="<?= $instance ?>" data-form-id="<?= (int)$arResult['ID'] ?>" data-signed-parameters="<?= htmlspecialcharsbx($this->getComponent()->getSignedParameters()) ?>">
  <form novalidate>
    <input class="fertem-smartform__trap" tabindex="-1" autocomplete="off" name="website" aria-hidden="true">
    <input type="hidden" name="_started" value="<?= time() ?>">
    <input type="hidden" name="_page" value="">
    <input type="hidden" name="_utm_source"><input type="hidden" name="_utm_medium"><input type="hidden" name="_utm_campaign">
    <?php foreach ((array)$arResult['FIELDS'] as $field):
      $code = preg_replace('/[^a-zA-Z0-9_]/', '', $field['code']);
      $type = in_array($field['type'], ['text','email','tel','textarea','select','checkbox','hidden'], true) ? $field['type'] : 'text';
      $condition = !empty($field['condition_field']) ? ' data-condition-field="'.htmlspecialcharsbx($field['condition_field']).'" data-condition-value="'.htmlspecialcharsbx($field['condition_value']).'" hidden' : '';
      if ($type === 'hidden'): ?><input type="hidden" name="<?= $code ?>" value="<?= htmlspecialcharsbx($field['options'] ?? '') ?>"><?php continue; endif; ?>
      <div class="fertem-smartform__field"<?= $condition ?>>
        <label for="<?= $instance.'-'.$code ?>"><?= htmlspecialcharsbx($field['label']) ?><?= $field['required']==='Y'?' *':'' ?></label>
        <?php if ($type === 'textarea'): ?><textarea id="<?= $instance.'-'.$code ?>" name="<?= $code ?>" <?= $field['required']==='Y'?'required':'' ?>></textarea>
        <?php elseif ($type === 'select'): ?><select id="<?= $instance.'-'.$code ?>" name="<?= $code ?>" <?= $field['required']==='Y'?'required':'' ?>><option value="">Выберите</option><?php foreach(explode('|',(string)$field['options']) as $option): ?><option value="<?= htmlspecialcharsbx(trim($option)) ?>"><?= htmlspecialcharsbx(trim($option)) ?></option><?php endforeach ?></select>
        <?php elseif ($type === 'checkbox'): ?><input id="<?= $instance.'-'.$code ?>" type="checkbox" name="<?= $code ?>" value="Y" <?= $field['required']==='Y'?'required':'' ?>>
        <?php else: ?><input id="<?= $instance.'-'.$code ?>" type="<?= $type ?>" name="<?= $code ?>" <?= $field['required']==='Y'?'required':'' ?>><?php endif ?>
        <div class="fertem-smartform__error" data-error="<?= $code ?>"></div>
      </div>
    <?php endforeach ?>
    <button type="submit" class="fertem-smartform__submit">Отправить</button><div class="fertem-smartform__message" role="status"></div>
  </form>
</div>
<script>document.addEventListener('DOMContentLoaded',function(){window.FertemSmartForms&&window.FertemSmartForms.init(document.getElementById('<?= CUtil::JSEscape($arResult['INSTANCE_ID']) ?>'));});</script>

