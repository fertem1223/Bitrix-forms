<?php
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class fertem_smartforms extends CModule
{
    public $MODULE_ID = 'fertem.smartforms';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $PARTNER_NAME = 'fertem1223';
    public $PARTNER_URI = 'https://github.com/fertem1223/Bitrix-forms';

    public function __construct()
    {
        include __DIR__ . '/version.php';
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = Loc::getMessage('FERTEM_SMARTFORMS_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('FERTEM_SMARTFORMS_DESCRIPTION');
    }

    public function DoInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);
        $this->InstallDB();
        $this->InstallFiles();
        $this->InstallEvents();
        return true;
    }

    public function DoUninstall()
    {
        global $APPLICATION;
        $request = Application::getInstance()->getContext()->getRequest();
        if ($request->get('step') === '2') {
            $this->UnInstallFiles();
            $this->UnInstallEvents();
            $this->UnInstallDB(['save_data' => $request->get('save_data') === 'Y']);
            ModuleManager::unRegisterModule($this->MODULE_ID);
            return true;
        }
        $APPLICATION->IncludeAdminFile(Loc::getMessage('FERTEM_SMARTFORMS_UNINSTALL_TITLE'), __DIR__ . '/unstep1.php');
        return true;
    }

    public function InstallDB()
    {
        if (!\Bitrix\Main\Loader::includeModule($this->MODULE_ID)) { return false; }
        foreach ([\Fertem\SmartForms\FormTable::class, \Fertem\SmartForms\SubmissionTable::class] as $entity) {
            $table = $entity::getEntity()->getDBTableName();
            if (!Application::getConnection()->isTableExists($table)) { $entity::getEntity()->createDbTable(); }
        }
        return true;
    }

    public function UnInstallDB(array $params = [])
    {
        if (empty($params['save_data'])) {
            $connection = Application::getConnection();
            foreach (['b_fertem_smartform_submission', 'b_fertem_smartform'] as $table) {
                if ($connection->isTableExists($table)) { $connection->dropTable($table); }
            }
        }
        return true;
    }

    public function InstallFiles()
    {
        CopyDirFiles(__DIR__ . '/admin', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin', true, true);
        CopyDirFiles(__DIR__ . '/components', $_SERVER['DOCUMENT_ROOT'] . '/local/components', true, true);
        CopyDirFiles(__DIR__ . '/js', $_SERVER['DOCUMENT_ROOT'] . '/local/js', true, true);
        return true;
    }

    public function UnInstallFiles()
    {
        DeleteDirFiles(__DIR__ . '/admin', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin');
        DeleteDirFilesEx('/local/components/fertem/smartform');
        DeleteDirFilesEx('/local/js/fertem.smartforms');
        return true;
    }

    public function InstallEvents()
    {
        $eventType = new \CEventType();
        $eventType->Add(['LID' => 'ru', 'EVENT_NAME' => 'FERTEM_SMARTFORM_NEW', 'NAME' => 'Новая заявка из умной формы', 'DESCRIPTION' => "#EMAIL_TO# - получатели\n#FORM_NAME# - форма\n#SUBMISSION_ID# - ID заявки\n#FIELDS# - поля\n#PAGE# - страница"]);
        $sites = \CSite::GetList($by = 'sort', $order = 'asc', ['ACTIVE' => 'Y']);
        $siteIds = [];
        while ($site = $sites->Fetch()) { $siteIds[] = $site['LID']; }
        if ($siteIds) {
            $message = new \CEventMessage();
            $message->Add(['ACTIVE' => 'Y', 'EVENT_NAME' => 'FERTEM_SMARTFORM_NEW', 'LID' => $siteIds, 'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#', 'EMAIL_TO' => '#EMAIL_TO#', 'SUBJECT' => 'Новая заявка ##SUBMISSION_ID#: #FORM_NAME#', 'BODY_TYPE' => 'text', 'MESSAGE' => "Форма: #FORM_NAME#\nЗаявка: ##SUBMISSION_ID#\nСтраница: #PAGE#\n\n#FIELDS#"]);
        }
        return true;
    }

    public function UnInstallEvents()
    {
        $messages = \CEventMessage::GetList($by = 'id', $order = 'asc', ['TYPE_ID' => 'FERTEM_SMARTFORM_NEW']);
        while ($message = $messages->Fetch()) { \CEventMessage::Delete((int)$message['ID']); }
        \CEventType::Delete('FERTEM_SMARTFORM_NEW');
        return true;
    }
}

