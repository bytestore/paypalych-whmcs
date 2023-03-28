<?php
/**
 * WHMCS Paypalych callback Gateway Module
 * Wesite: https://profvds.com
 * help: info@profvds.com
 * Donations:
 * BTC: 1PH4v4rh23ugwhB39VipcPN45jS3NPBwDY
 * LTC: LLrk4xXzAUUB2Nn8zCTPgZbmcVvXu6BFmb
 * USDT TRC20: TTofkjw9tiVhyXCyN34Qw3ebiC25mjfwYe
 */

// Require libraries needed for gateway module functions.
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';

// Detect module name from filename.
$gatewayModuleName = basename(__FILE__, '.php');

// Fetch gateway configuration parameters.
$gatewayParams = getGatewayVariables($gatewayModuleName);

// Die if module is not active.
if (!$gatewayParams['type']) {
    die("Module Not Activated");
}

// Get data
$rawBody = file_get_contents("php://input");
parse_str($rawBody, $decodedBody);

// Retrieve data returned in payload
$success = $decodedBody['Status'];
$invoiceId = $decodedBody['InvId'];
$transactionId = $decodedBody['TrsId'];
$paymentAmount = $decodedBody['OutSum'];
$CurrencyIn = $decodedBody['CurrencyIn'];
$Commission = $decodedBody['Commission'];
$hash = $decodedBody['SignatureValue'];

switch ($success) {
    case 'SUCCESS': {
            $transactionStatus = "Payment confirmed";
            echo "Payment confirmed";
            http_response_code(200);
            break;
        }
    case 'FAILED': {
            $transactionStatus = "Payment Failed";
            echo "Payment Failed";
            http_response_code(200);
            break;
        }
}

// Validate that the signarure is valid
$secretKey = $gatewayParams['API_token'];
if ($hash != strtoupper(md5($paymentAmount . ":" . $invoiceId . ":" . $secretKey)) ) {
    $transactionStatus = 'Hash Verification Failure';
    $success = false;
}

// Log the raw JSON response to the gateway module
logTransaction($gatewayParams['name'], $rawBody, $transactionStatus);

// Validate that the invoice is valid
$invoiceId = checkCbInvoiceID($invoiceId, $gatewayParams['name']);

// Validate that the transaction is valid
checkCbTransID($transactionId);

//add payment to invoice
if ($success == 'SUCCESS') {
    addInvoicePayment(
        $invoiceId,
        $transactionId,
        '',
        '',
        $gatewayModuleName
    );
}
