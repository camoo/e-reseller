<?php

namespace App\Lib\Utils;

use CAMOO\Http\ServerRequest;
use CAMOO\Cache\Cache;
use IteratorAggregate;
use ArrayIterator;
use Traversable;
use ArrayAccess;
use Countable;
use ArrayObject;

/**
 * Class Basket
 * @author CamooSarl
 */
class Basket implements IteratorAggregate, ArrayAccess, Countable
{
    /** @var ServerRequest $withRequest */
    private static $withRequest;

    /** @var array $data */
    private $data = [];

    /** @var null|Basket $created */
    private static $created = null;

    /** @var string $basketKey */
    private static $basketKey = null;

    /** @int $count */
    private $count = 0;

    /** @var float $total_price */
    private $total_price = 0.00;

    public function count()
    {
        return $this->count;
    }

    /**
     * Gets Basket total price
     */
    public function getTotalPrice() : float
    {
        return $this->total_price;
    }

    public static function create(ServerRequest $withRequest)
    {
        self::$withRequest = $withRequest;
        if (!empty(self::$withRequest->getSession()->check('Basket'))) {
            $sObject = Cache::read(self::$withRequest->getSession()->read('Basket'), '_camoo_hosting_conf');
            self::$created = !($sObject instanceof self) ? new self : $sObject;
        } elseif (self::$created === null) {
            self::$created = new self;
        }
        return self::$created;
    }

    /**
     * Deletes entire Basket
     */
    public function delete() : void
    {
        $request = $this->getRequest();
        if (!empty($request->getSession()->check('Basket'))) {
            // DELETE PREVIOUS CACHE
            Cache::delete($request->getSession()->read('Basket'), '_camoo_hosting_conf');
        }
        $this->count = 0;
    }

    /**
     * Saves current Basket
     */
    public function save() : ?bool
    {
        $request = $this->getRequest();
        if (!empty($request->getSession()->check('Basket'))) {
            // DELETE PREVIOUS CACHE
            Cache::delete($request->getSession()->read('Basket'), '_camoo_hosting_conf');
        }
        $Object = $this;
        $iUserId = (int) $request->getSession()->read('Auth.User.id');
        self::$basketKey = !empty($iUserId)? sprintf('Basket_%d', $iUserId) : uniqid('Basket', false);
        $request->getSession()->write('Basket', self::$basketKey);
        return Cache::write(self::$basketKey, $Object, '_camoo_hosting_conf');
    }

    /**
     * is not allowed to call from outside to prevent from creating multiple instances,
     * to use the singleton, you have to obtain the instance from AdapterFactory::create() instead
     */
    private function __construct()
    {
    }

    /**
     * prevent the instance from being cloned (which would create a second instance of it)
     */
    private function __clone()
    {
    }

    /**
     * Keeps Basket relevant properties during serialization
     */
    public function __sleep()
    {
        return ['count', 'data', 'total_price'];
    }
     
    /**
     * @return ServerRequest
     */
    private function getRequest() : ServerRequest
    {
        return self::$withRequest;
    }

    /**
     * @return Traversable
     */
    public function getIterator() : Traversable
    {
        return new ArrayIterator(new ArrayObject($this->data));
    }

    public function __get(string $key)
    {
        //$caller = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT)[1];
        return $this->offsetGet($key);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key) : bool
    {
        return $this->offsetExists($key);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->offsetGet($key);
    }

    /**
     * Adds an Item into Basket
     *
     * @param string $key
     * @param string $value
     * @return void
     */
    public function addItem(string $key, $value) : void
    {
        $this->offsetSet($key, $value);
    }

    /**
     * Removes an Item from Basket
     *
     * @return void
     */
    public function removeItem(string $key) : void
    {
        $this->offsetUnset($key);
    }

    public function offsetExists($key)
    {
        return isset($this->data[$key]);
    }

    public function offsetGet($key)
    {
        if ($this->offsetExists($key)):
            return $this->data[$key]; else:
        return null;
        endif;
    }

    public function offsetSet($key, $value)
    {
        if (!empty($key)) {
            if (!$this->has($key)) {
                ++$this->count;
                if (array_key_exists('price', $value)) {
                    $this->total_price += (float) $value['price'];
                }
            }
            $this->data[$key] = $value;
        } else {
            $this->data[] = $value;
            ++$this->count;
            if (array_key_exists('price', $value)) {
                $this->total_price += (float) $value['price'];
            }
        }
        $this->save();
    }

    public function offsetUnset($key)
    {
        if ($this->has($key)) {
            $value = $this->data[$key];
            unset($this->data[$key]);
            --$this->count;
            if (array_key_exists('price', $value)) {
                $this->total_price -= (float) $value['price'];
            }
        }
    }
}
