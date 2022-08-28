<?php
namespace YandexMarket\Model\mysql;

use xPDO\xPDO;

class YmField extends \YandexMarket\Model\YmField
{

    public static $metaMap = array (
        'package' => 'YandexMarket\\Model',
        'version' => '3.0',
        'table' => 'yandexmarket_fields',
        'extends' => 'xPDO\\Om\\xPDOSimpleObject',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'name' => NULL,
            'parent' => NULL,
            'type' => NULL,
            'pricelist_id' => NULL,
            'value' => NULL,
            'handler' => NULL,
            'properties' => NULL,
            'rank' => 0,
            'created_on' => NULL,
            'active' => 1,
        ),
        'fieldMeta' => 
        array (
            'name' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '255',
                'phptype' => 'string',
                'null' => false,
            ),
            'parent' => 
            array (
                'dbtype' => 'int',
                'phptype' => 'integer',
                'null' => true,
            ),
            'type' => 
            array (
                'dbtype' => 'int',
                'phptype' => 'integer',
                'null' => false,
            ),
            'pricelist_id' => 
            array (
                'dbtype' => 'int',
                'null' => false,
                'attributes' => 'unsigned',
                'phptype' => 'integer',
            ),
            'value' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '255',
                'phptype' => 'string',
                'null' => true,
            ),
            'handler' => 
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
            'rank' => 
            array (
                'dbtype' => 'int',
                'phptype' => 'integer',
                'null' => false,
                'default' => 0,
            ),
            'created_on' => 
            array (
                'dbtype' => 'datetime',
                'phptype' => 'datetime',
                'null' => false,
            ),
            'active' => 
            array (
                'dbtype' => 'tinyint',
                'precision' => '1',
                'phptype' => 'boolean',
                'null' => false,
                'default' => 1,
            ),
        ),
        'composites' => 
        array (
            'Attributes' => 
            array (
                'class' => 'YandexMarket\\Model\\YmFieldAttribute',
                'local' => 'id',
                'foreign' => 'field_id',
                'cardinality' => 'many',
                'owner' => 'local',
            ),
            'Children' => 
            array (
                'class' => 'YandexMarket\\Model\\YmField',
                'local' => 'id',
                'foreign' => 'parent',
                'cardinality' => 'many',
                'owner' => 'local',
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
            'Parent' => 
            array (
                'class' => 'YandexMarket\\Model\\YmField',
                'local' => 'parent',
                'foreign' => 'id',
                'cardinality' => 'one',
                'owner' => 'foreign',
            ),
        ),
    );

}
