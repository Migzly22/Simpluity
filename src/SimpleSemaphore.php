<?php
namespace Simpluity\Simpluity;

class SimpleSemaphore {

    public $API_KEY;
   
    public function __construct($API_KEY) {
        $this->API_KEY = $API_KEY;
    }
    public function sendText($number,$body) {
        try {
            $ch = curl_init();
            $parameters = array(
                'apikey' => $this->API_KEY, //Your API KEY
                'number' => $number,
                'message' => $body,
                'sendername' => 'SEMAPHORE'
            );
            curl_setopt( $ch, CURLOPT_URL,'https://semaphore.co/api/v4/messages' );
            curl_setopt( $ch, CURLOPT_POST, 1 );
            
            //Send the parameters set above with the request
            curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $parameters ) );
            
            // Receive response from server
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            $output = curl_exec( $ch );
            curl_close ($ch);
            
            //Show the server response
            return $output;
        } catch (Exception $e) {
            echo $e;
        }


    }

}