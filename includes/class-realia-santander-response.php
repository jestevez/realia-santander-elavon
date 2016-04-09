<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Realia_Santander_Response
 *
 * @class Realia_Santander_Response
 * @package Realia_Santander/Classes
 * @author jestevez
 */
class Realia_Santander_Response {

    public static function process_response() {

        /*
         * Los valores son recibidos desde el banco usando POST
         */
        if ($_POST) {
            // Respuesta del banco
            $timestamp = $_POST['TIMESTAMP'];
            $result = $_POST['RESULT'];
            $orderid = $_POST['ORDER_ID'];
            $message = $_POST['MESSAGE'];
            $authcode = $_POST['AUTHCODE'];
            $pasref = $_POST['PASREF'];
            $realexsha1 = $_POST['SHA1HASH'];

            // Variables enviadas por el cliente
            $object_id = $_POST["SANTANDER_ORDER_ID"];
            $payment_type = $_POST["SANTANDER_PAYMENT_TYPE"];
            $transaction_id = $_POST["SANTANDER_TRANSACTION_ID"];

            // Obtengo los parametros de configuracion
            $merchantid = get_theme_mod('realia_santander_merchant_id', null);
            $account = get_theme_mod('realia_santander_client_id', null);
            $secret = get_theme_mod('realia_santander_client_secret', null);
            $url = get_theme_mod('realia_santander_url', null);

            // Calculo del HASH recibido
            $tmp = "$timestamp.$merchantid.$orderid.$result.$message.$pasref.$authcode";
            $sha1hash = sha1($tmp);
            $tmp = "$sha1hash.$secret";
            $sha1hash = sha1($tmp);

            //Comprueba si las firmas coinciden o no
            if ($sha1hash != $realexsha1) {
                $_SESSION['messages'][] = array('danger', __("Hashes don't match - response not authenticated!", 'realia'));
                return "Hashes don't match - response not authenticated!";
            }

            $object = unserialize(get_post_meta($transaction_id, REALIA_TRANSACTION_PREFIX . 'object', true));

            $object['response_bank'] = $_POST;
            $object['orderid_bank'] = $orderid;

            // Si la transacion fue exitosa el resultado es 00
            if ($result == "00") {
                $object['success'] = 'true';
                update_post_meta($transaction_id, REALIA_TRANSACTION_PREFIX . 'object', serialize($object));

                $post = get_post($object_id);
                // Activo el paquete seleccionado por el cliente
                switch ($payment_type) {
                    case 'pay_for_featured':
                        update_post_meta($post->ID, REALIA_PROPERTY_PREFIX . 'featured', 'on');
                        $_SESSION['messages'][] = array('success', __('Property has been featured.', 'realia'));
                        break;
                    case 'pay_for_sticky':
                        update_post_meta($post->ID, REALIA_PROPERTY_PREFIX . 'sticky', 'on');
                        $_SESSION['messages'][] = array('success', __('Property has been sticked.', 'realia'));
                        break;
                    case 'pay_per_post':
                        $review_before = get_theme_mod('realia_submission_review_before', false);

                        if (!$review_before) {
                            wp_publish_post($post->ID);
                            $_SESSION['messages'][] = array('success', __('Property has been published.', 'realia'));
                        } else {
                            $_SESSION['messages'][] = array('success', __('Property will be published after review.', 'realia'));
                        }

                        break;
                    case 'package':
                        Realia_Packages::set_package_for_user(get_current_user_id(), $post->ID);
                        $_SESSION['messages'][] = array('success', __('Package has been upgraded.', 'realia'));
                        break;
                    default:
                        $_SESSION['messages'][] = array('danger', __('Undefined payment type.', 'realia'));
                        print_r($_SESSION);
                        print_r($_POST);
                        return __('Undefined payment type.', 'realia');
                }

                $_SESSION['messages'][] = array('success', __('Gracias por su pago. ' . $message, 'realia'));
                return "Gracias por su pago<br/>" . $message . "<br/>Para continuar navegando por favor haga <a href='" . site_url() . "'><b><u>clic aquí</u></b></a>";
            } else {
                $object['success'] = 'false';
                $_SESSION['messages'][] = array('danger', __('Hubo un error procesando su pago. ' . $message, 'realia'));
                return "
Hubo un error procesando su suscripción.<br/>
" . $message . "<br/>
Para intentarlo de nuevo por favor haga <a href='" . site_url() . "'><b><u>clic aquí</u></b></a><br><BR>
Por favor, contacte con nuestro departamento de atención al cliente en 
<a href='mailto:info.hablemosdenegocios@gmail.com'><b><u>info.hablemosdenegocios@gmail.com</u></b></a>
o si lo prefiere, contáctenos telefónicamente en el +34 91 621 60 67
";
            }
        } else {
            return 'Respuesta HTTP no valida!';
        }
        return 'Módulo de Pago TPV Virtu@l Santander Elavon';
    }

}
