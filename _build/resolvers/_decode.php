<?php
/** @var array $options */

/** @var xPDOTransport $transport */
if ($transport->xpdo) {
    /** @var modX $modx */
    $modx =& $transport->xpdo;
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            if ($options['vehicle_class'] === 'EncryptedVehicle') {
                foreach ($options['resolve'] as $idx => $values) {
                    if ($values['type'] === 'file') {
                        $fileMeta = $transport->xpdo->fromJSON($values['body'], true);
                        $fileTarget = eval($fileMeta['target']);
                        $fileTargetPath = $fileTarget.$fileMeta['name'];
                        // EncryptedVehicle::decodeTree($fileTargetPath);
                        $modx->log(xPDO::LOG_LEVEL_DEBUG, "Contents decoded: $fileTargetPath");
                    }
                }
            }

            break;

        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }
}

return true;