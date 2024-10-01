<?php
namespace Simpluity\Simpluity;

class SimpleSemaphore {

    public $API_KEY;
   
    public function __construct($API_KEY) {
        $this->API_KEY = $API_KEY;
    }
    public function sendText($number,$body, $sshDisable = false, $senderName = 'SEMAPHORE', $debug = false) {
        try {
            $ch = curl_init();
            $parameters = array(
                'apikey' => $this->API_KEY,
                'number' => $number,
                'message' => $body,
                'sendername' => $senderName 
            );
            curl_setopt($ch, CURLOPT_URL, 'https://semaphore.co/api/v4/messages');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
            // Disable SSL verification (use only for testing)
            if($sshDisable){
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            }

        
            // Enable error reporting
            curl_setopt($ch, CURLOPT_FAILONERROR, true);
        
            $output = curl_exec($ch);
        
            // Check for cURL errors
            if (curl_errno($ch)) {
                throw new Exception(curl_error($ch));
            }
        
            // Check HTTP status code
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode != 200) {
                throw new Exception("HTTP request failed. Status code: $httpCode");
            }
        
            curl_close($ch);

            if($debug){
                if (empty($output)) {
                    echo "The API request was successful, but the response was empty.";
                }
            
                // Optionally, decode JSON response
                $decodedResponse = json_decode($output, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    echo "\n\nDecoded response:\n";
                    print_r($decodedResponse);
                }
            }
        
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            error_log($e);
        }

    }

}