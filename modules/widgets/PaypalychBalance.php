<?php

/**
 * Name: WHMCS Paypalych Balance Widget
 * Wesite: https://profvds.com
 * help: info@profvds.com
 */
add_hook('AdminHomeWidgets', 1, function() {
    return new PaypalychBalanceWidget();
});

class PaypalychBalanceWidget extends \WHMCS\Module\AbstractWidget
{
    protected $title = 'Paypalych Balance';
    protected $description = 'Paypalych Balance widget';
    protected $weight = 150;
    protected $columns = 1;
    protected $cache = true;
    protected $cacheExpiry = 120;

    public function getData()
    {
            $gatewayParams = getGatewayVariables('paypalych');

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, 'https://paypalych.com/api/v1/merchant/balance');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

            $headers = array();
            $headers[] = 'Authorization: Bearer ' . $gatewayParams['API_token'];
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $result = curl_exec($ch);

            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
                }

            curl_close($ch);

            $response = json_decode($result, true);



    return $response;
    }

    public function generateOutput($response)
    {

    if ($response['success'] == "true") {
        foreach ($response['balances'] as $balance) {
        $balance['balance_available'] = round ($balance['balance_available'],2);
        $balance['balance_locked'] = round ($balance['balance_locked'],2);
        $balance['balance_hold'] = round ($balance['balance_hold'],2);
return <<<EOF
            <div style="margin:10px;padding:10px;background-color:#EFFAE4;text-align:center;font-size:16px;color:#000;">
            Balance: <strong>{$balance['balance_available']}</strong> {$balance['currency']} ~ Locked: {$balance['balance_locked']} ~ Hold: {$balance['balance_hold']}
            </div>
EOF;
        }
        } else {
            echo "Error: " . $response['error_message'] . "\n";
        }
    }
}
