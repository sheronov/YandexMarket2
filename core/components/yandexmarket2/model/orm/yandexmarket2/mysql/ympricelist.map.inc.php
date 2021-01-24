<?php
$xpdo_meta_map['ymPricelist']= array (
  'package' => 'yandexmarket2',
  'version' => '1.1',
  'table' => 'yandexmarket_pricelists',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' => 
  array (
    'engine' => 'InnoDB',
  ),
  'fields' => 
  array (
    'name' => NULL,
    'file' => NULL,
    'type' => NULL,
    'created_on' => NULL,
    'edited_on' => NULL,
    'generated_on' => NULL,
    'generate_mode' => NULL,
    'generate_interval' => NULL,
    'need_generate' => 0,
    'where' => NULL,
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
      'precision' => '255',
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
    'where' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'json',
      'null' => true,
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
      'class' => 'ymCategory',
      'local' => 'id',
      'foreign' => 'pricelist_id',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
    'Fields' => 
    array (
      'class' => 'ymField',
      'local' => 'id',
      'foreign' => 'pricelist_id',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
  ),
);
