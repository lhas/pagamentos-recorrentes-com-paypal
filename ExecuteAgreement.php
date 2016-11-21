<?php
require_once __DIR__  . '/vendor/autoload.php';

use PayPal\Api\Agreement;

$apiContext = new \PayPal\Rest\ApiContext(
    new \PayPal\Auth\OAuthTokenCredential(
        'AXci_kc19uu4SkOsq5eTBG5dMw8DhM4ZnbMBv4V_yMqGZgMRzJaY92JFQF-Wh98uKyrn_aqm3zdlW5sb',     // ClientID
        'EH_N4TdOkFK5NALPhMNAIUMWF1ELYTXbakjykRRa1AhC3mHJ21Met-15D7AcBL2ewwSjW69T9wuo8Zay'      // ClientSecret
    )
);

if (isset($_GET['success']) && $_GET['success'] == 'true') {
  var_dump('Usuário efetuou o pagamento com sucesso.');
  $token = $_GET['token'];
  $agreement = new Agreement();

  try {
      $agreement->execute($token, $apiContext);
  } catch (Exception $ex) {
      exit(1);
  }

  $agreement = Agreement::get($agreement->getId(), $apiContext);

  var_dump($agreement);
} else {
  var_dump('Usuário cancelou pagamento/não conseguiu pagar.');
}