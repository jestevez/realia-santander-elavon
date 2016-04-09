<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Realia_Santander_Logic
 *
 * @class Realia_Santander_Logic
 * @package Realia_Santander/Classes
 * @author ljavierrodriguez
 */
class Realia_Santander_Logic {

    /**
     * Inicializar la funcionalidad de Santander Evalon
     *
     * @access public
     * @return void
     */
    public static function init() {
        add_action('init', array(__CLASS__, 'process_payment'), 9999);
        add_filter('realia_payment_gateways', array(__CLASS__, 'payment_gateways'));
    }

    /**
     * Añadir a pasarelas de pagos
     *
     * @access public
     * @param array $gateways
     * @return array
     */
    public static function payment_gateways($gateways) {
        $data = self::get_data($_POST['payment_type'], $_POST['object_id']);
        $_SESSION["SANTANDER_PRICE"] = round($data['price'], 2) * 100;
        $_SESSION["SANTANDER_CURRENCY_CODE"] = $data['currency_code'];
        $_SESSION["SANTANDER_PAYMENT_TYPE"] = $_POST['payment_type'];
        $_SESSION["SANTANDER_OBJECT_ID"] = $_POST['object_id'];

        $timestamp = strftime("%Y%m%d%H%M%S");
        mt_srand((double) microtime() * 1000000);
        $orderid = $timestamp . "-" . mt_rand(1, 999);

        $transaction_id = self::create_transaction($data, $_POST['payment_type'], $_POST['object_id'], $orderid);

        $_SESSION["SANTANDER_ORDER_ID"] = $orderid;
        $_SESSION["SANTANDER_TRANSACTION_ID"] = $transaction_id;

        if (Realia_Santander::is_santander_enabled()) {
            if (get_theme_mod('realia_santander_credit_card', false) == '1') {
                $gateways[] = array(
                    'id' => 'santander-credit-card',
                    'title' => __('Pagar con tarjeta de débito o crédito', 'realia-credit-card'),
                    'proceed' => true,
                    'content' => Realia_Template_Loader::load('santander/credit-card-form', array(), REALIA_SANTANDER_DIR),
                );
            }
        }

        return $gateways;
    }

    public static function create_transaction($data, $payment_type, $object_id, $orderid) {
        //Obtener la ultima transacción para obtener el último número correlativo
        $queryTrans = new WP_Query(array(
            'post_type' => 'transaction',
            'posts_per_page' => 1,
            'orderby' => 'post_date',
            'order' => 'DESC',
            'meta_query' => array(
                'key' => REALIA_TRANSACTION_PREFIX . 'object',
                'value' => 'success',
                'compare' => 'LIKE',
            )
                )
        );
        if ($queryTrans->have_posts())
            $queryTrans->the_post();

        $numFactura = get_post_meta(get_the_ID(), REALIA_TRANSACTION_PREFIX . 'numfactura', true);

        if ($numFactura == "")
            $numFactura = 1;
        else
            $numFactura = $numFactura + 1;

        // Crear una nueva transacion
        $transaction_id = wp_insert_post(array(
            'post_type' => 'transaction',
            'post_title' => $orderid, //date( get_option( 'date_format' ), strtotime( 'today' ) ),
            'post_status' => 'publish',
            'post_author' => get_current_user_id(),
        ));

        $object = array(
            'success' => 'false',
            'price' => $data['price'],
            'price_formatted' => $data['price_formatted'],
            'currency_code' => $data['currency_code'],
            'currency_sign' => $data['currency_sign'],
            'title' => $orderid,
            'description' => $data['description'],
            'orderid' => $orderid,
        );

        update_post_meta($transaction_id, REALIA_TRANSACTION_PREFIX . 'object', serialize($object));
        update_post_meta($transaction_id, REALIA_TRANSACTION_PREFIX . 'object_id', $object_id);
        update_post_meta($transaction_id, REALIA_TRANSACTION_PREFIX . 'payment_type', $payment_type);
        update_post_meta($transaction_id, REALIA_TRANSACTION_PREFIX . 'numfactura', $numFactura);

        update_post_meta($transaction_id, REALIA_TRANSACTION_PREFIX . 'razonsocial', get_the_author_meta('razonsocial_profile', get_current_user_id()));
        update_post_meta($transaction_id, REALIA_TRANSACTION_PREFIX . 'dnicif', get_the_author_meta('dnicif_profile', get_current_user_id()));
        update_post_meta($transaction_id, REALIA_TRANSACTION_PREFIX . 'direccion', get_the_author_meta('direccion_profile', get_current_user_id()));
        update_post_meta($transaction_id, REALIA_TRANSACTION_PREFIX . 'cp', get_the_author_meta('cp_profile', get_current_user_id()));
        update_post_meta($transaction_id, REALIA_TRANSACTION_PREFIX . 'ciudad', get_the_author_meta('ciudad_profile', get_current_user_id()));

        return $transaction_id;
    }

    public static function get_data($payment_type, $object_id) {
        $data = array();
        $post = get_post($object_id);
        $currencies = get_theme_mod('realia_currencies', array());

        if (!empty($currencies) && is_array($currencies)) {
            $currency = array_shift($currencies);
            $currency_code = $currency['code'];
            $currency_sign = $currency['symbol'];
        } else {
            $currency_code = 'USD';
            $currency_sign = '$';
        }

        switch ($payment_type) {
            case 'pay_for_featured':
                $price = get_theme_mod('realia_submission_featured_price');
                $data = array(
                    'title' => __('Feature property', 'realia'),
                    'description' => sprintf(__('Feature property %s', 'realia'), $post->post_title),
                    'price' => $price,
                    'price_formatted' => Realia_Price::format_price($price),
                    'currency_code' => $currency_code,
                    'currency_sign' => $currency_sign,
                );
                break;
            case 'pay_for_sticky':
                $price = get_theme_mod('realia_submission_sticky_price');

                $data = array(
                    'title' => __('Sticky property', 'realia'),
                    'description' => sprintf(__('Sticky property %s', 'realia'), $post->post_title),
                    'price' => $price,
                    'price_formatted' => Realia_Price::format_price($price),
                    'currency_code' => $currency_code,
                    'currency_sign' => $currency_sign,
                );
                break;
            case 'pay_per_post':
                $price = get_theme_mod('realia_submission_pay_per_post_price');
                $data = array(
                    'title' => __('Publish property', 'realia'),
                    'description' => sprintf(__('Publish property %s', 'realia'), $post->post_title),
                    'price' => $price,
                    'price_formatted' => Realia_Price::format_price($price),
                    'currency_code' => $currency_code,
                    'currency_sign' => $currency_sign,
                );
                break;
            case 'package':
                $price = get_post_meta($object_id, REALIA_PACKAGE_PREFIX . 'price', true);

                $data = array(
                    'title' => __('Purchase package', 'realia'),
                    'description' => sprintf(__('Upgrade package to %s', 'realia'), $post->post_title),
                    'price' => $price,
                    'price_formatted' => Realia_Price::format_price($price),
                    'currency_code' => $currency_code,
                    'currency_sign' => $currency_sign,
                );
                break;
            default:
                return false;
        }

        return $data;
    }

}

Realia_Santander_Logic::init();
