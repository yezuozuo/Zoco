<?php

namespace Zoco;

/**
 * 暂时先用这么low的方法
 */
require(LIBPATH . '/module/ZDiff/Renderer/Html/SideBySide.php');
require(LIBPATH . '/module/ZDiff/Renderer/Html/Inline.php');
require(LIBPATH . '/module/ZDiff/Renderer/Text/Unified.php');
require(LIBPATH . '/module/ZDiff/Renderer/Text/Context.php');
require(LIBPATH . '/module/ZDiff/SequenceMatcher.php');

class ZDiff {
    /**
     * @var null
     */
    private $old = null;

    /**
     * @var array|null
     */
    private $new = null;

    /**
     * @var null
     */
    private $groupedCodes = null;

    /**
     * @var array
     */
    private $defaultOptions = array(
        'context'          => 3,
        'ignoreNewLines'   => false,
        'ignoreWhitespace' => false,
        'ignoreCase'       => false
    );

    /**
     * @var array
     */
    private $options = array();

    /**
     * @param       $old
     * @param       $new
     * @param array $options
     */
    public function __construct($old, $new, $options = array()) {
        $this->old = $old;
        $this->new = $new;

        if (is_array($options)) {
            $this->options = array_merge($this->defaultOptions, $options);
        } else {
            $this->options = $this->defaultOptions;
        }
    }

    /**
     * @param \ZDiffRendererAbstract $renderer
     * @return mixed
     */
    public function render(\ZDiffRendererAbstract $renderer) {
        $renderer->diff = $this;

        return $renderer->render();
    }

    /**
     * @param int  $start
     * @param null $end
     * @return array
     */
    public function getOld($start = 0, $end = null) {
        if ($start == 0 && $end === null) {
            return $this->old;
        }

        if ($end === null) {
            $length = 1;
        } else {
            $length = $end - $start;
        }

        return array_slice($this->old, $start, $length);

    }

    /**
     * @param int  $start
     * @param null $end
     * @return array
     */
    public function getNew($start = 0, $end = null) {
        if ($start == 0 && $end === null) {
            return $this->new;
        }

        if ($end === null) {
            $length = 1;
        } else {
            $length = $end - $start;
        }

        return array_slice($this->new, $start, $length);
    }

    /**
     * @return array|null
     */
    public function getGroupedOpcodes() {
        if (!is_null($this->groupedCodes)) {
            return $this->groupedCodes;
        }

        $sequenceMatcher    = new \ZDiffSequenceMatcher($this->old, $this->new, null, $this->options);
        $this->groupedCodes = $sequenceMatcher->getGroupedOpcodes($this->options['context']);

        return $this->groupedCodes;
    }

    public function sideBySide() {
        $renderer = new \ZDiffRendererHtmlSideBySide();
        echo $this->render($renderer);
    }

    public function inline() {
        $renderer = new \ZDiffRendererHtmlInline();
        echo $this->render($renderer);
    }

    public function unified() {
        $renderer = new \ZDiffRendererTextUnified();
        echo htmlspecialchars($this->render($renderer));
    }

    public function context() {
        $renderer = new \ZDiffRendererTextContext();
        echo htmlspecialchars($this->render($renderer));
    }
}