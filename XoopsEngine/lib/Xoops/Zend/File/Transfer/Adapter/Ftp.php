<?php
/**
 * Zend Framework for Xoops Engine
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Xoops Engine http://www.xoopsengine.org
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @since           3.0
 * @category        Xoops_Zend
 * @package         File
 * @version         $Id$
 */

class Xoops_Zend_File_Transfer_Adapter_Ftp extends Xoops_Zend_File_Transfer_Adapter_Abstract
{
    /**
     * The link identifier of the FTP connection
     */
    protected $stream;

    /**
     * Constructor for Http File Transfers
     *
     * @param array $options OPTIONAL Options to set
     */
    public function __construct($options = array())
    {
        if (!extension_loaded('ftp')) {
            throw new Zend_File_Transfer_Exception('FTP extension is not loaded!');
        }

        $this->setOptions($options);
        $this->addValidator('Upload', false, $this->_files);
    }

    /**
     * Opens an FTP connection
     *
     * @param string $host
     * @param int $port
     * @param int $timeout
     * @return ftp stream
     */
    public function connect($host = null, $port = null, $timeout = null)
    {
        $ftpFunction = (!empty($this->_options['ssl']) && function_exists('ftp_ssl_connect')) ? 'ftp_ssl_connect' : 'ftp_connect';
        $host = $host ?: $this->_options['host'];
        $port = $port ?: (isset($this->_options['port']) ? $this->_options['port'] : null);
        $timeout = $timeout ?: (isset($this->_options['timeout']) ? $this->_options['timeout'] : null);
        $this->stream = $ftpFunction($host, $port, $timeout);
        if (!$this->stream) {
            throw new Exception('Failed to connect to FTP Serve');
        }

        if (ftp_login($this->stream, $this->_options['username'], $this->_options['password'])) {
            throw new Exception('Failed to login to FTP Serve');
        }

        //Set the Connection to use Passive FTP
        ftp_pasv($this->stream, true);

        return $this->stream;
    }

    /**
     * Send the file to the client (Download)
     *
     * @param  string $remoteFile Remote file to be downloaded
     * @param  resource|null $localFile File handler to save contents; for return contents if local file is not set
     * @param  int $mode The transfer mode. Must be either FTP_ASCII or FTP_BINARY.
     * @param  int $resumepos The position in the remote file to start downloading from.
     * @return string|bool
     */
    public function send($remoteFile, $localFile = null, $mode = FTP_ASCII, $resumepos = 0)
    {
        if (!$localFile) {
            $tempFile = tmpfile();
            if (!$tempFile) {
                $tempFilePath = Xoops::path('var') . '/cache/system/ftp.' . uniqid();
                $tempFile = fopen($tempFilePath, 'r+');
            };
        } else {
            $tempFile = $localFile;
        }

        if (!ftp_fget($this->stream, $tempFile, $remoteFile, $mode, $resumepos)) {
            return false;
        }
        if ($localFile) {
            return true;
        }

        fseek($tempFile, 0);
        $contents = '';
        while (!feof($tempFile)) {
            $contents .= fread($tempFile, 8192);
        }
        fclose($tempFile);
        if (!empty($tempFilePath)) {
            unlink($tempFilePath);
        }

        return $contents;
    }

    /**
     * Receive the file to the server (Upload)
     *
     * @param  string $remoteFile The remote file path.
     * @param  resource|string $localFile An open file pointer on the local file; or contents to be uploaded
     * @param  int $mode The transfer mode. Must be either FTP_ASCII or FTP_BINARY.
     * @param  int $startpos The position in the remote file to start uploading to.
     * @return bool
     */
    public function receive($remoteFile, $localFile, $mode = null, $startpos = 0)
    {
        if (is_string($localFile)) {
            if (!$mode) {
                $mode = preg_match('|[^\x20-\x7E]|', $localFile) ? FTP_BINARY : FTP_ASCII;
            }
            $tempFile = tmpfile();
            if (!$tempFile) {
                $tempFilePath = Xoops::path('var') . '/cache/system/ftp.' . uniqid();
                $tempFile = fopen($tempFilePath, 'r+');
            };
            fwrite($tempFile, $localFile);
            fseek($tempFile, 0); //Skip back to the start of the file being written to
        } else {
            $tempFile = $localFile;
            $mode = $mode ?: FTP_BINARY;
        }
        $result = ftp_fput($this->stream, $remoteFile, $tempFile, $mode);

        if (is_string($localFile)) {
            fclose($tempFile);
            if (!empty($tempFilePath)) {
                unlink($tempFilePath);
            }
        }

        return $result;
    }
}