<?php

namespace Zoco\IFace;

/**
 * Interface Queue
 *
 * @package Zoco\IFace
 */
interface Queue {
    /**
     * @param $data
     * @return mixed
     */
    public function push($data);

    /**
     * @return mixed
     */
    public function pop();
}