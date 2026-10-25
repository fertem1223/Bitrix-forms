<?php
namespace Fertem\SmartForms;

use Bitrix\Main\Entity;

class FormTable extends Entity\DataManager
{
    public static function getTableName() { return 'b_fertem_smartform'; }
    public static function getMap()
    {
        return [
            new Entity\IntegerField('ID', ['primary' => true, 'autocomplete' => true]),
            new Entity\StringField('NAME', ['required' => true, 'validation' => [__CLASS__, 'validateName']]),
            new Entity\StringField('CODE', ['required' => true]),
            new Entity\BooleanField('ACTIVE', ['values' => ['N', 'Y'], 'default_value' => 'Y']),
            new Entity\TextField('FIELDS', ['serialized' => true, 'default_value' => []]),
            new Entity\TextField('SETTINGS', ['serialized' => true, 'default_value' => []]),
            new Entity\DatetimeField('DATE_CREATE', ['default_value' => static function () { return new \Bitrix\Main\Type\DateTime(); }]),
            new Entity\DatetimeField('DATE_UPDATE', ['default_value' => static function () { return new \Bitrix\Main\Type\DateTime(); }]),
        ];
    }
    public static function validateName() { return [new Entity\Validator\Length(null, 255)]; }
}

