<?php

define('DROPBOX_FOLDER', JPATH_SITE.DS."images".DS."dropbox");
define('DROPBOX_LOCK', JPATH_SITE.DS."tmp".DS."dropbox.lock");

require_once(COMPONENT_PATH.DS."dropbox".DS."OAuth".DS."Consumer".DS."ConsumerAbstract.php");
require_once(COMPONENT_PATH.DS."dropbox".DS."OAuth".DS."Consumer".DS."Curl.php");
require_once(COMPONENT_PATH.DS."dropbox".DS."OAuth".DS."Storage".DS."Encrypter.php");
require_once(COMPONENT_PATH.DS."dropbox".DS."OAuth".DS."Storage".DS."StorageInterface.php");
//require_once(JPATH_COMPONENT.DS."dropbox".DS."OAuth".DS."Storage".DS."Session.php");
require_once(COMPONENT_PATH.DS."dropbox".DS."Exception.php");
require_once(COMPONENT_PATH.DS."dropbox".DS."API.php");
require_once(COMPONENT_PATH.DS."JoomlaStorage.php");

class DropboxHelper {
    private $dropbox;
    private $dropbox_root = "";
    private $dropbox_folder = "";
    private $filter = array();
    private $protocol;
    private $callback;
    
    function __construct($key, $secret) {
        $this->protocol = (!empty($_SERVER['HTTPS'])) ? 'https' : 'http';
        $this->callback = $this->protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $encrypter = new \Dropbox\OAuth\Storage\Encrypter('XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX');
        $storage = new JoomlaStorage($encrypter);
        $OAuth = new \Dropbox\OAuth\Consumer\Curl($key, $secret, $storage, $this->callback);
        $this->dropbox = new \Dropbox\API($OAuth);
    }
    function setTargetPath($path) {
        $this->dropbox_folder = $path;
    }
    function setDropboxPath($path) {
        $this->dropbox_root = $path;
    }
    function fetchFiles($path) {
        $files = array();
        $metaData = $this->dropbox->metaData($path);
        foreach($metaData["body"]->contents as $file) {
            if($file->is_dir && !empty($file->path)) {
                $files = array_merge($files, $this->fetchFiles($file->path));
            } else {
                $files[] = $file;
            } 
        }
        return $files;
    }
    function getLocalFileListing() {
        $files = JFolder::files(DROPBOX_FOLDER, $filter = '.', true, true);
        foreach($files as &$file) {
            $file = substr($file, strlen(DROPBOX_FOLDER));
        }
        return $files;
    }
    function getRemoteFileListing($objects) {
        $files = array();
        foreach($objects as $object) {
            $files[] = $object->path;
        }
        return $files;
    }
    function Synchronize() {
        $time = time();
        $lasttime = 0;

        $response = array("removed" => 0, "added" => 0, "updated" => 0, "locked" => 0, "time" => 0);

        // read last update time
        $fp = fopen(DROPBOX_LOCK, "r");
        if ($fp) {
            $lasttime = fgets($fp);
            if (strlen($lasttime) == 0)
                $lasttime = 0;
            fclose($fp);
        }

        // reopen file to get write access
        if ($time - $lasttime > 15) {
            $fp = fopen(DROPBOX_LOCK, "w+");
        }
        if (flock($fp, LOCK_EX | LOCK_NB)) { // get lock
            $response['locked'] = true;

            $lasttime = $time;
            $targetFolder = JPATH_SITE.DS.$this->dropbox_folder;
            fputs($fp, $lasttime);
            
            if(!JFolder::exists($targetFolder)) {
                JFolder::create($targetFolder);
            }

            $localfiles = $this->getLocalFileListing();
            $remoteobjects = $this->fetchFiles($this->dropbox_root);
            $remotefiles = $this->getRemoteFileListing($remoteobjects);

            // folder, which are skips
            $filters = array("thumbs", "preview");

            function contains($str, $arr) {
                foreach ($arr as $filter) {
                    if (stripos($str, $filter) != 0) {
                        return true;
                    }
                }
                return false;
            }

            // delete files on local part, if then not exist anymore on dropbox
            $removeFiles = array_diff($localfiles, $remotefiles);
            foreach ($removeFiles as $file) {
                $path = dirname($file);
                if (contains($path, $filters))
                    continue;
                JFile::delete($targetFolder.$file);
                //echo $file.' removed<br />';
                $response['removed']++;
            }

            foreach ($localfiles as $file) {
                
            }

            // create folders and download files
            foreach ($remoteobjects as $file) {
                //echo $file->path.'<br />';
                $outFile = false;
                if (!JFolder::exists($targetFolder.dirname($file->path))) {
                    JFolder::create($targetFolder.dirname($file->path));
                    //echo dirname($file->path).' created<br />';
                }
                if (!JFile::exists($targetFolder.$file->path)) {
                    $temp = tempnam(sys_get_temp_dir(), 'db');
                    $this->dropbox->getFile($file->path, $temp);
                    JFile::move($temp, $targetFolder.$file->path);
                    //echo $file->path." downloaded<br />";
                    $response['added']++;
                } else {
                    //echo " exist<br />";
                }
            }

            $doc =& JFactory::getDocument();
            JRequest::setVar('tmpl', 'component');
            $doc->setTitle("Sync");
            $doc->setMimeEncoding('application/json');
            JResponse::setHeader('Content-Disposition', 'attachment;filename="' . basename(__FILE__, '.php') . '.json"');

            flock($fp, LOCK_UN);
        } else {
            $response['locked'] = false;
        }
        if($fp) 
            fclose ($fp);
        
        $response['time'] = date('Y-m-d H:i:s', $lasttime);
        
        return $response;
    }
}

?>
