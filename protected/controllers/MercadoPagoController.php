<?php

/**
 * Url for moip ruturn http://ip/billing/index.php/pagSeguro .
 * https://pagseguro.uol.com.br/preferences/automaticReturn.jhtml
 */
class MercadoPagoController extends CController
{
    public $config;

    public function actionIndex()
    {
        Yii::log('mercadaoPago' . print_r($_POST, true), 'error');

        require_once 'lib/mercadopago/mercadopago.php';

        $modelMethodpay = Methodpay::model()->find('payment_method = :key AND id_user = 1', array(':key' => 'MercadoPago'));

        $mp = new MP($modelMethodpay->username, $modelMethodpay->pagseguro_TOKEN);

        if (!isset($_GET["id"], $_GET["topic"]) || !ctype_digit($_GET["id"])) {
            http_response_code(400);
            return;
        }

        $topic               = $_GET["topic"];
        $merchant_order_info = null;

        switch ($topic) {
            case 'payment':
                $payment_info        = $mp->get("/collections/notifications/" . $_GET["id"]);
                $merchant_order_info = $mp->get("/merchant_orders/" . $payment_info["response"]["collection"]["merchant_order_id"]);
                break;
            case 'merchant_order':
                $merchant_order_info = $mp->get("/merchant_orders/" . $_GET["id"]);
                break;
            default:
                $merchant_order_info = null;
        }

        Yii::log('mercadaoPago' . print_r($merchant_order_info, true), 'error');

        if ($merchant_order_info == null) {
            echo "Error obtaining the merchant_order";
            die();
        }

        if ($merchant_order_info["status"] == 200) {

            if (isset($merchant_order_info["response"]['payments'][0]['status']) && $merchant_order_info["response"]['payments'][0]['status'] == 'approved') {
                $amount   = $merchant_order_info["response"]['items'][0]['unit_price'];
                $username = explode("-", $merchant_order_info["response"]['items'][0]['title']);
                $username = addslashes(strip_tags(trim($username[1])));

                $code        = $merchant_order_info["response"]['payments'][0]['id'];
                $description = "Pagamento confirmado, MERCADOPAGO:" . $code;
                $modelUser   = User::model()->find('username = :key', array(':key', $username));
                if (count($resultUser) > 0) {
                    UserCreditManager::releaseUserCredit($modelUser->id, $amount, $description, 1, $code);
                }

            }
        }
    }
}
