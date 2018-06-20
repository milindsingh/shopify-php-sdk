<?php

namespace Shopify\Storage;

use Shopify\Exception\ShopifySdkException;

class SessionStorage extends \Magento\Framework\Session\Storage implements PersistentStorageInterface
{
    protected $prefix = 'SHPFY_';

    public function __construct($namespace = 'default', array $data = [])
    {
        $namespace = $this->prefix;
        parent::__construct($namespace, $data);
    }

    public function get($key)
    {
        $value = $this->getData($this->prefix . $key);
        if (isset($value)) {
            return $value;
        }
        return false;
    }

    public function set($key, $value)
    {
        $this->setData($this->prefix . $key, $value);
    }
}
