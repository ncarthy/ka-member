<?php

require __DIR__ . '/../vendor/autoload.php';

// This code uses Luis Cobucci' implementation of JWT: https://github.com/lcobucci/jwt/


// /https://lcobucci-jwt.readthedocs.io/en/latest/upgrading/
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\ValidAt;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;

class JWTWrapper{
    
    private $config; // Jwt configuration object, contains the signer and constraints
    private $issuer = 'https://knightsbridgeassociation.com';
    private $audience = 'https://member.knightsbridgeassociation.com';

    // object properties
    public $id;
    public $user;
    public $isAdmin;
    public $loggedIn;
    public $expiry;

    // constructor
    public function __construct(){

        $this->config = Configuration::forSymmetricSigner(
            // You may use any HMAC variations (256, 384, and 512)
            new Sha256(),
            // replace the value below with a key of your own!
            InMemory::plainText( getenv('KA_MEMBER_KEY') )
            // You may also override the JOSE encoder/decoder if needed by providing extra arguments here
        );

        $this->config->setValidationConstraints(
            new SignedWith($this->config->signer(), $this->config->verificationKey()),
            new PermittedFor($this->audience),
            new IssuedBy($this->issuer)
        );

        $this->initializeToken();
        
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
        return NULL;
    }

    // Check the headers for the presence of a valid JWT
    public function checkAuth(){

        $token = $this->getBearerTokenFromHeaders(); // get token from Auth header
        
        // If no token just exit
        if( is_null($token) ){
            $this->initializeToken();
            return;
        }

        // convert from string into JWT object
        $parser = $this->config->parser();
        $token = $parser->parse((string) $token);

        $token->headers(); // Retrieves the token headers
        $claims = $token->claims(); // Retrieves the token claims

        $constraints = $this->config->validationConstraints();      
        
        // If its a valid token the update the class properties
        // the '...' means to pass an array as function arguments
        if($this->config->validator()->validate($token, ...$constraints)){
            $this->id = $claims->get('id');
            $this->user = $claims->get('user');
            $this->isAdmin=$claims->get('isAdmin')?true:false;
            $this->loggedIn = true;
            $this->expiry = $claims->get('exp')->format("Y-m-d H:i:s");
        }
        else {
            $this->initializeToken();
        }
    }

    // Get a string representation of a new JWT
    public function getToken($userid, $username, $isAdmin, $issuedAt, $expiresAt){

        $builder = $this->config->builder();

        $token = $builder->issuedBy($this->issuer)
                        ->withHeader('iss', $this->issuer)
                        ->permittedFor($this->audience)
                        ->issuedAt($issuedAt)
                        ->expiresAt($expiresAt)
                        ->withClaim('id', $userid)
                        ->withClaim('user', $username)
                        ->withClaim('isAdmin', $isAdmin)
                        ->getToken($this->config->signer(), $this->config->signingKey());

        return $token->toString();
    }

    /*
        Set the token properties to default states
    */
    private function initializeToken(){
        $this->user = '';
        $this->isAdmin = false;
        $this->loggedIn = false;
        $this->expiry = '';
        $this->id = 0;
    }
}
?>