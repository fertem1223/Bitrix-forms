<?php
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);
return [
    'parent_menu' => 'global_menu_services',
    'section' => 'fertem_smartforms',
    'sort' => 250,
    'text' => Loc::getMessage('FERTEM_SF_MENU'),
    'title' => Loc::getMessage('FERTEM_SF_MENU_TITLE'),
    'icon' => 'form_menu_icon',
    'page_icon' => 'form_page_icon',
    'items_id' => 'menu_fertem_smartforms',
    'items' => [
        ['text' => Loc::getMessage('FERTEM_SF_FORMS'), 'url' => 'fertem_smartforms.php?lang=' . LANGUAGE_ID, 'more_url' => ['fertem_smartforms_edit.php']],
        ['text' => Loc::getMessage('FERTEM_SF_SUBMISSIONS'), 'url' => 'fertem_smartforms_submissions.php?lang=' . LANGUAGE_ID],
    ],
];

