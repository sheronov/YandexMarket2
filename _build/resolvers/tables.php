<?php
/** @var xPDO\Transport\xPDOTransport|xPDOTransport $transport */
/** @var array $options */
/** @var  MODX\Revolution\modX|modX $modx */

if (!defined('MODX3')) {
    define('MODX3', class_exists('MODX\Revolution\modX'));
}

if ($transport->xpdo) {
    /** @var MODX\Revolution\modX|modX $modx */
    $modx =& $transport->xpdo;

    switch ($options[MODX3 ? \xPDO\Transport\xPDOTransport::PACKAGE_ACTION : xPDOTransport::PACKAGE_ACTION]) {
        case MODX3 ? \xPDO\Transport\xPDOTransport::ACTION_INSTALL : xPDOTransport::ACTION_INSTALL:
        case MODX3 ? \xPDO\Transport\xPDOTransport::ACTION_UPGRADE : xPDOTransport::ACTION_UPGRADE:
            if (MODX3) {
                $modx->addPackage('YandexMarket\Model', MODX_CORE_PATH.'components/yandexmarket2/src/', null, 'YandexMarket\\');
            } else {
                $modx->addPackage('yandexmarket2', MODX_CORE_PATH.'components/yandexmarket2/model/');
            }
            $manager = $modx->getManager();
            $objects = [];
            $schemaFile = MODX_CORE_PATH.'components/yandexmarket2/schema/yandexmarket2.mysql.schema.xml';

            // $manifestIndex = 2; //кладём его поле двух файлов encryption и namespace
            // $schemaFile = null;
            // $tmpPackage = $transport->path.$transport->signature.'/';
            // $filePath = $tmpPackage.$transport->vehicles[$manifestIndex]['filename'];
            // if (file_exists($filePath)) {
            //     $payload = include($filePath);
            //     $schemaFile = $tmpPackage.$payload['class'].'/'.$payload['signature'].'/yandexmarket2.mysql.schema.xml';
            // }

            if (is_file($schemaFile)) {
                $schema = new SimpleXMLElement($schemaFile, 0, true);
                if (isset($schema->object)) {
                    foreach ($schema->object as $obj) {
                        $objects[] = (string)$obj['class'];
                    }
                }
                unset($schema);
            }
            foreach ($objects as $class) {
                if (MODX3) {
                    $class = 'YandexMarket\\Model\\'.$class;
                }
                $table = $modx->getTableName($class);
                $sql = "SHOW TABLES LIKE '".trim($table, '`')."'";
                $stmt = $modx->prepare($sql);
                $newTable = true;
                if ($stmt->execute() && $stmt->fetchAll()) {
                    $newTable = false;
                }
                // If the table is just created
                if ($newTable) {
                    $manager->createObjectContainer($class);
                } else {
                    // If the table exists
                    // 1. Operate with tables
                    $tableFields = [];
                    $c = $modx->prepare("SHOW COLUMNS IN {$modx->getTableName($class)}");
                    $c->execute();
                    while ($cl = $c->fetch(PDO::FETCH_ASSOC)) {
                        $tableFields[$cl['Field']] = $cl['Field'];
                    }
                    foreach ($modx->getFields($class) as $field => $v) {
                        if (in_array($field, $tableFields, true)) {
                            unset($tableFields[$field]);
                            $manager->alterField($class, $field);
                        } else {
                            $manager->addField($class, $field);
                        }
                    }
                    foreach ($tableFields as $field) {
                        $manager->removeField($class, $field);
                    }
                    // 2. Operate with indexes
                    $indexes = [];
                    $c = $modx->prepare("SHOW INDEX FROM {$modx->getTableName($class)}");
                    $c->execute();
                    while ($row = $c->fetch(PDO::FETCH_ASSOC)) {
                        $name = $row['Key_name'];
                        if (!isset($indexes[$name])) {
                            $indexes[$name] = [$row['Column_name']];
                        } else {
                            $indexes[$name][] = $row['Column_name'];
                        }
                    }
                    foreach ($indexes as $name => $values) {
                        sort($values);
                        $indexes[$name] = implode(':', $values);
                    }

                    $map = $modx->getIndexMeta($class);
                    // Remove old indexes
                    foreach ($indexes as $key => $index) {
                        if (!isset($map[$key]) && $manager->removeIndex($class, $key)) {
                            $modx->log(MODX3 ? \xPDO\xPDO::LOG_LEVEL_INFO : xPDO::LOG_LEVEL_INFO, "Removed index \"{$key}\" of the table \"{$class}\"");
                        }
                    }
                    // Add or alter existing
                    foreach ($map as $key => $index) {
                        ksort($index['columns']);
                        $index = implode(':', array_keys($index['columns']));
                        if (!isset($indexes[$key])) {
                            if ($manager->addIndex($class, $key)) {
                                $modx->log(MODX3 ? \xPDO\xPDO::LOG_LEVEL_INFO : xPDO::LOG_LEVEL_INFO, "Added index \"{$key}\" in the table \"{$class}\"");
                            }
                        } elseif ($index != $indexes[$key]) {
                            if ($manager->removeIndex($class, $key) && $manager->addIndex($class, $key)) {
                                $modx->log(MODX3 ? \xPDO\xPDO::LOG_LEVEL_INFO : xPDO::LOG_LEVEL_INFO,
                                    "Updated index \"{$key}\" of the table \"{$class}\""
                                );
                            }
                        }
                    }

                    // 3. Operate with constraints (many shared hosting doesn't give grants for references)
                    // if ($manager instanceof xPDOManager_mysql && $connect = $manager->xpdo->getConnection([
                    //         xPDO::OPT_CONN_MUTABLE => true
                    //     ])) {
                    //     $modx->log(xPDO::LOG_LEVEL_INFO, 'config = '.print_r($connect->config, 1));
                    //
                    //     // add here get constraints from mysql
                    //     $constraints = array_filter($modx->getAggregates($class), static function (array $item) {
                    //         return ($item['cardinality'] ?? null) === 'one';
                    //     });
                    //     $modx->log(xPDO::LOG_LEVEL_INFO, print_r($constraints, 1));
                    //
                    //     if ($className = $manager->xpdo->loadClass($class)) {
                    //         $currentTable = $manager->xpdo->getTableName($className);
                    //         $grantsSql = "GRANT REFERENCES ON {$connect->config['dbname']}.{$currentTable}
                    //                     TO '{$connect->config['username']}'@'{$connect->config['host']}';";
                    //         $modx->log(xPDO::LOG_LEVEL_INFO, 'grantsSql = '.$grantsSql);
                    //         foreach ($constraints as $key => $constraint) {
                    //             if ($foreignClassKey = $manager->xpdo->loadClass($constraint['class'])) {
                    //                 $modx->log(xPDO::LOG_LEVEL_INFO, $className.' => '.$foreignClassKey);
                    //                 $foreignKey = mb_strtolower(implode('_',
                    //                     [$class, $constraint['local'], $key, $constraint['foreign']]));
                    //                 $sql = "ALTER TABLE {$manager->xpdo->getTableName($className)}
                    //                         ADD FOREIGN KEY ({$constraint['local']})
                    //                         REFERENCES {$manager->xpdo->getTableName($foreignClassKey)} ({$constraint['foreign']})";
                    //                 switch (mb_strtolower($constraint['on-delete'] ?? '')) {
                    //                     case 'cascade':
                    //                         $sql .= " ON DELETE CASCADE";
                    //                         break;
                    //                     case 'null':
                    //                     case 'set null':
                    //                         $sql .= " ON DELETE SET NULL";
                    //                         break;
                    //                 }
                    //                 $modx->log(xPDO::LOG_LEVEL_INFO, 'sql = '.$sql);
                    //             }
                    //         }
                    //     }
                    // } else {
                    //     $modx->log(xPDO::LOG_LEVEL_ERROR, 'Could not create constraints keys in table');
                    // }
                }
            }

            break;

        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }
}

return true;
