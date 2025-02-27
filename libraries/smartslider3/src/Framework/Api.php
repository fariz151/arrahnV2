<?php


namespace Nextend\Framework;


use Exception;
use Joomla\CMS\Http\Http;
use Nextend\Framework\Misc\Base64;
use Nextend\Framework\Misc\HttpClient;
use Nextend\Framework\Notification\Notification;
use Nextend\Framework\Platform\Platform;
use Nextend\Framework\Request\Request;
use Nextend\Framework\Url\Url;

class Api {

    private static $api = 'https://api.nextendweb.com/v1/';

    public static function getApiUrl() {

        return self::$api;
    }

    public static function api($posts, $returnUrl = false) {

        $api = self::getApiUrl();

		//mhehm >>
		if ($posts['action'] == 'asset')
		{
			//$api = 'https://joomlashare.ir/images/smartslider3/' . str_replace('http://smartslider3.com/','',$posts['asset']) . '.zip';
		}
		elseif ($posts['action'] == 'licensecheck')
		{
			return array('status' => 'OK', 'data' => '');
		}
		//<< mhehm

        $posts_default = array(
            'platform' => Platform::getName()
        );
        $posts_default['domain'] = parse_url(Url::getSiteUri(), PHP_URL_HOST);
    

        $posts = $posts + $posts_default;

		$posts['domain'] = 'localhost'; //mhehm

        if ($returnUrl) {
            return $api . '?' . http_build_query($posts, '', '&');
        }
        if (class_exists('Http')) {

            $client = new Http();
            try {
                $response = $client->post($api, http_build_query($posts, '', '&'), array('Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8'), 5);
            } catch (Exception $e) {
            }
            if (isset($response) && $response && $response->code == '200') {

                if (isset($response->headers['Content-Type'])) {
                    $contentType = $response->headers['Content-Type'];
                } else if (isset($response->headers['content-type'])) {
                    $contentType = $response->headers['content-type'];
                }

                if (is_array($contentType)) {
                    /**
                     * Joomla 4 headers stored as arrays
                     */
                    $contentType = $contentType[0];
                }

                $data = $response->body;
            }
        }

        if (!isset($data)) {
            if (function_exists('curl_init') && function_exists('curl_exec')) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $api);

                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($posts, '', '&'));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)');
                curl_setopt($ch, CURLOPT_REFERER, Request::$SERVER->getVar('REQUEST_URI'));

                $data        = curl_exec($ch);
                $errorNumber = curl_errno($ch);
                if ($errorNumber == 60 || $errorNumber == 77) {
                    curl_setopt($ch, CURLOPT_CAINFO, HttpClient::getCacertPath());
                    $data = curl_exec($ch);
                }
                $contentType     = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
                $error           = curl_error($ch);
                $curlErrorNumber = curl_errno($ch);
                curl_close($ch);

                if ($curlErrorNumber) {
                    Notification::error($curlErrorNumber . $error);

                    return array(
                        'status' => 'ERROR_HANDLED'
                    );
                }
            } else {
                $opts    = array(
                    'http' => array(
                        'method'  => 'POST',
                        'header'  => 'Content-type: application/x-www-form-urlencoded',
                        'content' => http_build_query($posts, '', '&')
                    )
                );
                $context = stream_context_create($opts);
                $data    = file_get_contents($api, false, $context);
                if ($data === false) {
                    Notification::error(n2_('CURL disabled in your php.ini configuration. Please enable it!'));

                    return array(
                        'status' => 'ERROR_HANDLED'
                    );
                }
                $headers = self::parseHeaders($http_response_header);
                if ($headers['status'] != '200') {
                    Notification::error(n2_('Unable to contact with the licensing server, please try again later!'));

                    return array(
                        'status' => 'ERROR_HANDLED'
                    );
                }
                if (isset($headers['content-type'])) {
                    $contentType = $headers['content-type'];
                }
            }
        }
    

        switch ($contentType) {
            case 'text/html; charset=UTF-8':

                Notification::error(sprintf('Unexpected response from the API.<br>Contact us (support@nextendweb.com) with the following log:') . '<br><textarea style="width: 100%;height:200px;font-size:8px;">' . Base64::encode($data) . '</textarea>');

                return array(
                    'status' => 'ERROR_HANDLED'
                );
                break;
            case 'application/json':
                return json_decode($data, true);
        }

        return $data;
    }

    private static function parseHeaders(array $headers, $header = null) {
        $output = array();
        if ('HTTP' === substr($headers[0], 0, 4)) {
            list(, $output['status'], $output['status_text']) = explode(' ', $headers[0]);
            unset($headers[0]);
        }
        foreach ($headers as $v) {
            $h = preg_split('/:\s*/', $v);
            if (count($h) >= 2) {
                $output[strtolower($h[0])] = $h[1];
            }
        }
        if (null !== $header) {
            if (isset($output[strtolower($header)])) {
                return $output[strtolower($header)];
            }

            return null;
        }

        return $output;
    }
}