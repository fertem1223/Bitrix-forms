<?php
namespace Fertem\SmartForms;

use Bitrix\Main\Entity;

class SubmissionTable extends Entity\DataManager
{
    public static function getTableName() { return 'b_fertem_smartform_submission'; }
    public static function getMap()
    {
        return [
            new Entity\IntegerField('ID', ['primary' => true, 'autocomplete' => true]),
            new Entity\IntegerField('FORM_ID', ['required' => true]),
            new Entity\TextField('DATA', ['serialized' => true]),
            new Entity\TextField('META', ['serialized' => true]),
            new Entity\StringField('STATUS', ['default_value' => 'NEW']),
            new Entity\StringField('IP_HASH', ['size' => 64]),
            new Entity\DatetimeField('DATE_CREATE', ['default_value' => static function () { return new \Bitrix\Main\Type\DateTime(); }]),
        ];
    }
}

