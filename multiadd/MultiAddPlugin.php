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
        return '0.2.1';
    }

    public function getSchemaVersion()
    {
        return '0.0.1';
    }

    public function getDeveloper()
    {
        return 'Verbb';
    }

    public function getDeveloperUrl()
    {
        return 'https://verbb.io';
    }

    public function getDescription()
    {
        return 'Provides an alternative controller to assist in adding multiple items to your Craft Commerce cart in one operation.';
    }

    public function getPluginUrl()
    {
        return 'https://github.com/verbb/multi-add';
    }

    public function getDocumentationUrl()
    {
        return 'https://verbb.io/craft-plugins/multi-add/docs';
    }

    public function getReleaseFeedUrl()
    {
        return 'https://raw.githubusercontent.com/verbb/multi-add/craft-2/releases.json';
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
