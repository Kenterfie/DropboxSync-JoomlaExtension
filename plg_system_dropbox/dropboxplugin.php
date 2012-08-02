<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

class plgSystemDropBoxPlugin extends JPlugin {

    function plgSystemDropBoxPlugin(&$subject, $params) {
        parent::__construct($subject, $params);
    }

    function onBeforeRender() {
        $app = JFactory::getApplication();
        $admin = $app->isAdmin();

        if (!$admin) {
            $doc = & JFactory::getDocument();
            $doc->addScript(JURI::root() . "components/com_dropbox/assets/js/dropbox.js");
        }
    }

}
?>