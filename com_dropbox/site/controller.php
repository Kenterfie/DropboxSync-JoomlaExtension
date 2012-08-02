<?php

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

class DropBoxSyncController extends JController {

    public function display($cachable = false, $urlparams = false) {
        $view = JRequest::getCmd('view', 'list');
        $task = JRequest::getCmd('task', '');
        $layout = JRequest::getVar('layout');
        
        JRequest::setVar('view', $view);

        return parent::display();
    }

}
