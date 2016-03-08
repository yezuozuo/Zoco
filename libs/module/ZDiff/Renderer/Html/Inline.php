<?php

require_once __DIR__ . '/Array.php';

/**
 * Class ZDiffRendererHtmlInline
 */
class ZDiffRendererHtmlInline extends ZDiffRendererHtmlArray {

    /**
     * @return string
     */
    public function render() {
        $changes = parent::render();
        $html    = '';
        if (empty($changes)) {
            return $html;
        }

        $html .= '<table class="Differences DifferencesInline">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>Old</th>';
        $html .= '<th>New</th>';
        $html .= '<th>Differences</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        foreach ($changes as $i => $blocks) {
            if ($i > 0) {
                $html .= '<tbody class="Skipped">';
                $html .= '<th>&hellip;</th>';
                $html .= '<th>&hellip;</th>';
                $html .= '<td>&nbsp;</td>';
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
                        $html .= '<th>' . $toLine . '</th>';
                        $html .= '<td class="Left">' . $line . '</td>';
                        $html .= '</tr>';
                    }
                } else {
                    if ($change['tag'] == 'insert') {
                        foreach ($change['changed']['lines'] as $no => $line) {
                            $toLine = $change['changed']['offset'] + $no + 1;
                            $html .= '<tr>';
                            $html .= '<th>&nbsp;</th>';
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
                                $html .= '<th>&nbsp;</th>';
                                $html .= '<td class="Left"><del>' . $line . '</del>&nbsp;</td>';
                                $html .= '</tr>';
                            }
                        } else {
                            if ($change['tag'] == 'replace') {
                                foreach ($change['base']['lines'] as $no => $line) {
                                    $fromLine = $change['base']['offset'] + $no + 1;
                                    $html .= '<tr>';
                                    $html .= '<th>' . $fromLine . '</th>';
                                    $html .= '<th>&nbsp;</th>';
                                    $html .= '<td class="Left"><span>' . $line . '</span></td>';
                                    $html .= '</tr>';
                                }

                                foreach ($change['changed']['lines'] as $no => $line) {
                                    $toLine = $change['changed']['offset'] + $no + 1;
                                    $html .= '<tr>';
                                    $html .= '<th>' . $toLine . '</th>';
                                    $html .= '<th>&nbsp;</th>';
                                    $html .= '<td class="Right"><span>' . $line . '</span></td>';
                                    $html .= '</tr>';
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