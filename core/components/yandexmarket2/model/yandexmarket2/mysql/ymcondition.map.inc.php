<?php
$xpdo_meta_map['ymCondition']= array (
  'package' => 'yandexmarket2',
  'version' => '1.1',
  'table' => 'yandexmarket_conditions',
  'extends' => 'xPDOSimpleObject',
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
  ),
  'aggregates' => 
  array (
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
