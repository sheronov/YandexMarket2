<?php

class EncryptedVehicle extends xPDOObjectVehicle
{
    public $class = 'EncryptedVehicle';
    const version = '2.0.0';
    const cipher  = 'AES-256-CBC';

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

            $transport->xpdo->log(xPDO::LOG_LEVEL_INFO, 'Vehicle encrypted!');
        }
    }

    /**
     * @param $transport xPDOTransport
     *
     * @return bool
     */
    public function store(&$transport)
    {
        $stored = parent::store($transport);

        if (defined('PKG_ENCODE_KEY')) {
            $path = $transport->path.$transport->signature.'/'.$this->payload['class'].'/'.$this->payload['signature'];

            foreach ($this->payload['resolve'] as $k => $v) {
                if ($v['type'] == 'file') {
                    if (!$this->encodeTree($path.'/'.$k)) {
                        return false;
                    }
                }
            }
        }

        return $stored;
    }

    /**
     * @param  string  $path
     *
     * @return bool
     */
    protected function encodeTree($path)
    {
        $Directory = new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);
        $Iterator = new RecursiveIteratorIterator($Directory, RecursiveIteratorIterator::LEAVES_ONLY);

        foreach ($Iterator as $filename => $object) {
            $contents = file_get_contents($filename);
            $contents = $this->encode($contents, PKG_ENCODE_KEY);
            if (!file_put_contents($filename, $contents)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  string  $path
     *
     * @return bool
     */
    public static function decodeTree($path)
    {
        $Directory = new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);
        $Iterator = new RecursiveIteratorIterator($Directory, RecursiveIteratorIterator::LEAVES_ONLY);

        foreach ($Iterator as $filename => $object) {
            $contents = file_get_contents($filename);
            $contents = EncryptedVehicle::decode($contents);
            if (!file_put_contents($filename, $contents)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $transport xPDOTransport
     * @param $options
     *
     * @return bool
     */
    public function install(&$transport, $options)
    {
        if ($this->decodePayloads($transport, 'install')) {
            $transport->xpdo->log(xPDO::LOG_LEVEL_INFO, 'Vehicle decrypted!');
        } else {
            $transport->xpdo->log(xPDO::LOG_LEVEL_ERROR, 'Vehicle not decrypted!');
            return false;
        }
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
        if ($this->decodePayloads($transport, 'uninstall')) {
            $transport->xpdo->log(xPDO::LOG_LEVEL_INFO, 'Vehicle decrypted!');
        } else {
            $transport->xpdo->log(xPDO::LOG_LEVEL_ERROR, 'Vehicle not decrypted!');
            return false;
        }
        return parent::uninstall($transport, $options);
    }

    /**
     * @param  array  $data
     *
     * @return string
     */
    protected function encode($data, $key)
    {
        $ivLen = openssl_cipher_iv_length(EncryptedVehicle::cipher);
        $iv = openssl_random_pseudo_bytes($ivLen);
        $cipher_raw = openssl_encrypt(serialize($data), EncryptedVehicle::cipher, $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv.$cipher_raw);
    }

    /**
     * @param  string  $string
     *
     * @return string
     */
    protected static function decode($string)
    {
        $ivLen = openssl_cipher_iv_length(EncryptedVehicle::cipher);
        $encoded = base64_decode($string);
        if (ini_get('mbstring.func_overload')) {
            $strLen = mb_strlen($encoded, '8bit');
            $iv = mb_substr($encoded, 0, $ivLen, '8bit');
            $cipher_raw = mb_substr($encoded, $ivLen, $strLen, '8bit');
        } else {
            $iv = substr($encoded, 0, $ivLen);
            $cipher_raw = substr($encoded, $ivLen);
        }
        return unserialize(openssl_decrypt($cipher_raw, EncryptedVehicle::cipher, PKG_ENCODE_KEY, OPENSSL_RAW_DATA,
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
        if (isset($this->payload['object_encrypted']) || isset($this->payload['related_objects_encrypted'])) {
            if (!defined('PKG_ENCODE_KEY')) {
                if (!$key = $this->getDecodeKey($transport, $action)) {
                    $transport->xpdo->log(xPDO::LOG_LEVEL_ERROR, "Decode key not received");
                    return false;
                }
                define('PKG_ENCODE_KEY', $key);
            }

            if (isset($this->payload['object_encrypted'])) {
                $this->payload['object'] = $this->decode($this->payload['object_encrypted']);
                unset($this->payload['object_encrypted']);
            }
            if (isset($this->payload['related_objects_encrypted'])) {
                $this->payload['related_objects'] = $this->decode($this->payload['related_objects_encrypted']);
                unset($this->payload['related_objects_encrypted']);
            }
            if (isset($this->payload['related_object_attr_encrypted'])) {
                $this->payload['related_object_attributes'] = $this->decode($this->payload['related_object_attr_encrypted']);
                unset($this->payload['related_object_attr_encrypted']);
            }
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
                    'vehicle_version' => self::version,
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
                        if (!empty($response['key'])) {
                            $key = $response['key'];
                        } else {
                            $transport->xpdo->log(xPDO::LOG_LEVEL_ERROR,
                                'Empty key from '.$provider->get('service_url'));
                        }
                    }
                    $transport->xpdo->setLogLevel($level);
                }
            } else {
                $transport->xpdo->log(xPDO::LOG_LEVEL_ERROR, "Set MODStore as a provider in package details");
            }
        }
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