<?php

/** @var xPDOTransport $transport */

/** @var array $options */
if (!class_exists('EncryptedVehicle')) {
    $classIndex = 0;

    if (!class_exists('xPDOObjectVehicle')) {
        $transport->xpdo->loadClass('transport.xPDOObjectVehicle', XPDO_CORE_PATH, true, true);
    }

    $filePath = $transport->path.$transport->signature.'/'.$transport->vehicles[$classIndex]['filename'];
    if (file_exists($filePath)) {
        $payload = include($filePath);
        $path = $transport->path.$transport->signature.'/'.$payload['class'].'/'.$payload['signature'].'/';
        if (!$transport->xpdo->loadClass('EncryptedVehicle', $path, true, true)) {
            $transport->xpdo->log(modX::LOG_LEVEL_ERROR, 'Could not load class EncryptedVehicle');
        }
    }
}

return true;