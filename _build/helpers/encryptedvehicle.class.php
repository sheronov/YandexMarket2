<?php

class EncryptedVehicle extends xPDOObjectVehicle
{
    public $class = 'EncryptedVehicle';
    const VERSION = '2.0.0';
    const CIPHER  = 'AES-256-CBC';
    const KEY_LENGTH = 40;

    /**
     * @param $transport xPDOTransport
     * @param $object
     * @param  array  $attributes
     */
    public function put(&$transport, &$object, $attributes = [])
    {
        parent::put($transport, $object, $attributes);

        if (defined('PKG_ENCODE_KEY')) {
            $this->payload['object_encrypted'] = $this->encode($this->payload['object'], PKG_ENCODE_KEY);
            unset($this->payload['object']);
            if (isset($this->payload['related_objects'])) {
                $this->payload['related_objects_encrypted'] = $this->encode($this->payload['related_objects'],
                    PKG_ENCODE_KEY);
                unset($this->payload['related_objects']);
            }
            if (isset($this->payload['related_object_attributes'])) {
                $this->payload['related_object_attr_encrypted'] = $this->encode($this->payload['related_object_attributes'],
                    PKG_ENCODE_KEY);
                unset($this->payload['related_object_attributes']);
            }

            $this->payload[xPDOTransport::ABORT_INSTALL_ON_VEHICLE_FAIL] = true;
            $this->payload[xPDOTransport::NATIVE_KEY] = $this->payload[xPDOTransport::NATIVE_KEY] ?? 1;

            $transport->xpdo->log(xPDO::LOG_LEVEL_INFO, 'Vehicle encrypted!');
        }
    }

    /**
     * @param $transport xPDOTransport
     * @param $options
     *
     * @return bool
     */
    public function install(&$transport, $options)
    {
        if (!$this->decodePayloads($transport, 'install')) {
            $transport->xpdo->log(xPDO::LOG_LEVEL_ERROR, 'Package can not be decrypted!');
            return false;
        }

        $transport->xpdo->log(xPDO::LOG_LEVEL_INFO, 'Package decrypted!');

        return parent::install($transport, $options);
    }

    /**
     * @param $transport xPDOTransport
     * @param $options
     *
     * @return bool
     */
    public function uninstall(&$transport, $options)
    {
        if (!$this->decodePayloads($transport, 'uninstall')) {
            $transport->xpdo->log(xPDO::LOG_LEVEL_ERROR, 'Package can not be decrypted!');
            return false;
        }

        $transport->xpdo->log(xPDO::LOG_LEVEL_INFO, 'Vehicle decrypted!');

        return parent::uninstall($transport, $options);
    }

    /**
     * @param  array  $data
     *
     * @return string
     */
    protected function encode($data, $key)
    {
        $ivLen = openssl_cipher_iv_length(EncryptedVehicle::CIPHER);
        $iv = openssl_random_pseudo_bytes($ivLen);
        $cipher_raw = openssl_encrypt(serialize($data), EncryptedVehicle::CIPHER, $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv.$cipher_raw);
    }

    /**
     * @param  string  $string
     *
     * @return string
     */
    protected function decode($string, $key)
    {
        $ivLen = openssl_cipher_iv_length(EncryptedVehicle::CIPHER);
        $encoded = base64_decode($string);
        if (ini_get('mbstring.func_overload')) {
            $strLen = mb_strlen($encoded, '8bit');
            $iv = mb_substr($encoded, 0, $ivLen, '8bit');
            $cipher_raw = mb_substr($encoded, $ivLen, $strLen, '8bit');
        } else {
            $iv = substr($encoded, 0, $ivLen);
            $cipher_raw = substr($encoded, $ivLen);
        }
        return unserialize(openssl_decrypt($cipher_raw, EncryptedVehicle::CIPHER, $key, OPENSSL_RAW_DATA,
            $iv), ['allowed_classes' => true]);
    }

    /**
     * @param $transport xPDOTransport
     * @param  string  $action
     *
     * @return bool
     */
    protected function decodePayloads(&$transport, $action = 'install')
    {
        if (!$key = $this->getDecodeKey($transport, $action)) {
            $transport->xpdo->log(xPDO::LOG_LEVEL_ERROR, "Decode key is not received");
            return false;
        }

        if (isset($this->payload['object_encrypted'])) {
            $this->payload['object'] = $this->decode($this->payload['object_encrypted'], $key);
            unset($this->payload['object_encrypted']);
        }
        if (isset($this->payload['related_objects_encrypted'])) {
            $this->payload['related_objects'] = $this->decode($this->payload['related_objects_encrypted'], $key);
            unset($this->payload['related_objects_encrypted']);
        }
        if (isset($this->payload['related_object_attr_encrypted'])) {
            $this->payload['related_object_attributes'] = $this->decode($this->payload['related_object_attr_encrypted'],
                $key);
            unset($this->payload['related_object_attr_encrypted']);
        }

        return true;
    }

    /**
     * @param $transport xPDOTransport
     * @param $action
     *
     * @return bool|string
     */
    protected function getDecodeKey(&$transport, $action)
    {
        if (defined('YANDEXMARKET2_DECODE_KEY')) {
            return YANDEXMARKET2_DECODE_KEY;
        }
        $key = false;
        $endpoint = 'package/decode/'.$action;

        /** @var modTransportPackage $package */
        $package = $transport->xpdo->getObject('transport.modTransportPackage', [
            'signature' => $transport->signature,
        ]);

        if ($package instanceof modTransportPackage) {
            /** @var modTransportProvider $provider */
            if ($provider = $package->getOne('Provider')) {
                $provider->xpdo->setOption('contentType', 'default');
                $params = [
                    'package'         => $package->package_name,
                    'version'         => $transport->version,
                    'username'        => $provider->username,
                    'api_key'         => $provider->api_key,
                    'vehicle_version' => self::VERSION,
                ];

                /*
                * New method without error log for 2.7.x +
                */
                $options = $this->getBaseArgs($provider);

                /** @var modRest $rest */
                $rest = $transport->xpdo->getService('modRest', 'rest.modRest', '', [
                    'baseUrl'        => rtrim($provider->get('service_url'), '/'),
                    'suppressSuffix' => true,
                    'timeout'        => 10,
                    'connectTimeout' => 10,
                    'format'         => 'xml',
                ]);

                if ($rest) {
                    $level = $transport->xpdo->getLogLevel();
                    $transport->xpdo->setLogLevel(xPDO::LOG_LEVEL_FATAL);
                    $result = $rest->post($endpoint, array_merge($options, $params));
                    if ($result->responseError) {
                        $transport->xpdo->log(xPDO::LOG_LEVEL_ERROR, $result->responseError);
                    } else {
                        $response = $result->process();
                        if (!empty($response['key']) && mb_strlen($response['key']) === self::KEY_LENGTH) {
                            $key = $response['key'];
                        } else {
                            $transport->xpdo->log(xPDO::LOG_LEVEL_ERROR,
                                'Invalid key from '.$provider->get('service_url'));
                        }
                    }
                    $transport->xpdo->setLogLevel($level);
                }
            } else {
                $transport->xpdo->log(xPDO::LOG_LEVEL_ERROR, "Set MODStore as a provider in package details");
            }
        }
        define('YANDEXMARKET2_DECODE_KEY', $key);
        return $key;
    }

    protected function getBaseArgs($provider)
    {
        /** @var modTransportProvider $provider */
        if (!defined('XPDO_PHP_VERSION')) {
            define('XPDO_PHP_VERSION', PHP_VERSION);
        }

        if (!is_array($provider->xpdo->version)) {
            $provider->xpdo->getVersionData();
        }
        return [
            'api_key'            => $provider->get('api_key'),
            'username'           => $provider->get('username'),
            'uuid'               => $provider->xpdo->uuid,
            'database'           => $provider->xpdo->config['dbtype'],
            'revolution_version' => $provider->xpdo->version['code_name'].'-'.$provider->xpdo->version['full_version'],
            'supports'           => $provider->xpdo->version['code_name'].'-'.$provider->xpdo->version['full_version'],
            'http_host'          => $provider->xpdo->getOption('http_host'),
            'php_version'        => XPDO_PHP_VERSION,
            'language'           => $provider->xpdo->getOption('manager_language')
        ];
    }
}