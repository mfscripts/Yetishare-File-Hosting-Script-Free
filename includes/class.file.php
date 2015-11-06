<?php

/**
 * main file class
 */
class file
{

    public $errorMsg = null;

    function __construct()
    {
        $this->errorMsg = null;
    }

    public function download()
    {
        // remove session
        if (isset($_SESSION['showDownload']))
        {
            // reset session variable for next time
            $_SESSION['showDownload'] = null;
            unset($_SESSION['showDownload']);
            session_write_close();
        }

        // php script timeout for long downloads (2 days!)
        set_time_limit(60 * 60 * 24 * 2);

        // load the server the file is on
        $storageType         = 'local';
        $storageLocation     = _CONFIG_FILE_STORAGE_PATH;
        $uploadServerDetails = $this->loadServer();
        if ($uploadServerDetails != false)
        {
            $storageLocation = $uploadServerDetails['storagePath'];
            $storageType     = $uploadServerDetails['serverType'];

            // if no storage path set & local, use system default
            if ((strlen($storageLocation) == 0) && ($storageType == 'local'))
            {
                $storageLocation = _CONFIG_FILE_STORAGE_PATH;
            }
        }

        // get file path
        $fullPath = $this->getFullFilePath($storageLocation);

        // open file - via ftp
        if ($storageType == 'remote')
        {
            // connect via ftp
            $conn_id = ftp_connect($uploadServerDetails['ipAddress'], $uploadServerDetails['ftpPort'], 30);
            if ($conn_id === false)
            {
                $this->errorMsg = 'Could not connect to ' . $uploadServerDetails['ipAddress'] . ' to upload file.';
                return false;
            }

            // authenticate
            $login_result = ftp_login($conn_id, $uploadServerDetails['ftpUsername'], $uploadServerDetails['ftpPassword']);
            if ($login_result === false)
            {
                $this->errorMsg = 'Could not login to ' . $uploadServerDetails['ipAddress'] . ' with supplied credentials.';
                return false;
            }

            // prepare the stream of data
            $pipes = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
            if ($pipes === false)
            {
                $this->errorMsg = 'Could not create stream to download file on ' . $uploadServerDetails['ipAddress'];
                return false;
            }

            stream_set_write_buffer($pipes[0], 10000);
            stream_set_timeout($pipes[1], 10);
            stream_set_blocking($pipes[1], 0);

            $fail = false;
            $ret  = ftp_nb_fget($conn_id, $pipes[0], $fullPath, FTP_BINARY, FTP_AUTORESUME);
        }
        // open file - locally
        else
        {
            $handle = @fopen($fullPath, "r");
            if (!$handle)
            {
                $this->errorMsg = 'Could not open file for reading.';
                return false;
            }
        }

        // download speed
        $speed = 0;

        // if free/non user
        $Auth = Auth::getAuth();
        if (($Auth->loggedIn == false) || ($Auth->level == 'free user'))
        {
            $speed = (int) SITE_CONFIG_FREE_USER_MAX_DOWNLOAD_SPEED;
        }
        else
        {
            $speed = (int) SITE_CONFIG_PREMIUM_USER_MAX_DOWNLOAD_SPEED;
        }

        // do we need to throttle the speed?
        if ($speed > 0)
        {
            // create new throttle config
            $config = new ThrottleConfig();

            // set standard transfer rate (in bytes/second)
            $config->burstLimit = $speed;
            $config->rateLimit = $speed;

            // enable module (this is a default value)
            $config->enabled = true;

            // start throttling
            $x = new Throttle($config);
        }

        // output some headers
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-type: " . $this->fileType);
        header("Pragma: public");
        header("Content-Disposition: attachment; filename=\"" . str_replace("\"", "", $this->originalFilename) . "\"");
        header("Content-Description: File Transfer");
        header("Content-Length: " . $this->fileSize);

        // output file - via ftp
        if ($storageType == 'remote')
        {
            while ($ret == FTP_MOREDATA)
            {
                $contents = stream_get_contents($pipes[1]);
                if ($contents !== false)
                {
                    echo $contents;
                    flush();
                }

                $ret = ftp_nb_continue($conn_id);
            }

            /*
              $contents = stream_get_contents($pipes[1]);
              if($contents !== false)
              {
              echo $contents;
              flush();
              }
             */

            fclose($pipes[0]);
            fclose($pipes[1]);
        }
        // output file - local
        else
        {
            while (($buffer = fgets($handle, 4096)) !== false)
            {
                echo $buffer;
            }
            fclose($handle);
        }
        exit();
    }

    public function loadServer()
    {
        // load the server the file is on
        if ((int) $this->serverId)
        {
            // load from the db
            $db                  = Database::getDatabase(true);
            $uploadServerDetails = $db->getRow('SELECT * FROM file_server WHERE id = ' . $db->quote((int) $this->serverId));
            $db->close();
            if (!$uploadServerDetails)
            {
                return false;
            }

            return $uploadServerDetails;
        }

        return false;
    }

    public function getFullFilePath($prePath = '')
    {
        if (substr($prePath, strlen($prePath) - 1, 1) == '/')
        {
            $prePath = substr($prePath, 0, strlen($prePath) - 1);
        }

        return $prePath . '/' . $this->localFilePath;
    }

    /**
     * Get full short url path
     *
     * @return string
     */
    public function getFullShortUrl()
    {
        if (SITE_CONFIG_FILE_URL_SHOW_FILENAME == 'yes')
        {
            return $this->getFullLongUrl();
        }

        return _CONFIG_SITE_PROTOCOL . '://' . _CONFIG_SITE_FILE_DOMAIN . '/' . $this->shortUrl;
    }

    public function getStatisticsUrl()
    {
        return _CONFIG_SITE_PROTOCOL . '://' . _CONFIG_SITE_FILE_DOMAIN . '/' . $this->shortUrl . '~s';
    }

    public function getDeleteUrl()
    {
        return _CONFIG_SITE_PROTOCOL . '://' . _CONFIG_SITE_FILE_DOMAIN . '/' . $this->shortUrl . '~d?' . $this->deleteHash;
    }

    public function getInfoUrl()
    {
        return _CONFIG_SITE_PROTOCOL . '://' . _CONFIG_SITE_FILE_DOMAIN . '/' . $this->shortUrl . '~i?' . $this->deleteHash;
    }

    public function getShortInfoUrl()
    {
        return _CONFIG_SITE_PROTOCOL . '://' . _CONFIG_SITE_FILE_DOMAIN . '/' . $this->shortUrl . '~i';
    }

    /**
     * Get full long url including the original filename
     *
     * @return string
     */
    public function getFullLongUrl()
    {
        return _CONFIG_SITE_PROTOCOL . '://' . _CONFIG_SITE_FILE_DOMAIN . '/' . $this->shortUrl . '/' . slugify($this->originalFilename);
    }

    /**
     * Method to increment visitors
     */
    public function updateVisitors()
    {
        $db = Database::getDatabase(true);
        $this->visits++;
        $db->query('UPDATE file SET visits = :visits WHERE id = :id', array('visits' => $this->visits, 'id'     => $this->id));
    }

    /**
     * Method to update last accessed
     */
    public function updateLastAccessed()
    {
        $db = Database::getDatabase(true);
        $db->query('UPDATE file SET lastAccessed = NOW() WHERE id = :id', array('id' => $this->id));
    }

    /**
     * Method to set folder
     */
    public function updateFolder($folderId = '')
    {
        $db = Database::getDatabase(true);
        $folderId = (int)$folderId;
        if($folderId == 0)
        {
            $folderId = '';
        }
        $db->query('UPDATE file SET folderId = :folderId WHERE id = :id', array('folderId' => $folderId, 'id'       => $this->id));
    }

    /**
     * Remove by user
     */
    public function removeByUser()
    {
        // load the server the file is on
        $storageType         = 'local';
        $storageLocation     = _CONFIG_FILE_STORAGE_PATH;
        $uploadServerDetails = $this->loadServer();
        if ($uploadServerDetails != false)
        {
            $storageLocation = $uploadServerDetails['storagePath'];
            $storageType     = $uploadServerDetails['serverType'];
            if ((strlen($uploadServerDetails['storagePath']) == 0) && ($storageType == 'local'))
            {
                $storageLocation = _CONFIG_FILE_STORAGE_PATH;
            }
        }

        // file path
        $filePath = $this->getFullFilePath($storageLocation);

        // remote - ftp
        if ($storageType == 'remote')
        {
            // connect via ftp
            $conn_id = ftp_connect($uploadServerDetails['ipAddress'], $uploadServerDetails['ftpPort'], 30);
            if ($conn_id === false)
            {
                $this->errorMsg = 'Could not connect to ' . $uploadServerDetails['ipAddress'] . ' to upload file.';
                return false;
            }

            // authenticate
            $login_result = ftp_login($conn_id, $uploadServerDetails['ftpUsername'], $uploadServerDetails['ftpPassword']);
            if ($login_result === false)
            {
                $this->errorMsg = 'Could not login to ' . $uploadServerDetails['ipAddress'] . ' with supplied credentials.';
                return false;
            }

            // remove file
            if (!ftp_delete($conn_id, $filePath))
            {
                $this->errorMsg = 'Could not remove file on ' . $uploadServerDetails['ipAddress'];
                return false;
            }
        }
        // local
        else
        {
            // delete file from server
            unlink($filePath);
        }

        // update db
        $db = Database::getDatabase(true);
        $db->query('UPDATE file SET statusId = 2 WHERE id = :id', array('id' => $this->id));
    }

    /**
     * Remove by system
     */
    public function removeBySystem()
    {
        // load the server the file is on
        $storageType         = 'local';
        $storageLocation     = _CONFIG_FILE_STORAGE_PATH;
        $uploadServerDetails = $this->loadServer();
        if ($uploadServerDetails != false)
        {
            $storageLocation = $uploadServerDetails['storagePath'];
            $storageType     = $uploadServerDetails['serverType'];
            if ((strlen($uploadServerDetails['storagePath']) == 0) && ($storageType == 'local'))
            {
                $storageLocation = _CONFIG_FILE_STORAGE_PATH;
            }
        }

        // file path
        $filePath = $this->getFullFilePath($storageLocation);

        // remote - ftp
        if ($storageType == 'remote')
        {
            // connect via ftp
            $conn_id = ftp_connect($uploadServerDetails['ipAddress'], $uploadServerDetails['ftpPort'], 30);
            if ($conn_id === false)
            {
                $this->errorMsg = 'Could not connect to ' . $uploadServerDetails['ipAddress'] . ' to upload file.';
                return false;
            }

            // authenticate
            $login_result = ftp_login($conn_id, $uploadServerDetails['ftpUsername'], $uploadServerDetails['ftpPassword']);
            if ($login_result === false)
            {
                $this->errorMsg = 'Could not login to ' . $uploadServerDetails['ipAddress'] . ' with supplied credentials.';
                return false;
            }

            // remove file
            if (!ftp_delete($conn_id, $filePath))
            {
                $this->errorMsg = 'Could not remove file on ' . $uploadServerDetails['ipAddress'];
                return false;
            }
        }
        // local
        else
        {
            // delete file from server
            unlink($filePath);
        }

        // update db
        $db = Database::getDatabase(true);
        $db->query('UPDATE file SET statusId = 5 WHERE id = :id', array('id' => $this->id));
    }

    public function getLargeIconPath()
    {
        $fileTypePath = DOC_ROOT . '/themes/' . SITE_CONFIG_SITE_THEME . '/images/file_icons/512px/' . $this->extension . '.png';
        if (!file_exists($fileTypePath))
        {
            return false;
        }

        return SITE_IMAGE_PATH . '/file_icons/512px/' . $this->extension . '.png';
    }

    public function getFilenameExcExtension()
    {
        $filename = $this->originalFilename;

        return basename($filename, '.' . $this->extension);
    }

    /**
     * Load by short url
     *
     * @param string $shortUrl
     * @return file
     */
    static function loadByShortUrl($shortUrl)
    {
        $db  = Database::getDatabase(true);
        $row = $db->getRow('SELECT * FROM file WHERE shortUrl = ' . $db->quote($shortUrl));
        if (!is_array($row))
        {
            return false;
        }

        $fileObj = new file();
        foreach ($row AS $k => $v)
        {
            $fileObj->$k = $v;
        }

        return $fileObj;
    }

    /**
     * Load by delete hash
     *
     * @param string $deleteHash
     * @return file
     */
    static function loadByDeleteHash($deleteHash)
    {
        $db  = Database::getDatabase(true);
        $row = $db->getRow('SELECT * FROM file WHERE deleteHash = ' . $db->quote($deleteHash));
        if (!is_array($row))
        {
            return false;
        }

        $fileObj = new file();
        foreach ($row AS $k => $v)
        {
            $fileObj->$k = $v;
        }

        return $fileObj;
    }

    /**
     * Load by id
     *
     * @param integer $shortUrl
     * @return file
     */
    static function loadById($id)
    {
        $db  = Database::getDatabase(true);
        $row = $db->getRow('SELECT * FROM file WHERE id = ' . (int) $id);
        if (!is_array($row))
        {
            return false;
        }

        $fileObj = new file();
        foreach ($row AS $k => $v)
        {
            $fileObj->$k = $v;
        }

        return $fileObj;
    }

    /**
     * Create short url
     *
     * @param integer $in
     * @return string
     */
    static function createShortUrlPart($in)
    {
        $codeset  = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $cbLength = strlen($codeset);
        while ((int) $in > $cbLength - 1)
        {
            $out = $codeset[fmod($in, $cbLength)] . $out;
            $in  = floor($in / $cbLength);
        }
        return $codeset[$in] . $out;
    }

    /**
     * Update short url for file
     *
     * @param integer $id
     * @param string $shortUrl
     */
    static function updateShortUrl($id, $shortUrl = '')
    {
        $db = Database::getDatabase(true);
        $db->query('UPDATE file SET shortUrl = :shorturl WHERE id = :id', array('shorturl' => $shortUrl, 'id'       => $id));
    }

    /**
     * Load all by account id
     *
     * @param integer $accountId
     * @return array
     */
    static function loadAllByAccount($accountId)
    {
        $db = Database::getDatabase(true);
        $rs = $db->getRows('SELECT * FROM file WHERE userId = ' . $db->quote($accountId) . ' ORDER BY originalFilename');
        if (!is_array($rs))
        {
            return false;
        }

        return $rs;
    }

    /**
     * Load all active by folder id
     *
     * @param integer $folderId
     * @return array
     */
    static function loadAllActiveByFolderId($folderId)
    {
        $db = Database::getDatabase(true);
        $rs = $db->getRows('SELECT * FROM file WHERE folderId = ' . $db->quote($folderId) . ' AND statusId = 1 ORDER BY originalFilename');
        if (!is_array($rs))
        {
            return false;
        }

        return $rs;
    }

    /**
     * Load all active by account id
     *
     * @param integer $accountId
     * @return array
     */
    static function loadAllActiveByAccount($accountId)
    {
        $db = Database::getDatabase(true);
        $rs = $db->getRows('SELECT * FROM file WHERE userId = ' . $db->quote($accountId) . ' AND statusId = 1 ORDER BY originalFilename');
        if (!is_array($rs))
        {
            return false;
        }

        return $rs;
    }

    /**
     * Load recent files based on account id
     *
     * @param integer $accountId
     * @return array
     */
    static function loadAllRecentByAccount($accountId, $activeOnly = false)
    {
        $db = Database::getDatabase(true);
        $rs = $db->getRows('SELECT * FROM file WHERE userId = ' . $db->quote($accountId) . ($activeOnly === true ? ' AND statusId=1' : '') . ' ORDER BY uploadedDate DESC LIMIT 10');
        if (!is_array($rs))
        {
            return false;
        }

        return $rs;
    }

    /**
     * Load recent files based on IP address
     *
     * @param string $ip
     * @return array
     */
    static function loadAllRecentByIp($ip, $activeOnly = false)
    {
        $db = Database::getDatabase(true);
        $rs = $db->getRows('SELECT * FROM file WHERE uploadedIP = ' . $db->quote($ip) . ($activeOnly === true ? ' AND statusId=1' : '') . ' ORDER BY uploadedDate DESC LIMIT 10');
        if (!is_array($rs))
        {
            return false;
        }

        return $rs;
    }

    /**
     * Get status label
     *
     * @param integer $statusId
     * @return string
     */
    static function getStatusLabel($statusId)
    {
        $db  = Database::getDatabase(true);
        $row = $db->getRow('SELECT label FROM file_status WHERE id = ' . (int) $statusId);
        if (!is_array($row))
        {
            return 'unknown';
        }

        return $row['label'];
    }

}
