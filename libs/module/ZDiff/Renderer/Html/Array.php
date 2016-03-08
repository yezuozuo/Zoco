<?php

require_once __DIR__ . '/../Abstract.php';

/**
 * Class Diff_Renderer_Html_Array
 */
class ZDiffRendererHtmlArray extends ZDiffRendererAbstract {

    /**
     * @var array
     */
    protected $defaultOptions = array(
        'tabSize' => 4
    );

    /**
     * @return array
     */
    public function render() {

        $old = $this->diff->getOld();
        $new = $this->diff->getNew();

        $changes = array();
        $opCodes = $this->diff->getGroupedOpcodes();
        foreach ($opCodes as $group) {
            $blocks    = array();
            $lastTag   = null;
            $lastBlock = 0;
            foreach ($group as $code) {
                list($tag, $i1, $i2, $j1, $j2) = $code;

                if ($tag == 'replace' && $i2 - $i1 == $j2 - $j1) {
                    for ($i = 0; $i < ($i2 - $i1); ++$i) {
                        $fromLine = $old[$i1 + $i];
                        $toLine   = $new[$j1 + $i];

                        list($start, $end) = $this->getChangeExtent($fromLine, $toLine);
                        if ($start != 0 || $end != 0) {
                            $last          = $end + strlen($fromLine);
                            $fromLine      = substr_replace($fromLine, "\0", $start, 0);
                            $fromLine      = substr_replace($fromLine, "\1", $last + 1, 0);
                            $last          = $end + strlen($toLine);
                            $toLine        = substr_replace($toLine, "\0", $start, 0);
                            $toLine        = substr_replace($toLine, "\1", $last + 1, 0);
                            $old[$i1 + $i] = $fromLine;
                            $new[$j1 + $i] = $toLine;
                        }
                    }
                }

                if ($tag != $lastTag) {
                    $blocks[]  = array(
                        'tag'     => $tag,
                        'base'    => array(
                            'offset' => $i1,
                            'lines'  => array()
                        ),
                        'changed' => array(
                            'offset' => $j1,
                            'lines'  => array()
                        )
                    );
                    $lastBlock = count($blocks) - 1;
                }

                $lastTag = $tag;

                if ($tag == 'equal') {
                    $lines = array_slice($old, $i1, ($i2 - $i1));
                    $blocks[$lastBlock]['base']['lines'] += $this->formatLines($lines);
                    $lines = array_slice($new, $j1, ($j2 - $j1));
                    $blocks[$lastBlock]['changed']['lines'] += $this->formatLines($lines);
                } else {
                    if ($tag == 'replace' || $tag == 'delete') {
                        $lines = array_slice($old, $i1, ($i2 - $i1));
                        $lines = $this->formatLines($lines);
                        $lines = str_replace(array("\0", "\1"), array('<del>', '</del>'), $lines);
                        $blocks[$lastBlock]['base']['lines'] += $lines;
                    }

                    if ($tag == 'replace' || $tag == 'insert') {
                        $lines = array_slice($new, $j1, ($j2 - $j1));
                        $lines = $this->formatLines($lines);
                        $lines = str_replace(array("\0", "\1"), array('<ins>', '</ins>'), $lines);
                        $blocks[$lastBlock]['changed']['lines'] += $lines;
                    }
                }
            }
            $changes[] = $blocks;
        }

        return $changes;
    }

    /**
     * @param $fromLine
     * @param $toLine
     * @return array
     */
    private function getChangeExtent($fromLine, $toLine) {
        $start = 0;
        $limit = min(strlen($fromLine), strlen($toLine));
        while ($start < $limit && $fromLine{$start} == $toLine{$start}) {
            ++$start;
        }
        $end   = -1;
        $limit = $limit - $start;
        while (-$end <= $limit && substr($fromLine, $end, 1) == substr($toLine, $end, 1)) {
            --$end;
        }

        return array(
            $start,
            $end + 1
        );
    }

    /**
     * @param $lines
     * @return array
     */
    protected function formatLines($lines) {
        $lines = array_map(array($this, 'ExpandTabs'), $lines);
        $lines = array_map(array($this, 'HtmlSafe'), $lines);
        foreach ($lines as &$line) {
            $line = preg_replace('# ( +)|^ #e', "\$this->fixSpaces('\\1')", $line);
        }

        return $lines;
    }

    /**
     * @param string $spaces
     * @return string
     */
    function fixSpaces($spaces = '') {
        $count = strlen($spaces);
        if ($count == 0) {
            return '';
        }

        $div = floor($count / 2);
        $mod = $count % 2;

        return str_repeat('&nbsp; ', $div) . str_repeat('&nbsp;', $mod);
    }

    /**
     * @param $line
     * @return mixed
     */
    private function expandTabs($line) {
        return str_replace("\t", str_repeat(' ', $this->options['tabSize']), $line);
    }

    /**
     * @param $string
     * @return string
     */
    private function htmlSafe($string) {
        return htmlspecialchars($string, ENT_NOQUOTES, 'UTF-8');
    }
}
