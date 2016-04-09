<?php
// Obtengo los valores personalizados
$merchantid = get_theme_mod( 'realia_santander_merchant_id', null );
$account    = get_theme_mod( 'realia_santander_client_id', null );
$secret = get_theme_mod( 'realia_santander_client_secret', null );
$url = get_theme_mod( 'realia_santander_url', null );

$timestamp = strftime("%Y%m%d%H%M%S");
$orderid = $_SESSION["SANTANDER_ORDER_ID"];

$curr = "EUR";
$amount = $_SESSION["SANTANDER_PRICE"];

//Generar una firma digital utilizando el algoritmo SHA1
$tmp_sha1hash = sha1("$timestamp.$merchantid.$orderid.$amount.$curr");
$sha1hash = sha1("$tmp_sha1hash.$secret");
?>

<input type=hidden name="MERCHANT_ID" value="<?=$merchantid?>">
<input type=hidden name="ACCOUNT" value="<?=$account?>">
<input type=hidden name="ORDER_ID" value="<?=$orderid?>">
<input type=hidden name="CURRENCY" value="<?=$curr?>">
<input type=hidden name="AMOUNT" value="<?=$amount?>">
<input type=hidden name="TIMESTAMP" value="<?=$timestamp?>">

<input type="hidden" name="SHA1HASH" value="<?=$sha1hash?>">
<input type=hidden name="AUTO_SETTLE_FLAG" value="1">
	
<input type="hidden" name="SANTANDER_PRICE" value="<?=$_SESSION["SANTANDER_PRICE"]?>">
<input type="hidden" name="SANTANDER_CURRENCY_CODE" value="<?=$_SESSION["SANTANDER_CURRENCY_CODE"]?>">
<input type="hidden" name="SANTANDER_PAYMENT_TYPE" value="<?=$_SESSION["SANTANDER_PAYMENT_TYPE"]?>">
<input type="hidden" name="SANTANDER_OBJECT_ID" value="<?=$_SESSION["SANTANDER_OBJECT_ID"]?>">
<input type="hidden" name="SANTANDER_ORDER_ID" value="<?=$_SESSION["SANTANDER_ORDER_ID"]?>">
<input type="hidden" name="SANTANDER_TRANSACTION_ID" value="<?=$_SESSION["SANTANDER_TRANSACTION_ID"]?>">
	
<script type='text/javascript'> jQuery(document).ready(function($) {   $('.payment-form').attr( 'action', '<?php echo $url ?>' );  }); </script>
