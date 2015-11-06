<?php

error_reporting(E_ALL | E_STRICT);

/* setup includes */
require_once('includes/master.inc.php');

class uploadHandler
{

    private $options;

    function __construct($options = null)
    {
        // get accepted file types
        $acceptedFileTypes = getAcceptedFileTypes();

        $this->options = array(
            'script_url' => $_SERVER['PHP_SELF'],
            'upload_dir' => _CONFIG_FILE_STORAGE_PATH,
            'upload_url' => dirname($_SERVER['PHP_SELF']) . '/files/',
            'param_name' => 'files',
            'delete_hash' => '',
            // The php.ini settings upload_max_filesize and post_max_size
            // take precedence over the following max_file_size setting:
            'max_file_size' => $this->get_max_upload_size(),
            'min_file_size' => 1,
            'accept_file_types' => COUNT($acceptedFileTypes) ? ('/(\.|\/)(' . str_replace(".", "", implode("|", $acceptedFileTypes)) . ')$/i') : '/.+$/i',
            'max_number_of_files' => null,
            'discard_aborted_uploads' => true,
            'image_versions' => array(
                'thumbnail' => array(
                    'upload_dir' => dirname(__FILE__) . '/thumbnails/',
                    'upload_url' => dirname($_SERVER['PHP_SELF']) . '/thumbnails/',
                    'max_width' => 80,
                    'max_height' => 80
                )
            )
        );
        if ($options)
        {
            $this->options = array_replace_recursive($this->options, $options);
        }
    }

    private function get_max_upload_size()
    {
        // Initialize current user
        $Auth = Auth::getAuth();

        // max allowed upload size
        $maxUploadSize = SITE_CONFIG_FREE_USER_MAX_UPLOAD_FILESIZE;
        if ($Auth->loggedIn())
        {
            // check if user is a premium/paid user
            if ($Auth->level != 'free user')
            {
                $maxUploadSize = SITE_CONFIG_PREMIUM_USER_MAX_UPLOAD_FILESIZE;
            }
        }

        // if php restrictions are lower than permitted, override
        $phpMaxSize = getPHPMaxUpload();
        if ($phpMaxSize < $maxUploadSize)
        {
            $maxUploadSize = $phpMaxSize;
        }

        return $maxUploadSize;
    }

    private function get_file_object($file_name)
    {
        $file_path = $this->options['upload_dir'] . $file_name;
        if (is_file($file_path) && $file_name[0] !== '.')
        {
            $file = new stdClass();
            $file->name = $file_name;
            $file->size = filesize($file_path);
            $file->url = $this->options['upload_url'] . rawurlencode($file->name);
            foreach ($this->options['image_versions'] as $version => $options)
            {
                if (is_file($options['upload_dir'] . $file_name))
                {
                    $file->{$version . '_url'} = $options['upload_url']
                            . rawurlencode($file->name);
                }
            }
            $file->delete_url = '~d?' . $this->options['delete_hash'];
            $file->info_url = '~i?' . $this->options['delete_hash'];
            $file->delete_type = 'DELETE';
            return $file;
        }
        return null;
    }

    private function get_file_objects()
    {
        return array_values(array_filter(array_map(
                                        array($this, 'get_file_object'), scandir($this->options['upload_dir'])
                                )));
    }

    private function create_scaled_image($file_name, $options)
    {
        $file_path = $this->options['upload_dir'] . $file_name;
        $new_file_path = $options['upload_dir'] . $file_name;
        list($img_width, $img_height) = @getimagesize($file_path);
        if (!$img_width || !$img_height)
        {
            return false;
        }
        $scale = min(
                $options['max_width'] / $img_width, $options['max_height'] / $img_height
        );
        if ($scale > 1)
        {
            $scale = 1;
        }
        $new_width = $img_width * $scale;
        $new_height = $img_height * $scale;
        $new_img = @imagecreatetruecolor($new_width, $new_height);
        switch (strtolower(substr(strrchr($file_name, '.'), 1)))
        {
            case 'jpg':
            case 'jpeg':
                $src_img = @imagecreatefromjpeg($file_path);
                $write_image = 'imagejpeg';
                break;
            case 'gif':
                $src_img = @imagecreatefromgif($file_path);
                $write_image = 'imagegif';
                break;
            case 'png':
                $src_img = @imagecreatefrompng($file_path);
                $write_image = 'imagepng';
                break;
            default:
                $src_img = $image_method = null;
        }
        $success = $src_img && @imagecopyresampled(
                        $new_img, $src_img, 0, 0, 0, 0, $new_width, $new_height, $img_width, $img_height
                ) && $write_image($new_img, $new_file_path);
        // Free up memory (imagedestroy does not delete files):
        @imagedestroy($src_img);
        @imagedestroy($new_img);
        return $success;
    }

    private function has_error($uploaded_file, $file, $error)
    {
        if ($error)
        {
            return $error;
        }
        if (!preg_match($this->options['accept_file_types'], $file->name))
        {
            return 'acceptFileTypes';
        }
        if ($uploaded_file && is_uploaded_file($uploaded_file))
        {
            $file_size = filesize($uploaded_file);
        } else
        {
            $file_size = $_SERVER['CONTENT_LENGTH'];
        }
        if ($this->options['max_file_size'] && (
                $file_size > $this->options['max_file_size'] ||
                $file->size > $this->options['max_file_size'])
        )
        {
            return 'maxFileSize';
        }
        if ($this->options['min_file_size'] &&
                $file_size < $this->options['min_file_size'])
        {
            return 'minFileSize';
        }
        if (is_int($this->options['max_number_of_files']) && (
                count($this->get_file_objects()) >= $this->options['max_number_of_files'])
        )
        {
            return 'maxNumberOfFiles';
        }
        return $error;
    }

    private function handle_file_upload($uploaded_file, $name, $size, $type, $error)
    {
        $fileUpload = new stdClass();
        $fileUpload->name = basename(stripslashes($name));
        $fileUpload->size = intval($size);
        $fileUpload->type = $type;
        $fileUpload->error = null;

        $extension = end(explode(".", $fileUpload->name));
        $fileUpload->error = $this->has_error($uploaded_file, $fileUpload, $error);
        if (!$fileUpload->error)
        {
            if (strlen(trim($fileUpload->name)) == 0)
            {
                $fileUpload->error = 'Filename not found.';
            }
        }
        elseif (intval($size) == 0)
        {
            $fileUpload->error = 'File received has zero size.';
        }
        elseif (intval($size) > $this->options['max_file_size'])
        {
            $fileUpload->error = 'File received is larger than permitted.';
        }
        
        if (!$fileUpload->error && $fileUpload->name)
        {
            if ($fileUpload->name[0] === '.')
            {
                $fileUpload->name = substr($fileUpload->name, 1);
            }
            $newFilename = MD5(microtime());
            
            // figure out upload type
            $file_size = 0;
            
            // select server from pool
            $uploadServerId = getAvailableServerId();
            $db = Database::getDatabase(true);
            $uploadServerDetails = $db->getRow('SELECT * FROM file_server WHERE id = ' . $db->quote($uploadServerId));
            
            // override storage path
            if(strlen($uploadServerDetails['storagePath']))
            {
                $this->options['upload_dir'] = $uploadServerDetails['storagePath'];
				if (substr($this->options['upload_dir'], strlen($this->options['upload_dir']) - 1, 1) == '/')
				{
					$this->options['upload_dir'] = substr($this->options['upload_dir'], 0, strlen($this->options['upload_dir']) - 1);
				}
				$this->options['upload_dir'] .= '/';
            }
            
            // move remotely via ftp
            if($uploadServerDetails['serverType'] == 'remote')
            {
                // connect ftp
                $conn_id = ftp_connect($uploadServerDetails['ipAddress'], $uploadServerDetails['ftpPort'], 30);
                if($conn_id === false)
                {
                    $fileUpload->error = 'Could not connect to file server '.$uploadServerDetails['ipAddress'];
                }
                
                // authenticate
                if(!$fileUpload->error)
                {
                    $login_result = ftp_login($conn_id, $uploadServerDetails['ftpUsername'], $uploadServerDetails['ftpPassword']);
                    if($login_result === false)
                    {
                        $fileUpload->error = 'Could not authenticate with file server '.$uploadServerDetails['ipAddress'];
                    }
                }

                // create the upload folder
                if(!$fileUpload->error)
                {
                    $uploadPathDir = $this->options['upload_dir'] . substr($newFilename, 0, 2);
                    if(!ftp_mkdir($conn_id, $uploadPathDir))
                    {
						// Error reporting removed for now as it causes issues with existing folders. Need to add a check in before here
						// to see if the folder exists, then create if not.
						// $fileUpload->error = 'There was a problem creating the storage folder on '.$uploadServerDetails['ipAddress'];
                    }
                }
                
                // upload via ftp
                if(!$fileUpload->error)
                {
                    $file_path = $uploadPathDir . '/' . $newFilename;
                    clearstatcache();
                    if ($uploaded_file && is_uploaded_file($uploaded_file))
                    {
                        // initiate ftp
                        $ret = ftp_nb_put($conn_id, $file_path, $uploaded_file, 
                                            FTP_BINARY, FTP_AUTORESUME);
                        while ($ret == FTP_MOREDATA)
                        {
                            // continue uploading
                            $ret = ftp_nb_continue($conn_id);
                        }

                        if ($ret != FTP_FINISHED)
                        {
                            $fileUpload->error = 'There was a problem uploading the file to '.$uploadServerDetails['ipAddress'];
                        }
                        else
                        {
                            $file_size = filesize($uploaded_file);
                            @unlink($uploaded_file);
                        }
                    }
                }
                
                // close ftp connection
                ftp_close($conn_id);
            }
            // move into local storage
            else
            {
                // create the upload folder
                $uploadPathDir = $this->options['upload_dir'] . substr($newFilename, 0, 2);
                @mkdir($uploadPathDir);
                
                $file_path = $uploadPathDir . '/' . $newFilename;
                clearstatcache();
                if ($uploaded_file && is_uploaded_file($uploaded_file))
                {
                    move_uploaded_file($uploaded_file, $file_path);
                }
                $file_size = filesize($file_path);
            }

            // check filesize uploaded matches tmp uploaded
            if ($file_size === $fileUpload->size)
            {
                $fileUpload->url = $this->options['upload_url'] . rawurlencode($fileUpload->name);

                // insert into the db
                $fileUpload->size = $file_size;
                $fileUpload->delete_url = '~d?' . $this->options['delete_hash'];
                $fileUpload->info_url = '~i?' . $this->options['delete_hash'];
                $fileUpload->delete_type = 'DELETE';

                // create delete hash, make sure it's unique
                $deleteHash = md5($fileUpload->name . getUsersIPAddress() . microtime());
                $existingFile = file::loadByDeleteHash($deleteHash);
                while ($existingFile != false)
                {
                    $deleteHash = md5($fileUpload->name . getUsersIPAddress() . microtime());
                    $existingFile = file::loadByDeleteHash($deleteHash);
                }

                // store in db
                $db = Database::getDatabase(true);
                $dbInsert = new DBObject("file", array("originalFilename", "shortUrl", "fileType", "extension", "fileSize", "localFilePath", "userId", "totalDownload", "uploadedIP", "uploadedDate", "statusId", "deleteHash", "serverId"));

                $dbInsert->originalFilename = $fileUpload->name;
                $dbInsert->shortUrl = 'temp';
                $dbInsert->fileType = $fileUpload->type;
                $dbInsert->extension = $extension;
                $dbInsert->fileSize = $fileUpload->size;
                $dbInsert->localFilePath = substr($file_path, strlen($this->options['upload_dir']), 99999);

                // add user id if user is logged in
                $dbInsert->userId = NULL;
                $Auth = Auth::getAuth();
                if ($Auth->loggedIn())
                {
                    $dbInsert->userId = (int) $Auth->id;
                }

                $dbInsert->totalDownload = 0;
                $dbInsert->uploadedIP = getUsersIPAddress();
                $dbInsert->uploadedDate = sqlDateTime();
                $dbInsert->statusId = 1;
                $dbInsert->deleteHash = $deleteHash;
                $dbInsert->serverId = $uploadServerId;

                if (!$dbInsert->insert())
                {
                    $fileUpload->error = 'abort';
                }

                // create short url
                $tracker = 1;
                $shortUrl = file::createShortUrlPart($tracker . $dbInsert->id);
                $fileTmp = file::loadByShortUrl($shortUrl);
                while ($fileTmp)
                {
                    $shortUrl = file::createShortUrlPart($tracker . $dbInsert->id);
                    $fileTmp = file::loadByShortUrl($shortUrl);
                    $tracker++;
                }

                // update short url
                file::updateShortUrl($dbInsert->id, $shortUrl);

                // update fileUpload with file location
                $file = file::loadByShortUrl($shortUrl);
                $fileUpload->url = $file->getFullShortUrl();
                $fileUpload->delete_url = $file->getDeleteUrl();
                $fileUpload->info_url = $file->getInfoUrl();
                $fileUpload->stats_url = $file->getStatisticsUrl();
                $fileUpload->short_url = $shortUrl;
            }
            else if ($this->options['discard_aborted_uploads'])
            {
                //@TODO - made ftp compatible
                @unlink($file_path);
                @unlink($uploaded_file);
                if(!isset($fileUpload->error))
                {
                    $fileUpload->error = 'maxFileSize';
                }
            }
        }

        return $fileUpload;
    }

    public function get()
    {
        $file_name = isset($_REQUEST['file']) ?
                basename(stripslashes($_REQUEST['file'])) : null;
        if ($file_name)
        {
            $info = $this->get_file_object($file_name);
        } else
        {
            $info = $this->get_file_objects();
        }
        header('Content-type: application/json');
        echo json_encode($info);
    }

    public function post()
    {
        $upload = isset($_FILES[$this->options['param_name']]) ?
                $_FILES[$this->options['param_name']] : array(
            'tmp_name' => null,
            'name' => null,
            'size' => null,
            'type' => null,
            'error' => null
                );
        $info = array();
        if (is_array($upload['tmp_name']))
        {
            foreach ($upload['tmp_name'] as $index => $value)
            {
                $info[] = $this->handle_file_upload($upload['tmp_name'][$index], 
                        isset($_SERVER['HTTP_X_FILE_NAME']) ? $_SERVER['HTTP_X_FILE_NAME'] : $upload['name'][$index], 
                        isset($_SERVER['HTTP_X_FILE_SIZE']) ? $_SERVER['HTTP_X_FILE_SIZE'] : $upload['size'][$index], 
                        isset($_SERVER['HTTP_X_FILE_TYPE']) ? $_SERVER['HTTP_X_FILE_TYPE'] : $upload['type'][$index], 
                        $upload['error'][$index]
                );
            }
        } else
        {
            $info[] = $this->handle_file_upload($upload['tmp_name'], 
                    isset($_SERVER['HTTP_X_FILE_NAME']) ? $_SERVER['HTTP_X_FILE_NAME'] : $upload['name'],
                    isset($_SERVER['HTTP_X_FILE_SIZE']) ? $_SERVER['HTTP_X_FILE_SIZE'] : $upload['size'],
                    isset($_SERVER['HTTP_X_FILE_TYPE']) ? $_SERVER['HTTP_X_FILE_TYPE'] : $upload['type'],
                    $upload['error']
            );
        }
        header('Vary: Accept');
        if (isset($_SERVER['HTTP_ACCEPT']) &&
                (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false))
        {
            header('Content-type: application/json');
        } else
        {
            header('Content-type: text/plain');
        }
        echo json_encode($info);
    }

}

$upload_handler = new uploadHandler();

header('Pragma: no-cache');
header('Cache-Control: private, no-cache');
header('Content-Disposition: inline; filename="files.json"');

// check we are receiving the request from this script
if (!checkReferrer())
{
    // exit
    header('HTTP/1.0 400 Bad Request');
    exit();
}

switch ($_SERVER['REQUEST_METHOD'])
{
    case 'HEAD':
    case 'GET':
        $upload_handler->get();
        break;
    case 'POST':
        $upload_handler->post();
        break;
    default:
        header('HTTP/1.0 405 Method Not Allowed');
}
