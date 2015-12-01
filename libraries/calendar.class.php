<?php
/**
 * calendar class file
 *
 * @author tommy <streen003@gmail.com>
 * @copyright  Copyright (c) 2010 Tommy Software Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: calendar.class.php 1.3 2011-11-13 20:47:01Z tommy $
 * @package libraries
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class calendar extends Base {

    /**
     * 显示日历.
     *
     * @access public
     * @param integer $year    年
     * @param integer $month 月
     * @param array $usedArray
     * @return string
     */
    public static function show($year = null, $month = null, $usedArray = null, $target = '_self', $className = 'doitphp_calendar') {

        //获得当前时间戳
        $timeNow      = isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time();
        $year         = is_null($year) ? date('Y', $timeNow) : $year;
        $month        = is_null($month) ? date('m', $timeNow) : $month;

        $yearNow      = date('Y',$timeNow);
        $monthNow     = date('m',$timeNow);
        $dateNow      = date('j', $timeNow);
        $timeIndex    = mktime(0, 0, 0, $month, 1, $year);

        //获取当前所在月份的总天数，第一天的星期数.
        $totalDays   = date('t', $timeIndex);
        $dayIndex    = date('w', $timeIndex);

        //计算日历的总行数。
        $totalLines  = ceil(($totalDays + $dayIndex)/7);

        //分析日期占用
        if (!is_null($usedArray) && is_array($usedArray)) {
            $usedStatus = true;
            $usedDateArray = array_keys($usedArray);
        } else {
            $usedStatus = false;
        }

        $html =<<<EOT
<!-- calendar begin --><table border="0" cellpadding="0" cellspacing="1" class="$className"><tr align="center"><td colspan="7" class="message">$year 年  $month 月</td></tr><tr class="menu" align="center"><td>日</td><td>一</td><td>二</td><td>三</td><td>四</td><td>五</td><td>六</td></tr>
EOT;

        for ($i=0; $i<$totalLines; $i++) {
            $html .= "<tr align=\"center\">";
            for ($k = 0; $k < 7; $k ++) {
                $dateShow = intval( 7 * $i + $k - $dayIndex + 1);
                if (($dateShow < 1) || ($dateShow > $totalDays)) {
                    $html .= "<td>&nbsp;</td>";
                } else {
                    if ($usedStatus === true && in_array($dateShow, $usedDateArray)) {
                        $html .= "<td class=\"used\"><a href=\"{$usedArray[$dateShow]}\" target=\"{$target}\">" . $dateShow . "</a></td>";
                    } else {
                        $html .= "<td" . ((($dateShow == $dateNow) && ($monthNow == $month) && ($yearNow == $year)) ? " class=\"today\"" : "") . ">" . $dateShow . "</td>";
                    }
                }
            }
            $html .= "</tr>";
        }
        $html .= "</table><!-- calendar end -->\r\n";

        return $html;
    }

    /**
     * 加载DoitPHP官方默认的日历CSS文件
     *
     * @access public
     * @return string
     */
    public function loadCss() {

        return '<link href="' . Controller::getAssetUrl('doit/images') . 'doitphp_calendar.min.css' .'" rel="stylesheet" type="text/css" />';
    }
}