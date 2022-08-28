<?php
namespace YandexMarket\Model\mysql;

use xPDO\xPDO;

class YmCondition extends \YandexMarket\Model\YmCondition
{

    public static $metaMap = array (
        'package' => 'YandexMarket\\Model',
        'version' => '3.0',
        'table' => 'yandexmarket_conditions',
        'extends' => 'xPDO\\Om\\xPDOSimpleObject',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'pricelist_id' => NULL,
            'column' => NULL,
            'operator' => NULL,
            'value' => NULL,
            'properties' => NULL,
            'group' => 'offer',
        ),
        'fieldMeta' => 
        array (
            'pricelist_id' => 
            array (
                'dbtype' => 'int',
                'null' => false,
                'attributes' => 'unsigned',
                'phptype' => 'integer',
            ),
            'column' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '255',
                'phptype' => 'string',
                'null' => false,
            ),
            'operator' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '255',
                'phptype' => 'string',
                'null' => false,
            ),
            'value' => 
            array (
                'dbtype' => 'text',
                'phptype' => 'string',
                'null' => true,
            ),
            'properties' => 
            array (
                'dbtype' => 'text',
                'phptype' => 'json',
                'null' => true,
            ),
            'group' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '255',
                'phptype' => 'string',
                'default' => 'offer',
            ),
        ),
        'aggregates' => 
        array (
            'Pricelist' => 
            array (
                'class' => 'YandexMarket\\Model\\YmPricelist',
                'local' => 'pricelist_id',
                'foreign' => 'id',
                'cardinality' => 'one',
                'owner' => 'foreign',
            ),
        ),
    );

}
