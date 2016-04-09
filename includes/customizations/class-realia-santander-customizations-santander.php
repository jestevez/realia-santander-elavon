<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Realia_Santander_Customizations_Santander
 *
 * @class Realia_Santander_Customizations_Santander
 * @author ljavierrodriguez
 */
class Realia_Santander_Customizations_Santander {

    /**
     * Inicializar la clase personalización
     *
     * @access public
     * @return void
     */
    public static function init() {
        add_action('customize_register', array(__CLASS__, 'customizations'));
    }

    /**
     * Personalizaciones
     *
     * @access public
     * @param object $wp_customize
     * @return void
     */
    public static function customizations($wp_customize) {
        $wp_customize->add_section('realia_santander', array(
            'title' => __('Realia Santander Elavon', 'realia-santander'),
            'priority' => 1,
        ));

        // Santander Client ID
        $wp_customize->add_setting('realia_santander_client_id', array(
            'default' => null,
            'crability' => 'edit_theme_options',
            'sanitize_callback' => 'sanitize_text_field',
        ));

        $wp_customize->add_control('realia_santander_client_id', array(
            'label' => __('Account', 'realia-santander'),
            'section' => 'realia_santander',
            'settings' => 'realia_santander_client_id',
        ));

        // Santander Merchant ID
        $wp_customize->add_setting('realia_santander_merchant_id', array(
            'default' => null,
            'crability' => 'edit_theme_options',
            'sanitize_callback' => 'sanitize_text_field',
        ));

        $wp_customize->add_control('realia_santander_merchant_id', array(
            'label' => __('Merchant ID', 'realia-santander'),
            'section' => 'realia_santander',
            'settings' => 'realia_santander_merchant_id',
        ));


        // Santander Shared Secret
        $wp_customize->add_setting('realia_santander_client_secret', array(
            'default' => null,
            'capability' => 'edit_theme_options',
            'sanitize_callback' => 'sanitize_text_field',
        ));

        $wp_customize->add_control('realia_santander_client_secret', array(
            'label' => __('Secret', 'realia-santander'),
            'section' => 'realia_santander',
            'settings' => 'realia_santander_client_secret',
        ));


        // Santander URL
        $wp_customize->add_setting('realia_santander_url', array(
            'default' => null,
            'capability' => 'edit_theme_options',
            'sanitize_callback' => 'sanitize_text_field',
        ));

        $wp_customize->add_control('realia_santander_url', array(
            'label' => __('TPV URL', 'realia-santander'),
            'section' => 'realia_santander',
            'settings' => 'realia_santander_url',
        ));
    }

}

realia_Santander_Customizations_Santander::init();
