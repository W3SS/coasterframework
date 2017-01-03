<?php namespace CoasterCms\Libraries\Blocks\Beacons;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use URL;
use CoasterCms\Libraries\Builder\FormMessage;
/**
 * Kontakt beacon "driver"
 */
class KontaktBeacon extends BeaconAbstract
{
  private $_client = null;

  public function getClient()
  {
    if ( ! empty($this->_client)) {
      return $this->_client;
    }
    return new Client(
        [
            'base_uri' => 'https://api.kontakt.io/',
            'headers' => [
                'Accept' => 'application/vnd.com.kontakt+json;version=8',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Api-Key' => config('coaster::key.kontakt'),
                'User-Agent' => 'Coaster CMS'
            ]
        ]
    );
  }

  public function setUrl($uid, $url)
  {
    $beaconUrlParts = parse_url($url);
    
    $beaconUrlEncoded = '02' . bin2hex('bit.ly'.$beaconUrlParts['path']);
    try {
      $client = $this->getClient();
      $client->request('POST', 'config/delete',
          ['query' =>
              [
                  'uniqueId' => $uid
              ]
          ]
      );

      $client->request('POST', 'config/create',
          ['query' =>
              [
                  'uniqueId' => $uid,
                  'deviceType' => 'beacon',
                  'url' => $beaconUrlEncoded
              ]
          ]
      )->getBody();
      return true;
    } catch (RequestException $e) {
      FormMessage::add('page_info_other[beacons]', 'Error setting URL with Kontakt API (response: '.$e->getMessage().')');
    }

  }

  public function getPendingConfigs()
  {
    $client = $this->getClient();
    $r = $client->request('GET', 'config', ['query' => ['deviceType' => 'beacon']]);

    return json_decode($client->request('GET', 'config', ['query' => ['deviceType' => 'beacon']])->getBody());
  }

  public function listBeacons($uid = '')
  {
    $client = $this->getClient();

    return json_decode($client->request('GET', 'device')->getBody());
  }

  public function getBeaconsByUrl($url = '')
  {
    # code...
  }
}