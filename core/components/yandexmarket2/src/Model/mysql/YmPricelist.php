<?php
namespace YandexMarket\Model\mysql;

use xPDO\xPDO;

class YmPricelist extends \YandexMarket\Model\YmPricelist
{

    public static $metaMap = array (
        'package' => 'YandexMarket\\Model',
        'version' => '3.0',
        'table' => 'yandexmarket_pricelists',
        'extends' => 'xPDO\\Om\\xPDOSimpleObject',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'name' => NULL,
            'file' => NULL,
            'type' => NULL,
            'class' => 'modResource',
            'created_on' => NULL,
            'edited_on' => NULL,
            'generated_on' => NULL,
            'generate_mode' => NULL,
            'generate_interval' => NULL,
            'need_generate' => 0,
            'properties' => NULL,
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
            'file' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '191',
                'phptype' => 'string',
                'null' => false,
            ),
            'type' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '255',
                'phptype' => 'string',
                'null' => false,
            ),
            'class' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '255',
                'phptype' => 'string',
                'null' => false,
                'default' => 'modResource',
            ),
            'created_on' => 
            array (
                'dbtype' => 'datetime',
                'phptype' => 'datetime',
                'null' => false,
            ),
            'edited_on' => 
            array (
                'dbtype' => 'datetime',
                'phptype' => 'datetime',
                'null' => true,
            ),
            'generated_on' => 
            array (
                'dbtype' => 'datetime',
                'phptype' => 'datetime',
                'null' => true,
            ),
            'generate_mode' => 
            array (
                'dbtype' => 'tinyint',
                'phptype' => 'integer',
                'null' => true,
            ),
            'generate_interval' => 
            array (
                'dbtype' => 'int',
                'phptype' => 'integer',
                'null' => true,
            ),
            'need_generate' => 
            array (
                'dbtype' => 'tinyint',
                'precision' => '1',
                'phptype' => 'boolean',
                'null' => false,
                'default' => 0,
            ),
            'properties' => 
            array (
                'dbtype' => 'text',
                'phptype' => 'json',
                'null' => true,
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
        'indexes' => 
        array (
            'file' => 
            array (
                'alias' => 'file',
                'primary' => false,
                'unique' => true,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'file' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
        ),
        'composites' => 
        array (
            'Categories' => 
            array (
                'class' => 'YandexMarket\\Model\\YmCategory',
                'local' => 'id',
                'foreign' => 'pricelist_id',
                'cardinality' => 'many',
                'owner' => 'local',
            ),
            'Fields' => 
            array (
                'class' => 'YandexMarket\\Model\\YmField',
                'local' => 'id',
                'foreign' => 'pricelist_id',
                'cardinality' => 'many',
                'owner' => 'local',
            ),
            'Conditions' => 
            array (
                'class' => 'YandexMarket\\Model\\YmCondition',
                'local' => 'id',
                'foreign' => 'pricelist_id',
                'cardinality' => 'many',
                'owner' => 'local',
            ),
        ),
    );

}
