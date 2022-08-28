<?php

/** @var xPDOTransport $transport */
/** @var array $options */

if (!class_exists('xPDOObjectVehicle')) {
    $transport->xpdo->loadClass('transport.xPDOObjectVehicle', XPDO_CORE_PATH, true, true);
}

if (!class_exists('EncryptedVehicle')) {
    if (!$transport->xpdo->loadClass('EncryptedVehicle', MODX_CORE_PATH.'components/yandexmarket2/', true, true)) {
        $transport->xpdo->log($transport->xpdo::LOG_LEVEL_ERROR, 'Could not load class EncryptedVehicle');
        return false;
    }
}

    // $classIndex = 0; //файл самым первым
    // $filePath = $transport->path.$transport->signature.'/'.$transport->vehicles[$classIndex]['filename'];
    // if (file_exists($filePath)) {
    //     $payload = include($filePath);
    //     $path = $transport->path.$transport->signature.'/'.$payload['class'].'/'.$payload['signature'].'/';
    //     if (!$transport->xpdo->loadClass('EncryptedVehicle', $path, true, true)) {
    //         $transport->xpdo->log(modX::LOG_LEVEL_ERROR, 'Could not load class EncryptedVehicle');
    //     }
    // }

return true;
