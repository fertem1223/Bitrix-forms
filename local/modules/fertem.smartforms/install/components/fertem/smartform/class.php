<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) { die(); }

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Loader;
use Fertem\SmartForms\FormService;
use Fertem\SmartForms\FormTable;

class FertemSmartFormComponent extends CBitrixComponent implements Controllerable
{
    public function configureActions(): array
    {
        return ['submit' => ['prefilters' => [new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]), new ActionFilter\Csrf()]]];
    }

    public function submitAction(array $fields = []): array
    {
        if (!Loader::includeModule('fertem.smartforms')) { return ['ok' => false, 'errors' => ['Модуль не установлен.']]; }
        $form = FormTable::getById((int)$this->arParams['FORM_ID'])->fetch();
        if (!$form || $form['ACTIVE'] !== 'Y') { return ['ok' => false, 'errors' => ['Форма недоступна.']]; }
        return FormService::submit($form, $fields, $_SERVER);
    }

    public function executeComponent()
    {
        if (!Loader::includeModule('fertem.smartforms')) { ShowError('Модуль fertem.smartforms не установлен.'); return; }
        $form = FormTable::getById((int)$this->arParams['FORM_ID'])->fetch();
        if (!$form || $form['ACTIVE'] !== 'Y') { return; }
        $this->arResult = $form;
        $this->arResult['INSTANCE_ID'] = 'fertem-sf-' . (int)$form['ID'] . '-' . substr(md5($this->getTemplateName()), 0, 6);
        $this->includeComponentTemplate();
    }
}

