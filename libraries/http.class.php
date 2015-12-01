<?php
/**
 * http.class.php
 *
 * 处理文件加载. 文件下载等相关功能
 * @author tommy <streen003@gmail.com>
 * @copyright Copyright (c) 2010 Tommycode Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: http.class.php 1.3 2012-02-12 02:06:22Z tommy $
 * @package libraries
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class http extends Base {

    /**
     * http下载文件
     *
     * Reads a file and send a header to force download it.
     * @copyright www.doophp.com
     * @param string $file_str File name with absolute path to it
     * @param bool $isLarge If True, the large file will be read chunk by chunk into the memory.
     * @param string $rename Name to replace the file name that would be downloaded
     */
    public static function download($file, $isLarge = false, $rename = NULL){

        if(headers_sent())return false;

        if(!$file) {
            exit('Error 404:The file not found!');
        }

        if($rename==NULL){
            if(strpos($file, '/')===false && strpos($file, '\\')===false)
                $filename = $file;
            else{
                $filename = basename($file);
            }
        }else{
            $filename = $rename;
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        ob_clean();
        flush();

        if($isLarge)
            self::readfileChunked($file);
        else
            readfile($file);
    }

    /**
     * Read a file and display its content chunk by chunk
     *
     * @param string $filename
     * @param bool $retbytes
     * @return mixed
     */
    private function readfileChunked($filename, $retbytes = true, $chunkSize = 1024) {

        $buffer = '';
        $cnt =0;

        $handle = fopen($filename, 'rb');
        if ($handle === false) {
            return false;
        }
        while (!feof($handle)) {
            $buffer = fread($handle, $chunkSize);
            echo $buffer;
            ob_flush();
            flush();
            if ($retbytes) {
                $cnt += strlen($buffer);
            }
        }
        $status = fclose($handle);
        if ($retbytes && $status) {
            return $cnt; // return num. bytes delivered like readfile() does.
        }

        return $status;
    }

    /**
     * HTTP状态信息数组
     *
     * @var array
     */
    protected static $_httpStatus = array(

        '100' => 'Continue',
        '101' => 'Switching Protocols',

        '200' => 'OK',
        '201' => 'Created',
        '202' => 'Accepted',
        '203' => 'Non-Authoritative Information',
        '204' => 'No Content',
        '205' => 'Reset Content',
        '206' => 'Partial Content',

        '300' => 'Multiple Choices',
        '301' => 'Moved Permanently',
        '302' => 'Found',
        '303' => 'See Other',
        '304' => 'Not Modified',
        '305' => 'Use Proxy',
        '306' => '(Unused)',
        '307' => 'Temporary Redirect',

        '400' => 'Bad Request',
        '401' => 'Unauthorized',
        '402' => 'Payment Required',
        '403' => 'Forbidden',
        '404' => 'Not Found',
        '405' => 'Method Not Allowed',
        '406' => 'Not Acceptable',
        '407' => 'Proxy Authentication Required',
        '408' => 'Request Timeout',
        '409' => 'Conflict',
        '410' => 'Gone',
        '411' => 'Length Required',
        '412' => 'Precondition Failed',
        '413' => 'Request Entity Too Large',
        '414' => 'Request-URI Too Long',
        '415' => 'Unsupported Media Type',
        '416' => 'Requested Range Not Satisfiable',
        '417' => 'Expectation Failed',

        '500' => 'Internal Server Error',
        '501' => 'Not Implemented',
        '502' => 'Bad Gateway',
        '503' => 'Service Unavailable',
        '504' => 'Gateway Timeout',
        '505' => 'HTTP Version Not Supported',
    );

    /**
     * 设置HTTP状态信息
     *
     * @access public
     * @param string $code  HTTP 状态编码
     * @param string $name  HTTP 状态信息
     * @return string
     */
    public static function httpStatus($code, $text = null) {
        //获取http协议
        $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
        $text = is_null($text) ? self::$_httpStatus[$code] : $text;
        $status = "$protocol $code $text";

        header($status);
    }
}