<?php
/**
 * ftp class file
 *
 * FTP操作类
 * @author DaBing<InitPHP>, tommy
 * @copyright  CopyRight DoitPHP team, initphp team
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: ftp.class.php 1.3 2011-11-13 21:01:01Z tommy $
 * @package libraries
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class ftp extends Base {

    /**
     * FTP 连接 ID
     *
     * @var object
     */
    private $linkId;


    /**
     * 构造函数
     *
     * @return void
     */
    public function __construct() {

    }

    /**
     * 连接FTP
     *
     * @param string $server
     * @param integer $port
     * @param string $username
     * @param string $password
     * @return boolean
     */
    public function connect($server, $port = 21, $username, $password) {

        //参数分析
        if (!$server || !$username || !$password) {
            return false;
        }

        $this->linkId = ftp_connect($server, $port) or die('FTP server connetc failed!');
        ftp_login($this->linkId, $username, $password) or die ('FTP server login failed!');
        //打开被动模拟
        ftp_pasv($this->linkId, 1);

        return true;
    }

    /**
     * FTP-文件上传
     *
     * @param string  $localFile 本地文件
     * @param string  $ftpFile Ftp文件
     * @return bool
     */
    public function upload($localFile, $ftpFile) {

        if (!$localFile || !$ftpFile) {
            return false;
        }

        $ftpPath = dirname($ftpFile);
        if (!empty($ftpPath)) {
            //创建目录
            $this->makeDir($ftpPath);
            @ftp_chdir($this->linkId, $ftpPath);
            $ftpFile = basename($ftpFile);
        }

        $ret = ftp_nb_put($this->linkId, $ftpFile, $localFile, FTP_BINARY);
        while ($ret == FTP_MOREDATA) {
            $ret = ftp_nb_continue($this->linkId);
           }

        if ($ret != FTP_FINISHED) {
            return false;
        }

        return true;
    }

    /**
     * FTP-文件下载
     *
     * @param string  $localFile 本地文件
     * @param string  $ftpFile Ftp文件
     * @return bool
     */
    public function download($localFile, $ftpFile) {

        if (!$localFile || !$ftpFile) {
            return false;
        }

        $ret = ftp_nb_get($this->linkId, $localFile, $ftpFile, FTP_BINARY);
        while ($ret == FTP_MOREDATA) {
               $ret = ftp_nb_continue ($this->linkId);
        }

        if ($ret != FTP_FINISHED) {
            return false;
        }

        return true;
    }

    /**
     * FTP-创建目录
     *
     * @param string  $path 路径地址
     * @return bool
     */
    public function makeDir($path) {

        if (!$path) {
            return false;
        }

           $dir  = explode("/", $path);
           $path = ftp_pwd($this->linkId) . '/';
           $ret  = true;
           for ($i=0; $i<count($dir); $i++) {
            $path = $path . $dir[$i] . '/';
            if (!@ftp_chdir($this->linkId, $path)) {
                if (!@ftp_mkdir($this->linkId, $dir[$i])) {
                    $ret = false;
                    break;
                }
            }
            @ftp_chdir($this->linkId, $path);
         }

        if (!$ret) {
            return false;
        }

         return true;
    }

    /**
     * FTP-删除文件目录
     *
     * @param string  $dir 删除文件目录
     * @return bool
     */
    public function deleteDir($dir) {

        $dir = $this->checkpath($dir);
        if (@!ftp_rmdir($this->linkId, $dir)) {
            return false;
        }

        return true;
    }

    /**
     * FTP-删除文件
     *
     * @param string  $file 删除文件
     * @return bool
     */
    public function deleteFile($file) {

        $file = $this->checkpath($file);
        if (@!ftp_delete($this->linkId, $file)) {
            return false;
        }

        return true;
    }

    /**
     * FTP-FTP上的文件列表
     *
     * @param string $path 路径
     * @return bool
     */
    public function nlist($path = '/') {

        return ftp_nlist($this->linkId, $path);
    }

    /**
     * FTP-改变文件权限值
     *
     * @param string $file 文件
     * @param string $val  值
     * @return bool
     */
    public function chmod($file, $value = 0777) {

        return @ftp_chmod($this->linkId, $value, $file);
    }

    /**
     * FTP-返回文件大小
     *
     * @param string $file 文件
     * @return bool
     */
    public function fileSize($file) {

        return ftp_size($this->linkId, $file);
    }

    /**
     * FTP-文件修改时间
     *
     * @param string $file 文件
     * @return bool
     */
    public function mdtime($file) {

        return ftp_mdtm($this->linkId, $file);
    }

    /**
     * FTP-更改ftp上的文件名称
     *
     * @param string $oldname 旧文件
     * @param string $newname 新文件名称
     * @return bool
     */
    public function rename($oldname, $newname) {

        return ftp_rename ($this->linkId, $oldname, $newname);
    }

    /**
     * 析构函数
     *
     * @return void
     */
    public function __destruct() {

        if ($this->linkId) {
            ftp_close($this->linkId);
        }
    }
}
