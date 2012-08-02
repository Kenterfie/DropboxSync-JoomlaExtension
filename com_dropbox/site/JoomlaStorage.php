<?php
jimport( 'joomla.application.component.helper' );

class JoomlaStorage implements Dropbox\OAuth\Storage\StorageInterface
{
    /**
        * Session namespace
        * @var string
        */
    private $namespace = 'dropbox_api';

    /**
        * Encyption object
        * @var Encrypter|null
        */
    private $encrypter = null;

    /**
        * Check if a session has been started and if an instance
        * of the encrypter is passed, set the encryption object
        * @return void
        */
    public function __construct(\Dropbox\OAuth\Storage\Encrypter $encrypter = null)
    {
        if($encrypter instanceof \Dropbox\OAuth\Storage\Encrypter){
            $this->encrypter = $encrypter;
        }
    }

    /**
        * Set the session namespace
        * $namespace corresponds to $_SESSION[$namespace] 
        * @param string $namespace
        * @return void
        */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
        * Get an OAuth token from the session
        * If the encrpytion object is set then
        * decrypt the token before returning
        * @return array|bool
        */
    public function get($type)
    {
        if($type != 'request_token' && $type != 'access_token') {
            throw new \Dropbox\Exception("Expected a type of either 'request_token' or 'access_token', got '$type'");
        } else {
            //$app = JFactory::getApplication();
            $params = &JComponentHelper::getParams('com_dropbox');
            $token =  $params->get("dropbox_token", null);
            
            if(empty($token))
                return false;
            
            $token = unserialize($token);
            
            return $token[$type];
//            if(JFile::exists(JOOMLA_TEMP."/dropbox.token")) {
//                $t = JFile::read(JOOMLA_TEMP."/dropbox.token");
//
//                /*if($this->encrypter instanceof \Dropbox\OAuth\Storage\Encrypter) {
//                    return $this->encrypter->decrypt($t);
//                }*/
//
//                $t = unserialize($t);
//
//                $token = $t[$type];
//
//                return $token;
//            }
        }
    }

    /**
        * Set an OAuth token in the session by type
        * If the encryption object is set then
        * encrypt the token before storing
        * @return void
        */
    public function set($token, $type) {
        if($type != 'request_token' && $type != 'access_token') {
            throw new \Dropbox\Exception("Expected a type of either 'request_token' or 'access_token', got '$type'");
        } else {
            $params = &JComponentHelper::getParams('com_dropbox');
            
            $t = $params->get("dropbox_token", null);
            if(empty($t)) {
                $t = array();
            } else {
                $t = unserialize($t);
            }
            
            $t[$type] = $token;
            $token = serialize($t);
            $params->set("dropbox_token", $token);
            $this->setParams($params->toArray());
            
            //$params->set("dropbox_token", $token);
            //$params->
//            if(JFile:exists(JOOMLA_TEMP."/dropbox.token")) {
//                $t = JFile::read(JOOMLA_TEMP."/dropbox.token");
//
//                /*if($this->encrypter instanceof \Dropbox\OAuth\Storage\Encrypter) {
//                    return $this->encrypter->decrypt($t);
//                }*/
//
//                $t = unserialize($t);
//            } else {
//                $t = array();
//            }
//
//            $t[$type] = $token;
//
//            $token = serialize($t);
//
//            /*if($this->encrypter instanceof \Dropbox\OAuth\Storage\Encrypter) {
//                $token = $this->encrypter->encrypt($token);
//            }*/
//
//            JFile::write(JOOMLA_TEMP."/dropbox.token", $token);
        }
    }
    
    function setParams($param_array) {
        if (count($param_array) > 0) {
            // read the existing component value(s)
            $db = JFactory::getDbo();
            $db->setQuery('SELECT params FROM #__extensions WHERE name = "com_dropbox"');
            $params = json_decode($db->loadResult(), true);
            // add the new variable(s) to the existing one(s)
            foreach ($param_array as $name => $value) {
                $params[(string) $name] = (string) $value;
            }
            // store the combined new and existing values back as a JSON string
            $paramsString = json_encode($params);
            $db->setQuery('UPDATE #__extensions SET params = ' .
                    $db->quote($paramsString) .
                    ' WHERE name = "com_dropbox"');
            $db->query();
        }
    }
}
?>
