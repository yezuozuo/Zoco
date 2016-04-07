<?php

class ZDiffSequenceMatcher {

    /**
     * @var array|null|string
     */
    private $junkCallback = null;

    /**
     * @var null
     */
    private $old = null;

    /**
     * @var null
     */
    private $new = null;

    /**
     * @var array
     */
    private $junkDict = array();

    /**
     * @var array
     */
    private $b2j = array();

    /**
     * @var array
     */
    private $options = array();

    /**
     * @var null
     */
    private $opCodes = null;

    /**
     * @var null
     */
    private $matchingBlocks = null;

    /**
     * @var array
     */
    private $defaultOptions = array(
        'ignoreNewLines'   => false,
        'ignoreWhitespace' => false,
        'ignoreCase'       => false
    );

    /**
     * @param      $old
     * @param      $new
     * @param null $junkCallback
     * @param      $options
     */
    public function __construct($old, $new, $junkCallback = null, $options) {
        $this->old          = null;
        $this->new          = null;
        $this->junkCallback = $junkCallback;
        $this->setOptions($options);
        $this->setSequences($old, $new);
    }

    /**
     * @param $options
     */
    public function setOptions($options) {
        $this->options = array_merge($this->defaultOptions, $options);
    }

    /**
     * @param $old
     * @param $new
     */
    public function setSequences($old, $new) {
        $this->setSeqOld($old);
        $this->setSeqNew($new);
    }

    /**
     * @param $old
     */
    public function setSeqOld($old) {
        if (!is_array($old)) {
            $old = str_split($old);
        }
        if ($old == $this->old) {
            return;
        }

        $this->old            = $old;
        $this->matchingBlocks = null;
        $this->opCodes        = null;
    }

    /**
     * @param $new
     */
    public function setSeqNew($new) {
        if (!is_array($new)) {
            $new = str_split($new);
        }
        if ($new == $this->new) {
            return;
        }

        $this->new            = $new;
        $this->matchingBlocks = null;
        $this->opCodes        = null;
        $this->chainNew();
    }

    private function chainNew() {
        $length      = count($this->new);
        $this->b2j   = array();
        $popularDict = array();

        for ($i = 0; $i < $length; ++$i) {
            $char = $this->new[$i];
            if (isset($this->b2j[$char])) {
                if ($length >= 200 && count($this->b2j[$char]) * 100 > $length) {
                    $popularDict[$char] = 1;
                    unset($this->b2j[$char]);
                } else {
                    $this->b2j[$char][] = $i;
                }
            } else {
                $this->b2j[$char] = array(
                    $i
                );
            }
        }

        foreach (array_keys($popularDict) as $char) {
            unset($this->b2j[$char]);
        }

        $this->junkDict = array();
        if (is_callable($this->junkCallback)) {
            foreach (array_keys($popularDict) as $char) {
                if (call_user_func($this->junkCallback, $char)) {
                    $this->junkDict[$char] = 1;
                    unset($popularDict[$char]);
                }
            }

            foreach (array_keys($this->b2j) as $char) {
                if (call_user_func($this->junkCallback, $char)) {
                    $this->junkDict[$char] = 1;
                    unset($this->b2j[$char]);
                }
            }
        }
    }

    /**
     * @param int $context
     * @return array
     */
    public function getGroupedOpcodes($context = 3) {
        $opCodes = $this->getOpCodes();
        if (empty($opCodes)) {
            $opCodes = array(
                array(
                    'equal',
                    0,
                    1,
                    0,
                    1,
                )
            );
        }

        if ($opCodes[0][0] == 'equal') {
            $opCodes[0] = array(
                $opCodes[0][0],
                max($opCodes[0][1], $opCodes[0][2] - $context),
                $opCodes[0][2],
                max($opCodes[0][3], $opCodes[0][4] - $context),
                $opCodes[0][4],
            );
        }

        $lastItem = count($opCodes) - 1;
        if ($opCodes[$lastItem][0] == 'equal') {
            list($tag, $i1, $i2, $j1, $j2) = $opCodes[$lastItem];
            $opCodes[$lastItem] = array(
                $tag,
                $i1,
                min($i2, $i1 + $context),
                $j1,
                min($j2, $j1 + $context),
            );
        }

        $maxRange = $context * 2;
        $groups   = array();
        $group    = array();
        foreach ($opCodes as $code) {
            list($tag, $i1, $i2, $j1, $j2) = $code;
            if ($tag == 'equal' && $i2 - $i1 > $maxRange) {
                $group[]  = array(
                    $tag,
                    $i1,
                    min($i2, $i1 + $context),
                    $j1,
                    min($j2, $j1 + $context),
                );
                $groups[] = $group;
                $group    = array();
                $i1       = max($i1, $i2 - $context);
                $j1       = max($j1, $j2 - $context);
            }
            $group[] = array(
                $tag,
                $i1,
                $i2,
                $j1,
                $j2,
            );
        }

        if (!empty($group) && !(count($group) == 1 && $group[0][0] == 'equal')) {
            $groups[] = $group;
        }

        return $groups;
    }

    /**
     * @return array
     */
    public function getOpCodes() {
        if (!empty($this->opCodes)) {
            return $this->opCodes;
        }

        $i             = 0;
        $j             = 0;
        $this->opCodes = array();

        $blocks = $this->getMatchingBlocks();
        foreach ($blocks as $block) {
            list($ai, $bj, $size) = $block;
            $tag = '';
            if ($i < $ai && $j < $bj) {
                $tag = 'replace';
            } else {
                if ($i < $ai) {
                    $tag = 'delete';
                } else {
                    if ($j < $bj) {
                        $tag = 'insert';
                    }
                }
            }

            if ($tag) {
                $this->opCodes[] = array(
                    $tag,
                    $i,
                    $ai,
                    $j,
                    $bj
                );
            }

            $i = $ai + $size;
            $j = $bj + $size;

            if ($size) {
                $this->opCodes[] = array(
                    'equal',
                    $ai,
                    $i,
                    $bj,
                    $j
                );
            }
        }

        return $this->opCodes;
    }

    /**
     * @return array|null
     */
    public function getMatchingBlocks() {
        if (!empty($this->matchingBlocks)) {
            return $this->matchingBlocks;
        }

        $aLength = count($this->old);
        $bLength = count($this->new);

        $queue = array(
            array(
                0,
                $aLength,
                0,
                $bLength,
            )
        );

        $matchingBlocks = array();
        while (!empty($queue)) {
            list($alo, $ahi, $blo, $bhi) = array_pop($queue);
            $x = $this->findLongestMatch($alo, $ahi, $blo, $bhi);
            list($i, $j, $k) = $x;
            if ($k) {
                $matchingBlocks[] = $x;
                if ($alo < $i && $blo < $j) {
                    $queue[] = array(
                        $alo,
                        $i,
                        $blo,
                        $j,
                    );
                }

                if ($i + $k < $ahi && $j + $k < $bhi) {
                    $queue[] = array(
                        $i + $k,
                        $ahi,
                        $j + $k,
                        $bhi,
                    );
                }
            }
        }

        usort($matchingBlocks, array($this, 'tupleSort'));

        $i1          = 0;
        $j1          = 0;
        $k1          = 0;
        $nonAdjacent = array();
        foreach ($matchingBlocks as $block) {
            list($i2, $j2, $k2) = $block;
            if ($i1 + $k1 == $i2 && $j1 + $k1 == $j2) {
                $k1 += $k2;
            } else {
                if ($k1) {
                    $nonAdjacent[] = array(
                        $i1,
                        $j1,
                        $k1,
                    );
                }

                $i1 = $i2;
                $j1 = $j2;
                $k1 = $k2;
            }
        }

        if ($k1) {
            $nonAdjacent[] = array(
                $i1,
                $j1,
                $k1,
            );
        }

        $nonAdjacent[] = array(
            $aLength,
            $bLength,
            0,
        );

        $this->matchingBlocks = $nonAdjacent;

        return $this->matchingBlocks;
    }

    /**
     * @param $alo
     * @param $ahi
     * @param $blo
     * @param $bhi
     * @return array
     */
    public function findLongestMatch($alo, $ahi, $blo, $bhi) {
        $old = $this->old;
        $new = $this->new;

        $bestI    = $alo;
        $bestJ    = $blo;
        $bestSize = 0;

        $j2Len   = array();
        $nothing = array();

        for ($i = $alo; $i < $ahi; ++$i) {
            $newJ2Len = array();
            $jDict    = $this->arrayGetDefault($this->b2j, $old[$i], $nothing);
            foreach ($jDict as $jKey => $j) {
                if ($j < $blo) {
                    continue;
                } else {
                    if ($j >= $bhi) {
                        break;
                    }
                }

                $k            = $this->arrayGetDefault($j2Len, $j - 1, 0) + 1;
                $newJ2Len[$j] = $k;
                if ($k > $bestSize) {
                    $bestI    = $i - $k + 1;
                    $bestJ    = $j - $k + 1;
                    $bestSize = $k;
                }
            }

            $j2Len = $newJ2Len;
        }

        while ($bestI > $alo && $bestJ > $blo && !$this->isBJunk($new[$bestJ - 1]) &&
               !$this->linesAreDifferent($bestI - 1, $bestJ - 1)) {
            --$bestI;
            --$bestJ;
            ++$bestSize;
        }

        while ($bestI + $bestSize < $ahi && ($bestJ + $bestSize) < $bhi &&
               !$this->isBJunk($new[$bestJ + $bestSize]) && !$this->linesAreDifferent($bestI + $bestSize, $bestJ + $bestSize)) {
            ++$bestSize;
        }

        return array(
            $bestI,
            $bestJ,
            $bestSize
        );
    }

    /**
     * @param $array
     * @param $key
     * @param $default
     * @return mixed
     */
    private function arrayGetDefault($array, $key, $default) {
        if (isset($array[$key])) {
            return $array[$key];
        } else {
            return $default;
        }
    }

    /**
     * @param $new
     * @return bool
     */
    private function isBJunk($new) {
        if (isset($this->junkDict[$new])) {
            return true;
        }

        return false;
    }

    /**
     * @param $oldIndex
     * @param $newIndex
     * @return bool
     */
    public function linesAreDifferent($oldIndex, $newIndex) {
        $lineOld = $this->old[$oldIndex];
        $lineNew = $this->new[$newIndex];

        if ($this->options['ignoreWhitespace']) {
            $replace = array("\t", ' ');
            $lineOld = str_replace($replace, '', $lineOld);
            $lineNew = str_replace($replace, '', $lineNew);
        }

        if ($this->options['ignoreCase']) {
            $lineOld = strtolower($lineOld);
            $lineNew = strtolower($lineNew);
        }

        if ($lineOld != $lineNew) {
            return true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function ratio() {
        $matches = array_reduce($this->getMatchingBlocks(), array($this, 'ratioReduce'), 0);

        return $this->calculateRatio($matches, count($this->old) + count($this->new));
    }

    /**
     * @param     $matches
     * @param int $length
     * @return int
     */
    private function calculateRatio($matches, $length = 0) {
        if ($length) {
            return 2 * ($matches / $length);
        } else {
            return 1;
        }
    }

    /**
     * @param $sum
     * @param $triple
     * @return mixed
     */
    private function ratioReduce($sum, $triple) {
        return $sum + ($triple[count($triple) - 1]);
    }

    /**
     * @param $old
     * @param $new
     * @return int
     */
    private function tupleSort($old, $new) {
        $max = max(count($old), count($new));
        for ($i = 0; $i < $max; ++$i) {
            if ($old[$i] < $new[$i]) {
                return -1;
            } else {
                if ($old[$i] > $new[$i]) {
                    return 1;
                }
            }
        }

        if (count($old) == count($new)) {
            return 0;
        } else {
            if (count($old) < count($new)) {
                return -1;
            } else {
                return 1;
            }
        }
    }
}