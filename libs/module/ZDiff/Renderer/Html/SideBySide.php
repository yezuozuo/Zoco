<?php

require_once __DIR__ . '/Array.php';

/**
 * Class ZDiffRendererHtmlSideBySide
 */
class ZDiffRendererHtmlSideBySide extends ZDiffRendererHtmlArray {

    /**
     * @return string
     */
    public function render() {
        $changes = parent::render();

        $html = '';
        if (empty($changes)) {
            return $html;
        }

        $html .= '<table class="Differences DifferencesSideBySide">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th colspan="2">Old Version</th>';
        $html .= '<th colspan="2">New Version</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        foreach ($changes as $i => $blocks) {
            if ($i > 0) {
                $html .= '<tbody class="Skipped">';
                $html .= '<th>&hellip;</th><td>&nbsp;</td>';
                $html .= '<th>&hellip;</th><td>&nbsp;</td>';
                $html .= '</tbody>';
            }

            foreach ($blocks as $change) {
                $html .= '<tbody class="Change' . ucfirst($change['tag']) . '">';
                if ($change['tag'] == 'equal') {
                    foreach ($change['base']['lines'] as $no => $line) {
                        $fromLine = $change['base']['offset'] + $no + 1;
                        $toLine   = $change['changed']['offset'] + $no + 1;
                        $html .= '<tr>';
                        $html .= '<th>' . $fromLine . '</th>';
                        $html .= '<td class="Left"><span>' . $line . '</span>&nbsp;</span></td>';
                        $html .= '<th>' . $toLine . '</th>';
                        $html .= '<td class="Right"><span>' . $line . '</span>&nbsp;</span></td>';
                        $html .= '</tr>';
                    }
                } else {
                    if ($change['tag'] == 'insert') {
                        foreach ($change['changed']['lines'] as $no => $line) {
                            $toLine = $change['changed']['offset'] + $no + 1;
                            $html .= '<tr>';
                            $html .= '<th>&nbsp;</th>';
                            $html .= '<td class="Left">&nbsp;</td>';
                            $html .= '<th>' . $toLine . '</th>';
                            $html .= '<td class="Right"><ins>' . $line . '</ins>&nbsp;</td>';
                            $html .= '</tr>';
                        }
                    } else {
                        if ($change['tag'] == 'delete') {
                            foreach ($change['base']['lines'] as $no => $line) {
                                $fromLine = $change['base']['offset'] + $no + 1;
                                $html .= '<tr>';
                                $html .= '<th>' . $fromLine . '</th>';
                                $html .= '<td class="Left"><del>' . $line . '</del>&nbsp;</td>';
                                $html .= '<th>&nbsp;</th>';
                                $html .= '<td class="Right">&nbsp;</td>';
                                $html .= '</tr>';
                            }
                        } else {
                            if ($change['tag'] == 'replace') {
                                if (count($change['base']['lines']) >= count($change['changed']['lines'])) {
                                    foreach ($change['base']['lines'] as $no => $line) {
                                        $fromLine = $change['base']['offset'] + $no + 1;
                                        $html .= '<tr>';
                                        $html .= '<th>' . $fromLine . '</th>';
                                        $html .= '<td class="Left"><span>' . $line . '</span>&nbsp;</td>';
                                        if (!isset($change['changed']['lines'][$no])) {
                                            $toLine      = '&nbsp;';
                                            $changedLine = '&nbsp;';
                                        } else {
                                            $toLine      = $change['base']['offset'] + $no + 1;
                                            $changedLine = '<span>' . $change['changed']['lines'][$no] . '</span>';
                                        }
                                        $html .= '<th>' . $toLine . '</th>';
                                        $html .= '<td class="Right">' . $changedLine . '</td>';
                                        $html .= '</tr>';
                                    }
                                } else {
                                    foreach ($change['changed']['lines'] as $no => $changedLine) {
                                        if (!isset($change['base']['lines'][$no])) {
                                            $fromLine = '&nbsp;';
                                            $line     = '&nbsp;';
                                        } else {
                                            $fromLine = $change['base']['offset'] + $no + 1;
                                            $line     = '<span>' . $change['base']['lines'][$no] . '</span>';
                                        }
                                        $html .= '<tr>';
                                        $html .= '<th>' . $fromLine . '</th>';
                                        $html .= '<td class="Left"><span>' . $line . '</span>&nbsp;</td>';
                                        $toLine = $change['changed']['offset'] + $no + 1;
                                        $html .= '<th>' . $toLine . '</th>';
                                        $html .= '<td class="Right">' . $changedLine . '</td>';
                                        $html .= '</tr>';
                                    }
                                }
                            }
                        }
                    }
                }
                $html .= '</tbody>';
            }
        }
        $html .= '</table>';

        return $html;
    }
}