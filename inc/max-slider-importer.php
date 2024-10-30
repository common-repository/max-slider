<?php

/**
 * Max Slider Demo Importer
 */

if (class_exists('Max_Slider_Pro')) {
    return;
};

class MaxSliderDemoFiles
{
    private $settings;
    private $demosUrl;

    public function __construct($settings)
    {
        $this->settings = $settings;
        $this->demosUrl = 'https://maxslider.maxech.com/demos';
    }

    public function getImportFiles()
    {
        $response = wp_remote_get($this->demosUrl . '/demos.json');

        if (is_array($response) && !is_wp_error($response)) {
            $body = wp_remote_retrieve_body($response);
            $demoNames = json_decode($body, true);

            if ($demoNames !== null) {
                $options = [];

                foreach ($demoNames as $demoName) {
                    $isInner = strpos(strtolower($demoName), 'inner') !== false;
                    $categories = $isInner ? ['Inner Pages'] : ['Home Templates'];

                    $singleImport = [
                        'title' => ucwords(str_replace('-', ' ', $demoName)),
                        'preview_url' => 'https://maxslider.maxech.com/' . $demoName,
                        'preview_image' => $this->demosUrl . '/' . strtolower(str_replace(' ', '-', $demoName)) . '/screenshot.png',
                    ];

                    $options[$demoName] = $singleImport;
                }

                return $options;
            } else {
                error_log('Failed to fetch data from ' . $this->demosUrl);
                return [];
            }
        } else {
            error_log('Failed to fetch data from ' . $this->demosUrl);
            return [];
        }
    }

    public function init()
    {
        require_once('demo-importer/init.php'); // Include the file containing the Max_Slider_Demo_Importer class
        $importFiles = $this->getImportFiles();
        Max_Slider_Demo_Importer::instance($this->settings, $importFiles);
    }
}

$settings = [
    'menu_parent' => 'edit.php?post_type=max_slides',
    'menu_title' => __('Demo Importer', 'max-slider'),
    'menu_type' => 'add_submenu_page',
    'menu_slug' => 'max_slider_demo_importer',
];

$MaxSliderDemoFiles = new MaxSliderDemoFiles($settings);
$MaxSliderDemoFiles->init();
