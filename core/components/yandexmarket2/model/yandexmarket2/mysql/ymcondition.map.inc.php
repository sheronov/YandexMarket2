<?php
$xpdo_meta_map['YmCondition']= array (
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
      'class' => 'YmPricelist',
      'local' => 'pricelist_id',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
  ),
);
