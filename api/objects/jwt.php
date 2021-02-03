<?php

require __DIR__ . '/../vendor/autoload.php';

// /https://lcobucci-jwt.readthedocs.io/en/latest/upgrading/
use Lcobucci\JWT\Configuration;
//use Lcobucci\JWT\Builder;
//use Lcobucci\JWT\Parser;
//use Lcobucci\JWT\ValidationData;
//use Lcobucci\JWT\Signer\Hmac\Sha256;

class JWTWrapper{
    
    private $signer; // JWT signer object
    private $secret_key;
    private $website = 'https://member.knightsbridgeassociation.com';
    private $expirationSeconds = 864000; // 10 days    

    private Configuration $config;
+    
+    public function __construct(Configuration $config)
+    {
+        $this->config = $config;
+    }

    // object properties
    public $user;
    public $isAdmin;
    public $loggedIn;
    public $expiry;

    // constructor
    public function __construct(){
        $this->secret_key = getenv('KA_MEMBER_KEY'); // Must be set in .htaccess
        $this->initializeToken();
        $this->signer = new Sha256();
        $this->checkAuth();
    }

    // used by select drop-down list
    private function getAuthorizationHeader(){
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        }
        else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            //print_r($requestHeaders);
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }
    /**
    * get access token from header
    * */
    private function getBearerTokenFromHeaders() {
        $headers = $this->getAuthorizationHeader();
        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    // Check the headers for the presence of a valid JWT
    public function checkAuth(){

        $token = $this->getBearerTokenFromHeaders(); // get token from Auth header
        
        // If no token just exit
        if($token === NULL){
            $this->initializeToken();
            return;
        }

        // convert from string into JWT object
        $token = (new Parser())->parse((string) $token);
        
        // If its a valid token the update the class properties
        if($token->verify($this->signer, $this->secret_key) && $token->getClaim('user')){
            
            $data = new ValidationData(); // It will use the current time to validate (iat, nbf and exp)
            $data->setIssuer($this->website);
            $data->setAudience($this->website);

            if($token->validate($data)){
                $this->user = $token->getClaim('user');
                $this->isAdmin=$token->getClaim('isAdmin')?true:false;
                $this->loggedIn = true;
                $this->expiry = date("Y-m-d H:i:s",$token->getClaim('exp'));
            }
            else {
                $this->initializeToken();
            }
        }
        else {
            $this->initializeToken();
        }
    }

    // Get a string representation of a new JWT
    public function getToken($username, $isAdmin){
        return (string)(new Builder())->setIssuer($this->website) // Configures the issuer (iss claim)
                        ->setAudience($this->website) // Configures the audience (aud claim)
                        ->setIssuedAt(time()) // Configures the time that the token was issue (iat claim)
                        ->set('user', $username) // Configures a new claim, called "user"
                        ->set('isAdmin', $isAdmin) // Configures a new claim, called "user"
                        ->setExpiration(time() + $this->expirationSeconds) // Configures the expiration time of the token (exp claim)
                        ->sign($this->signer, $this->secret_key) // creates a signature using "testing" as key
                        ->getToken(); // Retrieves the generated token
    }

    /*
        Set the token properties to default states
    */
    private function initializeToken(){
        $this->user = '';
        $this->isAdmin = false;
        $this->loggedIn = false;
        $this->expiry = '';
    }
}
?>