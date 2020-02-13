Common Component
================

## Build `Client` for your REST application

```php
use SocialConnect\Common\ClientAbstract;

class MySocialNetworkClient extends ClientAbstract
{
  public function requestMethod($method, $parameters)
  {
    //...
  }
  
  public function getUser($id)
  {
    $result = $this->requestMethod('/user/get/', $id);
    if ($result) {
      $user = new User();
      $user->id = $result->id;
      //...
      
      return $user;
    }
    
    return false;
  }
}
```

Next you can use it

```php
$client = new MySocialNetworkClient($appId, $appSecret);
// You can use any client that implements Psr\Http\Client\ClientInterface
$client->setHttpClient(new \SocialConnect\HttpClient\Curl());

$user = $client->getUser(1);

//Custom rest methods
$client->requestMethod('myTestMethod', []);
$client->requestMethod('myTest', []);
```
