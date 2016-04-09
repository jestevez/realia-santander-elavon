<?php

/*
  Plugin Name: Módulo de Pago TPV Virtu@l Santander Elavon
  Plugin URI: https://github.com/jestevez/realia-santander-elavon
  Version: 0.1.0
  Description: Este plugins permite el cobro de pedidos a través de tarjetas de crédito en tiendas que funcionen con el plugin inmobiliario realia para wordpress.
  Date: 04 Abril 2016
  Author: jestevez / ljavierrodriguez
  Author URI: https://github.com/jestevez/realia-santander-elavon
  Text Domain: realia-santander-elavon
  Domain Path: /languages/
  License: GNU General Public License v3.0
  License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

/*
  Note: Este plugins requiere el plugins inmobiliario de Realia http://wprealia.com
 */

if (!class_exists('Realia_Santander') && class_exists('Realia')) {

    /**
     * Class Realia_Santander
     *
     * @class Realia_Santander
     * @package Realia_Santander
     * @author jestevez
     */
    final class Realia_Santander {

        /**
         * Inicializar Realia Santander plugins
         */
        public function __construct() {
            $this->constants();
            $this->includes();
            $this->load_plugin_textdomain();
        }

        /**
         * Definir las constantes
         *
         * @access public
         * @return void
         */
        public function constants() {
            define('REALIA_SANTANDER_DIR', plugin_dir_path(__FILE__));
        }

        /**
         * Incluir clases
         *
         * @access public
         * @return void
         */
        public function includes() {
            require_once REALIA_SANTANDER_DIR . 'includes/class-realia-santander-customizations.php';

            if (self::is_santander_enabled()) {
                require_once REALIA_SANTANDER_DIR . 'includes/class-realia-santander-logic.php';
                require_once REALIA_SANTANDER_DIR . 'includes/class-realia-santander-response.php';
            }
        }

        /**
         * Carga los archivos de i18n
         *
         * @access public
         * @return void
         */
        public function load_plugin_textdomain() {
            load_plugin_textdomain('realia-santander', false, plugin_basename(dirname(__FILE__)) . '/languages');
        }

        /**
         * Esta función comprueba si está configurado los valores necesarios para que el plugins funcione
         *
         * @access public
         * @return bool
         */
        public static function is_santander_enabled() {
            $client_id = get_theme_mod('realia_santander_client_id', null);
            $client_secret = get_theme_mod('realia_santander_client_secret', null);

            if (!empty($client_id) && !empty($client_secret)) {
                return true;
            }
            return false;
        }

    }

    new Realia_Santander();
}

/**
 * Este shortcode es usado para crear la pagina de respuesta
 * @return type
 */
function shortcode_payment_response() {
    return Realia_Santander_Response::process_response();
}

add_shortcode('santander_page_response', 'shortcode_payment_response');
