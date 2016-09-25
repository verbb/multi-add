<?php
namespace Craft;

class MultiAddPlugin extends BasePlugin
{
    protected static $settings;

    // =========================================================================
    // PLUGIN INFO
    // =========================================================================

    public function getName()
    {
        return Craft::t('Multi Add for Commerce');
    }

    public function getVersion()
    {
        return '0.1.8';
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

    public function getDocumentationUrl()
    {
        return 'https://github.com/engram-design/MultiAdd';
    }

    public function getDescription()
    {
        return 'Provides an alternative controller to assist in adding multiple items to your Craft Commerce cart in one operation.';
    }

    public function getReleaseFeedUrl()
    {
        return 'https://raw.githubusercontent.com/engram-design/MultiAdd/master/releases.json';
    }

    public function hasSettings()
    {
        return true;
    }

    public function defineSettings()
    {
        return array(
            'debug'     => AttributeType::Bool,
            'debugPOST' => AttributeType::Bool,
        );
    }

    public function getSettingsHtml()
    {
        $settings = self::$settings;

        $variables = array(
            'name'          => $this->getName(),
            'description'   => $this->getDescription(),
            'version'       => $this->getVersion(),
            'settings'      => $settings,
        );

        return craft()->templates->render('multiadd/_settings', $variables);
   }

    public function init()
    {
        self::$settings = $this->getSettings();
    }


    // =========================================================================
    // PLUGIN LOGGING
    // =========================================================================

    public static function logError($msg)
    {
        MultiAddPlugin::log($msg, LogLevel::Error, $force = true);
    }

    public static function logWarning($msg)
    {
        MultiAddPlugin::log($msg, LogLevel::Warning, $force = true);
    }

    // If debugging is set to true in this plugin's settings, then log every message, devMode or not.
    public static function log($msg, $level = LogLevel::Profile, $force = false)
    {
        if (self::$settings['debug']) {
            $force = true;
        }

        if (is_string($msg)) {
            $msg = "\n\n" . $msg . "\n";
        } else {
            $msg = "\n\n" . print_r($msg, true) . "\n";
        }

        parent::log($msg, $level, $force);
    }
}
