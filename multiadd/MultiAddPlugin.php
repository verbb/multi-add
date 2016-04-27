<?php
namespace Craft;

class MultiAddPlugin extends BasePlugin
{

    protected $settings;

    public function init()
    {
        $this->settings = $this->getSettings();
    }

    /* --------------------------------------------------------------
    * PLUGIN INFO
    * ------------------------------------------------------------ */

    public function getName()
    {
        return Craft::t('Multi Add for Commerce');
    }

    public function getVersion()
    {
        return '0.1.0';
    }

    public function getSchemaVersion()
    {
        return '0.0.1';
    }

    public function getDeveloper()
    {
        return 'S. Group';
    }

    public function getDeveloperUrl()
    {
        return 'http://sgroup.com.au';
    }

    function getDocumentationUrl(){
        return 'https://github.com/engram-design/MultiAdd';
    }

    function getDescription(){
        return 'Provides an alternative controller to assist in adding multiple items to your Craft Commerce cart in one operation.';
    }

    public function getReleaseFeedUrl()
    {
        return 'https://raw.githubusercontent.com/engram-design/MultiAdd/master/releases.json';
    }

    function hasSettings(){
        return true;
    }


    public function defineSettings()
    {
        return array(
            'debug' => AttributeType::Bool,
        );
    }

    public function getSettingsHtml()
    {

        $settings = $this->settings;

        $variables = array(
            'name'     => $this->getName(true),
            'version'  => $this->getVersion(),
            'settings' => $settings,
        );

        return craft()->templates->render('multiadd/_settings', $variables);

   }

    /* --------------------------------------------------------------
    * HOOKS
    * ------------------------------------------------------------ */
 
}
