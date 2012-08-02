<?php

// No direct access
defined('_JEXEC') or die;

include_once(COMPONENT_PATH.DS."dropbox.inc.php");

jimport('joomla.html.parameter');
jimport('joomla.application.component.view');

class DropBoxSyncViewSync extends JView {

    function display($tpl = null) {
        $app = JFactory::getApplication();

        $params = &JComponentHelper::getParams('com_dropbox');
        $key = $params->get('dropbox_api_key');
        $secret = $params->get('dropbox_api_secret');
        $folder = $params->get('dropbox_folder');

        try {
            $dropboxHelper = new DropboxHelper($key, $secret);
            $dropboxHelper->setTargetPath($folder);
            echo json_encode($dropboxHelper->Synchronize());
        } catch (Exception $e) {
            var_dump($e);
        }
        $app->close();
    }

}
