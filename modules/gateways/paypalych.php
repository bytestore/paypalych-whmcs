<?php
/**
 * WHMCS Paypalych Payment Gateway Module
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * Define module related meta data.
 */
function paypalych_MetaData()
{
    return array(
        'DisplayName' => 'Paypalych',
        'APIVersion' => '1.1', // Use API Version 1.1
        'DisableLocalCreditCardInput' => true,
        'TokenisedStorage' => false,
    );
}


/**
 * Define gateway configuration options.
 */
function paypalych_config()
{
    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'Paypalych',
        ),
        'shop_ID' => array(
            'FriendlyName' => 'Shop ID',
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter your Shop ID here',
        ),
        'API_token' => array(
            'FriendlyName' => 'API token',
            'Type' => 'password',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter API token here',
        ),
        'payer_pays_commission' => array(
            'FriendlyName' => 'Payeers pays comission',
            'Type' => 'yesno',
            'Default' => 'yes',
            'Description' => 'Tick to enable payeers pays fee',
        ),
        'convert_to_rub_cbrf' => array(
            'FriendlyName' => 'Convert to RUB',
            'Type' => 'yesno',
            'Default' => 'yes',
            'Description' => 'convert via API Ru Central Bank API and set payment currency to rub',
        ),
    );
}

/**
 * Payment link.
 */
function paypalych_link($params)
{
    // Gateway Configuration Parameters
    $shop_ID = $params['shop_ID'];
    $API_token = $params['API_token'];
    $dropdownField = $params['dropdownField'];
    $radioField = $params['radioField'];
    $textareaField = $params['textareaField'];
    if ($params['payer_pays_commission'] ==  'on') { $payer_pays_commission = '1';}

    // Invoice Parameters
    $invoiceId = $params['invoiceid'];
    $description = $params["description"];
    $amount = $params['amount'];
    $currencyCode = $params['currency'];

    // Client Parameters
    $firstname = $params['clientdetails']['firstname'];
    $lastname = $params['clientdetails']['lastname'];
    $email = $params['clientdetails']['email'];
    $address1 = $params['clientdetails']['address1'];
    $address2 = $params['clientdetails']['address2'];
    $city = $params['clientdetails']['city'];
    $state = $params['clientdetails']['state'];
    $postcode = $params['clientdetails']['postcode'];
    $country = $params['clientdetails']['country'];
    $phone = $params['clientdetails']['phonenumber'];

    // System Parameters
    $companyName = $params['companyname'];
    $systemUrl = $params['systemurl'];
    $returnUrl = $params['returnurl'];
    $langPayNow = $params['langpaynow'];
    $moduleDisplayName = $params['name'];
    $moduleName = $params['paymentmethod'];
    $whmcsVersion = $params['whmcsVersion'];

    $url = 'https://paypalych.com/api/v1/bill/create';

    $postfields = array();
    $postfields['order_id'] = $invoiceId;
    $postfields['description'] = $description;
    $postfields['amount'] = $amount;
    $postfields['currency_in'] = $currencyCode;
    $postfields['shop_id'] = $shop_ID;
    $postfields['payer_pays_commission'] = $payer_pays_commission;
    $postfields['success_url'] = $returnUrl;
    $postfields['fail_url'] = $returnUrl;
    $postfields['type'] = 'normal';

    if ($params['convert_to_rub_cbrf'] ==  'on') {
        $from_currencyconv = $currencyCode;
        $api_urlconv = 'https://www.cbr-xml-daily.ru/daily_json.js';

        // convert currency to rub via api russian central bank
        $responseconv = file_get_contents($api_urlconv);
        $dataconv = json_decode($responseconv, true);
        $exchange_rateconv = $dataconv['Valute'][$from_currencyconv]['Value'];

        $resultconv = $amount * $exchange_rateconv;
        $resultconv = round($resultconv, 2);
        $postfields['amount'] = round($resultconv, 2);
        $postfields['currency_in'] = 'RUB';
    }

    // Contact merchant and get URL data
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS,  http_build_query($postfields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $headers = [
        "Authorization: Bearer " . $API_token
    ];

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $server_output = curl_exec($ch);
    curl_close($ch);

    // Convert response to PHP array and print button
    $payment_url = json_decode($server_output, true);

    if ($params['convert_to_rub_cbrf'] ==  'on' && $resultconv >= '15') {
        $htmlOutput = '<form method="GET" action="' . $payment_url['link_page_url']  . '">';
        $htmlOutput .= '<input type="submit" value="' . $langPayNow . '" />';
        $htmlOutput .= '</form>';
        if ($params['convert_to_rub_cbrf'] ==  'on') {$htmlOutput .= "{$resultconv} RUB";}
        return $htmlOutput;

    } else if ($params['convert_to_rub_cbrf'] ==  'on' && $resultconv < '15') {

        $htmlOutput = "Minimum amount 15 RUB <br>";
        $htmlOutput .= "Current amount {$resultconv} RUB";
        return $htmlOutput;

    } else {

        $htmlOutput = '<form method="GET" action="' . $payment_url['link_page_url']  . '">';
        $htmlOutput .= '<input type="submit" value="' . $langPayNow . '" />';
        $htmlOutput .= '</form>';
        if ($params['convert_to_rub_cbrf'] ==  'on') {$htmlOutput .= "{$resultconv} RUB";}
        return $htmlOutput;
    };
}
