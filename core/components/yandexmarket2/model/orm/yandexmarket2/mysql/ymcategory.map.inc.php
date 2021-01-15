<?php
$xpdo_meta_map['ymCategory']= array (
  'package' => 'yandexmarket2',
  'version' => '1.1',
  'table' => 'yandexmarket_categories',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' => 
  array (
    'engine' => 'InnoDB',
  ),
  'fields' => 
  array (
    'category_id' => NULL,
    'pricelist_id' => NULL,
  ),
  'fieldMeta' => 
  array (
    'category_id' => 
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
  ),
  'indexes' => 
  array (
    'category_pricelist' => 
    array (
      'alias' => 'category_pricelist',
      'primary' => false,
      'unique' => true,
      'type' => 'BTREE',
      'columns' => 
      array (
        'category_id' => 
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
    'Category' => 
    array (
      'class' => 'modResource',
      'local' => 'category_id',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
    'Pricelist' => 
    array (
      'class' => 'ymPricelist',
      'local' => 'pricelist_id',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
  ),
);
