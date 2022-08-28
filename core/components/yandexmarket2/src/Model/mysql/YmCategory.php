<?php
namespace YandexMarket\Model\mysql;

use xPDO\xPDO;

class YmCategory extends \YandexMarket\Model\YmCategory
{

    public static $metaMap = array (
        'package' => 'YandexMarket\\Model',
        'version' => '3.0',
        'table' => 'yandexmarket_categories',
        'extends' => 'xPDO\\Om\\xPDOSimpleObject',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'resource_id' => NULL,
            'pricelist_id' => NULL,
            'name' => NULL,
            'properties' => NULL,
        ),
        'fieldMeta' => 
        array (
            'resource_id' => 
            array (
                'dbtype' => 'int',
                'null' => false,
                'attributes' => 'unsigned',
                'phptype' => 'integer',
            ),
            'pricelist_id' => 
            array (
                'dbtype' => 'int',
                'null' => false,
                'attributes' => 'unsigned',
                'phptype' => 'integer',
            ),
            'name' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '255',
                'phptype' => 'string',
                'null' => true,
            ),
            'properties' => 
            array (
                'dbtype' => 'text',
                'phptype' => 'json',
                'null' => true,
            ),
        ),
        'indexes' => 
        array (
            'resource_pricelist' => 
            array (
                'alias' => 'resource_pricelist',
                'primary' => false,
                'unique' => true,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'resource_id' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                    'pricelist_id' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
        ),
        'aggregates' => 
        array (
            'Resource' => 
            array (
                'class' => 'MODX\\Revolution\\modResource',
                'local' => 'resource_id',
                'foreign' => 'id',
                'cardinality' => 'one',
                'owner' => 'foreign',
            ),
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
