<?php
// No direct access
defined('_JEXEC') or die;

include_once(COMPONENT_PATH.DS."dropbox.inc.php");

jimport('joomla.application.component.view');

class DropBoxSyncViewList extends JView {

    function display($tpl = null) {
        $app = JFactory::getApplication();
        $doc =& JFactory::getDocument();
        
        JHTML::_('behavior.mootools');
        
        $script = '
        Joomla.submitbutton = function(task) {
            switch(task) {
                case "sync":
                    Joomla.systemMessage("test");
                    break;
                default:
                    Joomla.submitform(task);
            }
        }';
        $doc->addScript(JURI::root() . "components/com_dropbox/assets/js/joomla.message.js");
        $doc->addScriptDeclaration($script);
        
        $language = JFactory::getLanguage();
        $language->load('com_dropbox', JPATH_SITE, 'en-GB', true);
        $language->load('com_dropbox', JPATH_SITE, null, true);

        $this->addToolBar();
        
        $params = &JComponentHelper::getParams('com_dropbox');
        $key = $params->get('dropbox_api_key');
        $secret = $params->get('dropbox_api_secret');
        $folder = $params->get('dropbox_folder');
        
        $dropboxActive = false;

        try {
            $dropboxHelper = new DropboxHelper($key, $secret);
            $dropboxHelper->setTargetPath($folder);

            $localfiles = $dropboxHelper->getLocalFileListing();
            $remoteobjects = $dropboxHelper->fetchFiles("");
            $remotefiles = $dropboxHelper->getRemoteFileListing($remoteobjects);
            
            $dropboxActive = true;
        } catch (Dropbox\Exception $e) {

        } catch (Exception $e) {
            var_dump($e);
        }
        ?>
    <table class="adminlist">
    <thead>
        <tr>
            <th width="1%">Status</th>
            <th>Filename</th>
            <th>Folder</th>
        </tr>
    </thead>
    <tbody>
        <?php
            function cmpFile($a, $b) {
                return strcasecmp(basename($a), basename($b));
            }
            //$app->enqueueMessage("test");
            uasort($remotefiles, 'cmpFile');
            if($dropboxActive)
                foreach($remotefiles as $file):
                    $pi = pathinfo($file);
        ?>
        <tr>
            <td class="jgrid center"><span class="state publish"></span></td>
            <td class="nowrap"><?php echo $pi['basename']; ?></td>
            <td><?php echo $pi['dirname']; ?></td>
        </tr>
        <?php
                endforeach;
        ?>
    </tbody>
</table>
<?php
    }
    
    protected function addToolBar() {
        JToolBarHelper::title('DropBox Sync');
        JToolBarHelper::addNew('sync', 'Sync');
        JToolBarHelper::preferences('com_dropbox');
    }

}
