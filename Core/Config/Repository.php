<?php
namespace Cache\Core\Config;

use ArrayAccess;
use Cache\Core\Contracts\Config\Repository as RepositoryContracts;

/**
 * load config file
 * User: Liu xiaoquan
 * Date: 2017/7/20
 * Time: 18:42
 */
class Repository implements ArrayAccess,RepositoryContracts
{


    protected $items;//config items

    public function __construct(array $items=array()) {
        $this->items = $items;
    }

    public function boot(){
        echo __METHOD__;
    }

    public function has($key){

    }

    public function set($key, $value)
    {
        $keys = is_array($key) ? $key : array($key => $value);

        foreach ($keys as $key => $value) {
            self::parse($this->items, $key, $value);
        }
    }

    public function get($key)
    {
        if (empty($key)) {
            return is_null($key) ? null : '';
        }

        $res = array_reduce(explode('.', $key), function($res, $v) {
            return $res[$v];
        }, $this->items);

        return $res;
    }

    public function all()
    {
        return $this->items;
    }

    public function offsetExists($offset){
        return $this->has($offset);
    }

    public function offsetGet($offset){
        return $this->get($offset);
    }

    public function offsetSet($offset, $value){
        $this->set($offset, $value);
    }

    public function offsetUnset($offset){
        $this->set($offset, null);
    }

    /**
     * Set an array item to a given value using "dot" notation.
     *
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $value
     * @return array
     */
    protected static function parse(&$array, $key, $value)
    {
        if (is_null($key)) {
            return $array = $value;
        }

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (! isset($array[$key]) || ! is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }
}