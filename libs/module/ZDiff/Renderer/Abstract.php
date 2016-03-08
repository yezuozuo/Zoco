<?php

/**
 * Class ZDiffRendererAbstract
 * @method render()
 */
abstract class ZDiffRendererAbstract {
    /**
     * @var \Zoco\ZDiff
     */
    public $diff;

    /**
     * @var array
     */
    protected $defaultOptions = array();

    /**
     * @var array
     */
    protected $options = array();

    /**
     * @param array $options
     */
    public function __construct(array $options = array()) {
        $this->setOptions($options);
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options) {
        $this->options = array_merge($this->defaultOptions, $options);
    }
}