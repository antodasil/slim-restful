<?php

namespace SlimRestful;

use DI\Container;
use DI\ContainerBuilder;

class SettingsManager {

    /**
     * @var SettingsManager|null $instance
     */
    protected static ?SettingsManager $instance = null;
    /**
     * @var array $settings
     */
    protected array $settings;

    /**
     * __construct
     */
    private function __construct() {
        $this->settings = array();
    }

    /**
     * SINGLETON
     * 
     * @return SettingsManager
     */
    public static function getInstance(): SettingsManager {
 
        if(is_null(static::$instance)) {
          static::$instance = new SettingsManager();
        }
    
        return static::$instance;
    }

    /**
     * Get a setting value
     * 
     * @param string $name setting name
     * 
     * @return mixed|null
     */
    public function get(string $name) {
        return array_key_exists($name, $this->settings) ? $this->settings[$name] : null;
    }

    /**
     * Add settings to the SettingsManager
     * 
     * @param array settings array
     * 
     * @return void
     */
    public function addSettings(array $settings): void {
        $this->settings = array_merge($settings, $this->settings);
    }

    /**
     * Load settings from .ini/.json file
     * 
     * @param string $filename
     * 
     * @throws SettingsException
     */
    public function load(string $filename) {

        $settingsManager = static::getInstance();

        if (file_exists($filename)) {
            $extension = pathinfo($filename)['extension'];
            
            switch ($extension) {
                case 'ini': 
                    $params = parse_ini_file($filename, true);
                    break;
                case 'json': 
                    $params = json_decode(file_get_contents($filename), true);
                    break;
                default: 
                    throw new SettingsException('Config file must be of ini or json type');
                    break;
                }

            $settingsManager->addSettings($params);

        } else {
            throw new SettingsException("Failed to load config file: $filename.");
        }

        return $this;
    }

    /**
     * Get Container from settings
     * 
     * @return Container
     */
    public function getContainer(): Container {
        $builder = new ContainerBuilder();
        $isDev = $this->get('application')['environment'] === 'development'
            || $this->get('environment') === 'development';

        $builder->addDefinitions(array(
            'settings' => array_merge(
                array(
                    'displayErrorDetails' => $isDev,
                    'determineRouteBeforeAppMiddleware' => true,
                ),
                $this->get('containerSettings')
            )
        ));
        return $builder->build();
    }
}