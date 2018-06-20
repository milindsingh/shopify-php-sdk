<?php

namespace Shopify\Service;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Shopify\ApiInterface;
use Shopify\Object\AbstractObject;
use Shopify\Inflector;

abstract class AbstractService
{
    public $lastResponse;

    private $api;
    private $mapper;
    private $type = "object";

    const TYPE_ARRAY = 'array';
    const TYPE_JSON = 'json';
    const TYPE_OBJECT = 'object';

    const REQUEST_METHOD_GET = 'GET';
    const REQUEST_METHOD_POST = 'POST';
    const REQUEST_METHOD_PUT = 'PUT';
    const REQUEST_METHOD_DELETE = 'DELETE';

    public function setType($type)
    {
        if (in_array($type, array(self::TYPE_ARRAY, self::TYPE_JSON, self::TYPE_OBJECT))) {
            $this->type = $type;
        }
    }

    public function getType()
    {
        return $this->type;
    }

    public static function factory(ApiInterface $api)
    {
        return new static($api);
    }

    public function __construct(ApiInterface $api)
    {
        $this->api = $api;
    }

    public function getApi()
    {
        return $this->api;
    }

    public function request($endpoint, $method = self::REQUEST_METHOD_GET, array $params = array())
    {
        $request = $this->createRequest($endpoint, $method);
        return $this->send($request, $params);
    }

    public function createRequest($endpoint, $method = self::REQUEST_METHOD_GET)
    {
        return new Request($method, $endpoint);
    }

    public function send(Request $request, array $params = array())
    {
        try {
            $handler = $this->getApi()->getHttpHandler();
            $args = array();
            if ($request->getMethod() === 'GET') {
                $args['query'] = $params;
            } else {
                $args['json'] = $params;
            }

            $this->lastResponse = $handler->send($request, $args);
            return json_decode($this->lastResponse->getBody()->getContents(), true);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function createObject($className, $data)
    {
        $type = $this->getType();
        if ($type == self::TYPE_OBJECT) {
            $obj = new $className();
            $obj->setData($data);
            return $obj;
        } elseif ($type == self::TYPE_JSON) {
            return json_encode($data);
        } else {
            return $data;
        }
    }

    public function createCollection($className, $data)
    {
        $type = $this->getType();
        if ($type == self::TYPE_OBJECT) {
            return array_map(
                function ($object) use ($className) {
                    return $this->createObject($className, $object);
                }, $data
            );
        } elseif ($type == self::TYPE_JSON) {
            return json_encode($data);
        } else {
            return $data;
        }
    }
}
