<?php

namespace Zoco\IFace;

/**
 * Interface Log
 *
 * @package Zoco\IFace
 */
interface Log {
    /**
     * 写入日志
     *
     * @param     $msg
     * @param int $type
     * @return mixed
     */
    public function put($msg, $type = \Zoco\Log::INFO);
}