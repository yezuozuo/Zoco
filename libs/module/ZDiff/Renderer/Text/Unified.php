<?php

require_once __DIR__ . '/../Abstract.php';

/**
 * Class ZDiffRendererTextUnified
 */
class ZDiffRendererTextUnified extends ZDiffRendererAbstract {

    /**
     * @return string
     */
    public function render() {
        $diff    = '';
        $opCodes = $this->diff->getGroupedOpcodes();
        foreach ($opCodes as $group) {
            $lastItem = count($group) - 1;
            $i1       = $group[0][1];
            $i2       = $group[$lastItem][2];
            $j1       = $group[0][3];
            $j2       = $group[$lastItem][4];

            if ($i1 == 0 && $i2 == 0) {
                $i1 = -1;
                $i2 = -1;
            }

            $diff .= '@@ -' . ($i1 + 1) . ',' . ($i2 - $i1) . ' +' . ($j1 + 1) . ',' . ($j2 - $j1) . " @@\n";
            foreach ($group as $code) {
                list($tag, $i1, $i2, $j1, $j2) = $code;
                if ($tag == 'equal') {
                    $diff .= ' ' . implode("\n ", $this->diff->getOld($i1, $i2)) . "\n";
                } else {
                    if ($tag == 'replace' || $tag == 'delete') {
                        $diff .= '-' . implode("\n-", $this->diff->getOld($i1, $i2)) . "\n";
                    }

                    if ($tag == 'replace' || $tag == 'insert') {
                        $diff .= '+' . implode("\n+", $this->diff->getNew($j1, $j2)) . "\n";
                    }
                }
            }
        }

        return $diff;
    }
}