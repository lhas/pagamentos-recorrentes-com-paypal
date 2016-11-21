<?php
require_once __DIR__  . '/vendor/autoload.php';

use PayPal\Api\ChargeModel;
use PayPal\Api\Currency;
use PayPal\Api\MerchantPreferences;
use PayPal\Api\PaymentDefinition;
use PayPal\Api\Patch;
use PayPal\Api\PatchRequest;
use PayPal\Api\Plan;
use PayPal\Api\Agreement;
use PayPal\Api\Payer;
use PayPal\Common\PayPalModel;

$apiContext = new \PayPal\Rest\ApiContext(
    new \PayPal\Auth\OAuthTokenCredential(
        'AXci_kc19uu4SkOsq5eTBG5dMw8DhM4ZnbMBv4V_yMqGZgMRzJaY92JFQF-Wh98uKyrn_aqm3zdlW5sb',     // ClientID
        'EH_N4TdOkFK5NALPhMNAIUMWF1ELYTXbakjykRRa1AhC3mHJ21Met-15D7AcBL2ewwSjW69T9wuo8Zay'      // ClientSecret
    )
);

function createPlan($apiContext, $name, $description, $frequency, $frequencyInterval, $cycles, $amountValue, $amountCurrency) {
  $paymentDefinition = new PaymentDefinition();

  $paymentDefinition->setName('Regular Payments')
    ->setType('REGULAR')
    ->setFrequency($frequency)
    ->setFrequencyInterval($frequencyInterval)
    ->setCycles($cycles)
    ->setAmount(new Currency(array('value' => $amountValue, 'currency' => $amountCurrency)));

  $plan = new Plan();

  $plan->setName($name)
    ->setDescription($description)
    ->setType('fixed');

  $plan->setPaymentDefinitions(array($paymentDefinition));

  $plan = setMerchantPreferences($plan, 'http://localhost/pagamentos-recorrentes-com-paypal');

  return $plan->create($apiContext);
}

function setMerchantPreferences($plan, $baseUrl, $returnUrl = '/ExecuteAgreement.php?success=true', $cancelUrl = '/ExecuteAgreement.php?success=false') {
  $merchantPreferences = new MerchantPreferences();

  $merchantPreferences->setReturnUrl($baseUrl . $returnUrl)
    ->setCancelUrl($baseUrl . $cancelUrl)
    ->setAutoBillAmount("yes")
    ->setInitialFailAmountAction("CONTINUE")
    ->setMaxFailAttempts("0");

  $plan->setMerchantPreferences($merchantPreferences);

  return $plan;
}

function activatePlan($apiContext, $plan) {
  $patch = new Patch();

  $value = new PayPalModel('{
     "state":"ACTIVE"
   }');

  $patch->setOp('replace')
    ->setPath('/')
    ->setValue($value);

  $patchRequest = new PatchRequest();
  $patchRequest->addPatch($patch);

  $plan->update($patchRequest, $apiContext);

  return Plan::get($plan->getId(), $apiContext);
}

function createAgreement($apiContext, $createdPlan) {
  $agreement = new Agreement();

  $agreement->setName('Base Agreement')
    ->setDescription('Basic Agreement')
    ->setStartDate('2019-06-17T9:45:04Z');

  $plan = new Plan();
  $plan->setId($createdPlan->getId());
  $agreement->setPlan($plan);

  $payer = new Payer();
  $payer->setPaymentMethod('paypal');
  $agreement->setPayer($payer);

  return $agreement->create($apiContext);
}

// Criamos um plano chamado "User Premium".
// Este plano será cobrado a cada 1 ($frequencyInterval) mês ($frequency) durante 12 meses ($cycles)
// com um preço de R$ 50 ($amountCurrency e $amountValue).
$userPremiumPlan = createPlan($apiContext, 'User Premium Plan', 'A premium plan for our users on 0e1dev.com', 'Month', '1', '12', 50, 'BRL');

// Agora vamos ativar o plano
$userPremiumPlan = activatePlan($apiContext, $userPremiumPlan);

$userPremiumAgreement = createAgreement($apiContext, $userPremiumPlan);

redirectUser($userPremiumAgreement->getApprovalLink());

var_dump($userPremiumAgreement);