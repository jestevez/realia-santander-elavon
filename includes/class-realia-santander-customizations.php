<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Realia_Customizations
 *
 * @access public
 * @package Realia_Paypal/Classes/Customizations
 * @return void
 * @author ljavierrodriguez
 */
class Realia_Santander_Customizations {

    /**
     * Inicializar la clase personalización
     *
     * @access public
     * @return void
     */
    public static function init() {
        self::includes();
    }

    /**
     * Incluir todas las personalizaciones
     *
     * @access public
     * @return void
     */
    public static function includes() {
        require_once REALIA_SANTANDER_DIR . 'includes/customizations/class-realia-santander-customizations-santander.php';
    }

}

Realia_Santander_Customizations::init();
