<?php

namespace Zoco;

/**
 * Class ArrayObject
 *
 * @package Zoco
 */
class ArrayObject implements \ArrayAccess, \Serializable, \Countable, \Iterator {
    /**
     * @var array
     */
    protected $array;

    /**
     * @var int
     */
    protected $index = 0;

    /**
     * @param $array
     */
    public function __construct($array) {
        $this->array = $array;
    }

    /**
     * @return mixed
     */
    public function current() {
        return current($this->array);
    }

    /**
     * @return mixed
     */
    public function key() {
        return key($this->array);
    }

    /**
     * @return bool
     */
    public function valid() {
        return count($this->array) >= $this->index;
    }

    /**
     * @return mixed
     */
    public function rewind() {
        $this->index = 0;

        return reset($this->array);
    }

    /**
     * @return mixed
     */
    public function next() {
        $this->index++;

        return next($this->array);
    }

    /**
     * @return string
     */
    public function serialize() {
        return serialize($this->array);
    }

    /**
     * @param string $str
     */
    public function unserialize($str) {
        $this->array = unserialize($str);
    }

    /**
     * @param mixed $k
     * @return mixed
     */
    public function offsetGet($k) {
        return $this->array[$k];
    }

    /**
     * @param mixed $k
     * @param mixed $v
     */
    public function offsetSet($k, $v) {
        $this->array[$k] = $v;
    }

    /**
     * @param mixed $k
     */
    public function offsetUnset($k) {
        unset($this->array[$k]);
    }

    /**
     * @param mixed $k
     * @return bool
     */
    public function offsetExists($k) {
        return isset($this->array[$k]);
    }

    /**
     * @param $val
     * @return bool
     */
    public function contains($val) {
        return in_array($val, $this->array);
    }

    /**
     * @param $str
     * @return String
     */
    public function join($str) {
        return new String(implode($str, $this->array));
    }

    /**
     * @param $offset
     * @param $val
     * @return array|bool
     */
    public function insert($offset, $val) {
        if ($offset > count($this->array)) {
            return false;
        }

        return array_splice($this->array, $offset, 0, $val);
    }

    /**
     * @param $find
     * @return mixed
     */
    public function search($find) {
        return array_search($find, $this->array);
    }

    /**
     * @return int
     */
    public function count() {
        return count($this->array);
    }

    /**
     * @param $val
     * @return int
     */
    public function append($val) {
        return array_push($this->array, $val);
    }

    /**
     * @param $val
     * @return int
     */
    public function prepend($val) {
        return array_unshift($this->array, $val);
    }

    /**
     * @param      $offset
     * @param null $length
     * @return ArrayObject
     */
    public function slice($offset, $length = null) {
        return new ArrayObject(array_slice($this->array, $offset, $length));
    }

    /**
     * @return mixed
     */
    public function rand() {
        return $this->array[array_rand($this->array, 1)];
    }

    /**
     * @return array
     */
    public function toArray() {
        return $this->array;
    }
}