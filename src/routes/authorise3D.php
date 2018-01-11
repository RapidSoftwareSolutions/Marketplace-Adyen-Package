<?php

$app->post('/api/Adyen/authorise3D', function ($request, $response) {

    $settings = $this->settings;
    $checkRequest = $this->validation;
    $validateRes = $checkRequest->validate($request, ['username','password','md','merchantAccount','paResponse','amountCurrency','amountValue','reference']);

    if(!empty($validateRes) && isset($validateRes['callback']) && $validateRes['callback']=='error') {
        return $response->withHeader('Content-type', 'application/json')->withStatus(200)->withJson($validateRes);
    } else {
        $post_data = $validateRes;
    }

    $requiredParams = ['reference'=>'reference','amountCurrency'=>'amountCurrency','amountValue'=>'amountValue','username'=>'username','password'=>'password','md'=>'md','merchantAccount'=>'merchantAccount','paResponse'=>'paResponse'];
    $optionalParams = ['additionalData'=>'additionalData','billingAddress'=>'billingAddress','browserInfo'=>'browserInfo','captureDelayHours'=>'captureDelayHours','dateOfBirth'=>'dateOfBirth','dccQuote'=>'dccQuote','deliveryAddress'=>'deliveryAddress','deliveryDate'=>'deliveryDate','deviceFingerprint'=>'deviceFingerprint','fraudOffset'=>'fraudOffset','installments'=>'installments','mcc'=>'mcc','merchantOrderReference'=>'merchantOrderReference','metadata'=>'metadata','orderReference'=>'orderReference','recurring'=>'recurring','recurringProcessingModel'=>'recurringProcessingModel','selectedBrand'=>'selectedBrand','selectedRecurringDetailReference'=>'selectedRecurringDetailReference','sessionId'=>'sessionId','shopperEmail'=>'shopperEmail','shopperIP'=>'shopperIP','shopperInteraction'=>'shopperInteraction','shopperLocale'=>'shopperLocale','shopperName'=>'shopperName','shopperReference'=>'shopperReference','shopperStatement'=>'shopperStatement','socialSecurityNumber'=>'socialSecurityNumber','store'=>'store','telephoneNumber'=>'telephoneNumber','totalsGroup'=>'totalsGroup'];
    $bodyParams = [
       'json' => ['additionalAmount','additionalData','amount','billingAddress','browserInfo','captureDelayHours','dateOfBirth','dccQuote','deliveryAddress','deliveryDate','deviceFingerprint','fraudOffset','installments','mcc','md','merchantAccount','merchantOrderReference','metadata','orderReference','paResponse','recurring','recurringProcessingModel','reference','selectedBrand','selectedRecurringDetailReference','sessionId','shopperEmail','shopperIP','shopperInteraction','shopperLocale','shopperName','shopperReference','shopperStatement','socialSecurityNumber','store','telephoneNumber','totalsGroup']
    ];

    $data = \Models\Params::createParams($requiredParams, $optionalParams, $post_data['args']);

    
    $data['dateOfBirth'] = \Models\Params::toFormat($data['dateOfBirth'], 'Y-m-d'); 
    $data['deliveryDate'] = \Models\Params::toFormat($data['deliveryDate'], 'Y-m-d'); 

    $client = $this->httpClient;
    $query_str = "https://pal-test.adyen.com/pal/servlet/Payment/v30/authorise3d";

    $data['amount']['currency'] = $data['amountCurrency'];
$data['amount']['value'] = $data['amountValue'];

    $requestParams = \Models\Params::createRequestBody($data, $bodyParams);
    $requestParams['headers'] = [];
    $requestParams["auth"] = [$data['username'],$data['password']];

    try {
        $resp = $client->post($query_str, $requestParams);
        $responseBody = $resp->getBody()->getContents();

        if(in_array($resp->getStatusCode(), ['200', '201', '202', '203', '204'])) {
            $result['callback'] = 'success';
            $result['contextWrites']['to'] = is_array($responseBody) ? $responseBody : json_decode($responseBody);
            if(empty($result['contextWrites']['to'])) {
                $result['contextWrites']['to']['status_msg'] = "Api return no results";
            }
        } else {
            $result['callback'] = 'error';
            $result['contextWrites']['to']['status_code'] = 'API_ERROR';
            $result['contextWrites']['to']['status_msg'] = json_decode($responseBody);
        }

    } catch (\GuzzleHttp\Exception\ClientException $exception) {

        $responseBody = $exception->getResponse()->getBody()->getContents();
        if(empty(json_decode($responseBody))) {
            $out = $responseBody;
        } else {
            $out = json_decode($responseBody);
        }
        $result['callback'] = 'error';
        $result['contextWrites']['to']['status_code'] = 'API_ERROR';
        $result['contextWrites']['to']['status_msg'] = $out;

    } catch (GuzzleHttp\Exception\ServerException $exception) {

        $responseBody = $exception->getResponse()->getBody()->getContents();
        if(empty(json_decode($responseBody))) {
            $out = $responseBody;
        } else {
            $out = json_decode($responseBody);
        }
        $result['callback'] = 'error';
        $result['contextWrites']['to']['status_code'] = 'API_ERROR';
        $result['contextWrites']['to']['status_msg'] = $out;

    } catch (GuzzleHttp\Exception\ConnectException $exception) {
        $result['callback'] = 'error';
        $result['contextWrites']['to']['status_code'] = 'INTERNAL_PACKAGE_ERROR';
        $result['contextWrites']['to']['status_msg'] = 'Something went wrong inside the package.';

    }

    return $response->withHeader('Content-type', 'application/json')->withStatus(200)->withJson($result);

});