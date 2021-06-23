<?php
//ini_set('display_errors', 1);
//error_reporting(E_ALL);
require __DIR__ . '/vendor/autoload.php';
include 'MarketplaceWebServiceProducts/Client.php';
include 'MarketplaceWebServiceProducts/Model/GetLowestOfferListingsForASINRequest.php';
include 'MarketplaceWebServiceProducts/Model/GetCompetitivePricingForASINRequest.php';
include 'MarketplaceWebServiceProducts/Model/GetLowestPricedOffersForASINRequest.php';
include 'MarketplaceWebServiceProducts/Model/GetMyPriceForASINRequest.php';
include 'MarketplaceWebServiceProducts/Model/ASINListType.php';
    if (isset($_GET['asin']))
    {
        $action = $_GET['action'];
        if ($action == 0)
        {
            $ra = array();
            $ra[] = $_GET['asin'];
            GetAsinsLowestOfferListings($ra);
        }
        else
        {
            if ($action == 1)
            {
                $ra = array();
                $ra[] = $_GET['asin'];
                GetCompetitivePricing($ra);
            }
        }
        exit();
    }
function getClient()
{
    try
    {
        $client = new Google_Client();
        $client->setApplicationName('Google Sheets Parser');
        $client->setScopes(Google_Service_Sheets::SPREADSHEETS);
        $client->setAuthConfig('credentialsm.json');
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');
        $credentialsPath = 'tokenm.json';
        $accessToken = "";
        if (file_exists($credentialsPath))
        {
            $accessToken = json_decode(file_get_contents($credentialsPath), true);
        }
        else
        {
            $authUrl = $client->createAuthUrl();
            printf("Open the following link in your browser:\n%s\n", $authUrl);
            print 'Enter verification code: ';
            $authCode = trim(fgets(STDIN));

            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
            if (array_key_exists('error', $accessToken))
            {
                throw new Exception(join(', ', $accessToken));
            }

            // Store the credentials to disk.
            if (!file_exists(dirname($credentialsPath)))
            {
                mkdir(dirname($credentialsPath), 0700, true);
            }
            file_put_contents($credentialsPath, json_encode($accessToken));
            printf("Credentials saved to %s\n", $credentialsPath);
        }
        $client->setAccessToken($accessToken);
        if ($client->isAccessTokenExpired())
        {
            $refreshTokenSaved = $client->getRefreshToken();

            // update access token
            $client->fetchAccessTokenWithRefreshToken($refreshTokenSaved);

            // pass access token to some variable
            $accessTokenUpdated = $client->getAccessToken();

            // append refresh token
            $accessTokenUpdated['refresh_token'] = $refreshTokenSaved;

            //Set the new acces token
            $accessToken = $refreshTokenSaved;
            $client->setAccessToken($accessToken);

            // save to file
            file_put_contents($credentialsPath,
                json_encode($accessTokenUpdated));
        }
        return $client;
    } catch (Exception $ex)
    {
        return getClient();
    }
}

$client = getClient();
$service = new Google_Service_Sheets($client);
$spreadsheetId = '1PJUXyOrqkAwaqhmHp54ej56VmVUHruS8E9Y2-DCuUm4';
$range = 'C2:F';
$response = $service->spreadsheets_values->get($spreadsheetId, $range);
$values = $response->getValues();

if (empty($values))
{
    print "No data found.\n";
}
else
{
    GetFirst();
    GetSecond();
}
function GetFirst()
{
    global $spreadsheetId;
    global $service;
    global $values;
    $asins = array();
    foreach ($values as $row)
    {
        $asins[] = $row[4];
    }
    $asinprices = array();
    $asinfullfilled = array();
    $requestcount = ceil(count($asins) / 20);
    for ($i = 0; $i < $requestcount; $i++)
    {
        $result = GetAsinsLowestOfferListings(array_slice($asins, $i * 20, 20));
        for ($f = 0; $f < count($result); $f++)
        {
            $sp = explode('-', $result[$f]);
            $asinprices[] = $sp[0];
            $asinfullfilled[] = $sp[1];
        }
    }
    {
        $asinscount = count($asins) + 1;
        $asinspricesupdaterange = 'M3:M' . $asinscount;
        echo $asinspricesupdaterange;
        $asinspricesvalues = new Google_Service_Sheets_ValueRange();
        $asinspricesvalues->range = $asinspricesupdaterange;
        $asinspricesvalues->values = array();
        for ($i = 0; $i < count($asins); $i++)
        {
            $asinspricesvalues->values[] = ["$asinprices[$i]"];
        }
        $asinspricesvalues->majorDimension = 'ROWS';
        $service->spreadsheets_values->update($spreadsheetId, $asinspricesupdaterange, $asinspricesvalues, ['valueInputOption' => 'USER_ENTERED']);
    }
    {
        $asinscount = count($asins) + 1;
        $asinsfullfilledupdaterange = 'G3:G' . $asinscount;
        echo $asinsfullfilledupdaterange;
        $asinsfullfilledvalues = new Google_Service_Sheets_ValueRange();
        $asinsfullfilledvalues->range = $asinsfullfilledupdaterange;
        $asinsfullfilledvalues->values = array();
        for ($i = 0; $i < count($asins); $i++)
        {
            $asinsfullfilledvalues->values[] = ["$asinfullfilled[$i]"];
        }
        $asinsfullfilledvalues->majorDimension = 'ROWS';
        $service->spreadsheets_values->update($spreadsheetId, $asinsfullfilledupdaterange, $asinsfullfilledvalues, ['valueInputOption' => 'USER_ENTERED']);
    }
}

function GetSecond()
{
    global $spreadsheetId;
    global $service;
    global $values;
    $asins = array();
    foreach ($values as $row)
    {
        $asins[] = $row[2];
    }
    $asinprices = array();
    $asinfullfilled = array();
    $requestcount = ceil(count($asins) / 20);
    for ($i = 0; $i < $requestcount; $i++)
    {
        $result = GetAsinsLowestOfferListings(array_slice($asins, $i * 20, 20));
        for ($f = 0; $f < count($result); $f++)
        {
            $sp = explode('-', $result[$f]);
            $asinprices[] = $sp[0];
            $asinfullfilled[] = $sp[1];
        }
    }
    {
        $asinscount = count($asins) + 1;
        $asinspricesupdaterange = 'L3:L' . $asinscount;
        echo $asinspricesupdaterange;
        $asinspricesvalues = new Google_Service_Sheets_ValueRange();
        $asinspricesvalues->range = $asinspricesupdaterange;
        $asinspricesvalues->values = array();
        for ($i = 0; $i < count($asins); $i++)
        {
            $asinspricesvalues->values[] = ["$asinprices[$i]"];
        }
        $asinspricesvalues->majorDimension = 'ROWS';
        $service->spreadsheets_values->update($spreadsheetId, $asinspricesupdaterange, $asinspricesvalues, ['valueInputOption' => 'USER_ENTERED']);
    }
    {
        $asinscount = count($asins) + 1;
        $asinsfullfilledupdaterange = 'E3:E' . $asinscount;
        echo $asinsfullfilledupdaterange;
        $asinsfullfilledvalues = new Google_Service_Sheets_ValueRange();
        $asinsfullfilledvalues->range = $asinsfullfilledupdaterange;
        $asinsfullfilledvalues->values = array();
        for ($i = 0; $i < count($asins); $i++)
        {
            $asinsfullfilledvalues->values[] = ["$asinfullfilled[$i]"];
        }
        $asinsfullfilledvalues->majorDimension = 'ROWS';
        $service->spreadsheets_values->update($spreadsheetId, $asinsfullfilledupdaterange, $asinsfullfilledvalues, ['valueInputOption' => 'USER_ENTERED']);
    }

}
function GetIndex($items,$asin)
{
    for ($i = 0; $i < count($items); $i++)
    {
        if( $items[$i]['ASIN']==$asin)
        {
            return $i;
        }
    }
}
function GetAsinsLowestOfferListings($asins)
{
    $config = array('ServiceURL' => 'https://mws.amazonservices.com/Products/2011-10-01');
    $client = new MarketplaceWebServiceProducts_Client('AKIAJDT4XTUNN3LVXM4A', '8QDW/+36akHMbvB4wekrF8TGRkJ0QRUw0F8SUEcI', 'Amazon ASINS Parser', '1.0', $config);
    $request = new MarketplaceWebServiceProducts_Model_GetLowestOfferListingsForASINRequest();
    $request->setSellerId('A14G9MNB5HLBR7');
    $request->setMarketplaceId('ATVPDKIKX0DER');
    $asinslist = new MarketplaceWebServiceProducts_Model_ASINListType();
    $asinslist->setASIN($asins);
    $request->setASINList($asinslist);
    $result = $client->getLowestOfferListingsForASIN($request);
    $xml = simplexml_load_string($result->toXML());
    $count = count($asins);
    $resultasins = array();
    for ($i = 0; $i < $count; $i++)
    {
        try
        {
            $index =  GetIndex($xml->GetLowestOfferListingsForASINResult,$asins[$i]);
            $resultasins[$i] = 0;
            $status = 'false';
            $ncount = $xml->GetLowestOfferListingsForASINResult[$index]->Product->LowestOfferListings->LowestOfferListing->count();
            for ($z = 0; $z < $ncount; $z++)
            {
                if ($xml->GetLowestOfferListingsForASINResult[$index]->Product->LowestOfferListings->LowestOfferListing[$z]->Qualifiers->ItemCondition == 'New')
                {
                    if ($xml->GetLowestOfferListingsForASINResult[$index]->Product->LowestOfferListings->LowestOfferListing[$z]->Price->LandedPrice->Amount < $resultasins[$i] || $resultasins[$i] == 0)
                    {
                        $resultasins[$i] = $xml->GetLowestOfferListingsForASINResult[$index]->Product->LowestOfferListings->LowestOfferListing[$z]->Price->LandedPrice->Amount;
                    }
                    if ($xml->GetLowestOfferListingsForASINResult[$index]->Product->LowestOfferListings->LowestOfferListing[$z]->Qualifiers->FulfillmentChannel == 'Amazon')
                    {
                        $status = 'true';
                    }
                }
            }
            $resultasins[$i] = $resultasins[$i] . '-' . $status;
        } catch (Exception $ex)
        {
            $resultasins[$i] = '0-false';
        }
       // echo $resultasins[$i];
       // echo '<br/>';
    }
    var_dump($xml);
    return $resultasins;
}

function GetCompetitivePricing($asins)
{
    $config = array('ServiceURL' => 'https://mws.amazonservices.com/Products/2011-10-01');
    $client = new MarketplaceWebServiceProducts_Client('AKIAJDT4XTUNN3LVXM4A', '8QDW/+36akHMbvB4wekrF8TGRkJ0QRUw0F8SUEcI', 'Amazon ASINS Parser', '1.0', $config);
    $request = new MarketplaceWebServiceProducts_Model_GetCompetitivePricingForASINRequest();
    $request->setSellerId('A14G9MNB5HLBR7');
    $request->setMarketplaceId('ATVPDKIKX0DER');
    $asinslist = new MarketplaceWebServiceProducts_Model_ASINListType();
    $asinslist->setASIN($asins);
    $request->setASINList($asinslist);
    $result = $client->getCompetitivePricingForASIN($request);
    $xml = simplexml_load_string($result->toXML());
    $count = count($result->getGetCompetitivePricingForASINResult());
    $resultasins = array();
    for ($i = 0; $i < $count; $i++)
    {
        try
        {
            //$count1= $xml->GetCompetitivePricingForASINResult[$i]->Product->CompetitivePricing->CompetitivePrices->count();
            //if($count1!='0') {
            $resultasins[$i] = $xml->GetCompetitivePricingForASINResult[$i]->Product->CompetitivePricing->CompetitivePrices->CompetitivePrice->Price->LandedPrice->Amount;
            //}
            //else
            // {
            //     $resultasins[$i] = 0;
            //}
        } catch (Exception $ex)
        {
            $resultasins[$i] = 0;
        }
    }
    echo $result->toXML();
    return $resultasins;
}