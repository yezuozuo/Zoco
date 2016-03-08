<?php

require_once __DIR__ . '/../Abstract.php';

/**
 * Class ZDiffRendererTextContext
 */
class ZDiffRendererTextContext extends ZDiffRendererAbstract {

    /**
     * @var array
     */
    private $tagMap = array(
        'insert'  => '+',
        'delete'  => '-',
        'replace' => '!',
        'equal'   => ' '
    );

    /**
     * @return string
     */
    public function render() {
        $diff    = '';
        $opCodes = $this->diff->getGroupedOpcodes();
        foreach ($opCodes as $group) {
            $diff .= "***************\n";
            $lastItem = count($group) - 1;
            $i1       = $group[0][1];
            $i2       = $group[$lastItem][2];
            $j1       = $group[0][3];
            $j2       = $group[$lastItem][4];

            if ($i2 - $i1 >= 2) {
                $diff .= '*** ' . ($group[0][1] + 1) . ',' . $i2 . " ****\n";
            } else {
                $diff .= '*** ' . $i2 . " ****\n";
            }

            if ($j2 - $j1 >= 2) {
                $separator = '--- ' . ($j1 + 1) . ',' . $j2 . " ----\n";
            } else {
                $separator = '--- ' . $j2 . " ----\n";
            }

            $hasVisible = false;
            foreach ($group as $code) {
                if ($code[0] == 'replace' || $code[0] == 'delete') {
                    $hasVisible = true;
                    break;
                }
            }

            if ($hasVisible) {
                foreach ($group as $code) {
                    list($tag, $i1, $i2, $j1, $j2) = $code;
                    unset($j1);
                    unset($j2);
                    if ($tag == 'insert') {
                        continue;
                    }
                    $diff .= $this->tagMap[$tag] . ' ' . implode("\n" . $this->tagMap[$tag] . ' ', $this->diff->getOld($i1, $i2)) . "\n";
                }
            }

            $hasVisible = false;
            foreach ($group as $code) {
                if ($code[0] == 'replace' || $code[0] == 'insert') {
                    $hasVisible = true;
                    break;
                }
            }

            $diff .= $separator;

            if ($hasVisible) {
                foreach ($group as $code) {
                    list($tag, $i1, $i2, $j1, $j2) = $code;
                    unset($i1);
                    unset($i2);
                    if ($tag == 'delete') {
                        continue;
                    }
                    $diff .= $this->tagMap[$tag] . ' ' . implode("\n" . $this->tagMap[$tag] . ' ', $this->diff->getNew($j1, $j2)) . "\n";
                }
            }
        }

        return $diff;
    }
}