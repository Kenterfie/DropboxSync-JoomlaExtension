<?php
/**
 * Author:	Stefan Reusch
 * Email:	kenterfie@kenterfie.de
 * Website:	http://www.kenterfie.de
 * Component:   DropBox Sync
 * Version:	1.0.0
 * Date:	7/30/2012
 * copyright	Copyright (C) 2012 http://www.kenterfie.de. All Rights Reserved.
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');
define('COMPONENT_PATH', JPATH_SITE . DS . 'components' . DS . 'com_dropbox');

$app = JFactory::getApplication();
$admin = $app->isAdmin();

$controller = JController::getInstance('DropBoxSync');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();
?>