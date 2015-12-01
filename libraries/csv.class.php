<?php
/**
 * CSV操作类
 *
 * CSV文件的读取及生成
 * @author tommy <streen003@gmail.com>
 * @copyright Copyright (c) 2010 Tommycode Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: csv.class.php 1.0 2011-12-24 18:08:57Z tommy $
 * @package libraries
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class csv extends Base {

    /**
     * 将CSV文件转化为数组
     *
     * @access public
     * @param string $fileName csv文件名(路径)
     * @param string $delimiter 单元分割符(逗号或制表符)
     * @return array
     */
    public static function readCsv($fileName, $delimiter = ",") {

        //参数分析
        if (!$fileName) {
            return false;
        }

        setlocale(LC_ALL, 'en_US.UTF-8');

        //读取csv文件内容
        $handle       = fopen($fileName, 'r');
        $outputArray  = array();
        $row          = 0;
        while ($data = fgetcsv($handle, 1000, $delimiter)) {
            $num = count($data);
            for ($i = 0; $i < $num; $i ++) {
                $outputArray[$row][$i] = $data[$i];
            }
            $row++;
        }
        fclose($handle);

        return $outputArray;
    }

    /**
     * 生成csv文件
     *
     * @access public
     * @param string $fileName 所要生成的文件名
     * @param array $data csv数据内容, 注:本参数为二维数组
     * @return void
     */
    public static function makeCsv($fileName, $data) {

        //参数分析
        if (!$fileName || !$data || !is_array($data)) {
            return false;
        }
        if (stripos($fileName, '.csv') === false) {
            $fileName .= '.csv';
        }

        //分析$data内容
        $content = '';
        foreach ($data as $lines) {
            if ($lines && is_array($lines)) {
                foreach ($lines as $key=>$value) {
                    if (is_string($value)) {
                        $lines[$key] = '"' . $value . '"';
                    }
                }
                $content .= implode(",", $lines) . "\n";
            }
        }

        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header('Expires:0');
        header('Pragma:public');
        header("Cache-Control: public");
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . $fileName);

        echo $content;
    }
}
