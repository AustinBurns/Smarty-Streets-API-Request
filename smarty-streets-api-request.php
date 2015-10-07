<?php
/*
 *
 * Class to interface with the SmartyStreets US/International API.
 * @author Austin Burns
 * @author-url http://github.com/AustinBurns
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class SmartyStreetsAPIRequest {

    const AUTH_ID = '{your-auth-id}';
    const AUTH_TOKEN = '{your-auth-token}';
    public $us_array = array('US', 'USA', 'United States');
    private $ch;
    private $smarty_streets_api_url;

    function __construct() {
        $this->ch = curl_init();
        curl_setopt_array(
            $this->ch,
            array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_SSL_VERIFYHOST => 2
            )
        );
    }

    public function validate_address($address) {
        if (!is_array($address)) {
            throw new Exception('Address must be an array.');
        } elseif (!array_key_exists('country', $address)) {
            throw new Exception('The country must be provided to search for the address.');
        } else {
            if (in_array($address['country'], $this->us_array)) {
                $this->smarty_streets_api_url = 'https://api.smartystreets.com/street-address?auth-id=' . self:: AUTH_ID . '&auth-token=' . self::AUTH_TOKEN;
            } else {
                $this->smarty_streets_api_url = 'https://international-api.smartystreets.com/verify?auth-id=' . self:: AUTH_ID . '&auth-token=' . self::AUTH_TOKEN;
            }
        }

        try {
            curl_setopt(
                $this->ch,
                CURLOPT_URL,
                $this->smarty_streets_api_url . $this->get_address_query_string($address)
            );

            $response = curl_exec($this->ch);

            if ($response === false) {
                throw new Exception(
                    curl_error($this->ch),
                    curl_errno($this->ch)
                );
            } else {
                return json_decode($response);
            }
        } catch (Exception $e) {
            trigger_error(
                sprintf(
                    'Curl failed with error #%d: %s',
                    $e->getCode(),
                    $e->getMessage()
                ),
                E_USER_ERROR
            );
        }
        curl_close($this->ch);
    }

    private function get_address_query_string($address) {
        $query_string = '';

        foreach ($address as $query_param => $query_value) {
            $query_string .= '&' . $query_param . '=' . urlencode($query_value);
        }

        return $query_string;
    }
}
?>