<?php
/**
 * Elance strategy for Opauth
 * Based on https://www.elance.com/q/api2/getting-started
 *
 * More information on Opauth: http://opauth.org
 *
 * @copyright    Copyright © 2014 Opauth (http://opauth.org)
 * @link         http://opauth.org
 * @package      Opauth.ElanceStrategy
 * @license      MIT License
 */
namespace Opauth\Elance\Strategy;

use Opauth\Opauth\AbstractStrategy;
use Opauth\Opauth\HttpClientInterface;

class Elance extends AbstractStrategy
{

    /**
     * Compulsory config keys, listed as unassociative arrays
     */
    public $expects = array('client_id', 'client_secret');

    /**
     * Optional config keys, without predefining any default values.
     */
    public $optionals = array('redirect_uri', 'scope', 'state');

    /**
     * Optional config keys with respective default values, listed as associative arrays
     * eg. array('scope' => 'basicInfo');
     */
    public $defaults = array(
        'scope' => 'basicInfo'
    );

    public $responseMap = array(
        'uid' => 'data.providerProfile.userId',
        'name' => 'data.providerProfile.userName',
        'info.name' => 'data.providerProfile.businessName',
        'info.image' => 'data.providerProfile.logo',
        'info.company_id' => 'data.providerProfile.companyUserId',
        'info.company' => 'data.providerProfile.companyBusinessName',
        'info.description' => 'data.providerProfile.tagLine',
        'info.overview' => 'data.providerProfile.overview',
        'info.hourly_rate' => 'data.providerProfile.hourlyRate',
        'info.is_individual' => 'data.providerProfile.isIndividual',
        'info.city' => 'data.providerProfile.city',
        'info.state' => 'data.providerProfile.state',
        'info.country' => 'data.providerProfile.country',
        'info.urls.profile' => 'data.providerProfile.providerProfileURL',
    );

    /**
     * Auth request
     */
    public function request()
    {
        $url = 'https://api.elance.com/api2/oauth/authorize';

        $params = array(
            'redirect_uri' => $this->callbackUrl(),
            'response_type' => 'code',
        );
        $params = $this->addParams(array('client_id', 'scope'), $params);
        $params = $this->addParams($this->optionals, $params);

        $this->redirect($url, $params);
    }

    /**
     * Internal callback, after Elance connect request
     */
    public function callback()
    {
        $callbackTime = time();
        if (!array_key_exists('code', $_GET) || empty($_GET['code'])) {
            return $this->error(
                'Missing code in callback',
                'oauth2callback_error',
                $_GET
            );
        }

        $url = 'https://api.elance.com/api2/oauth/token';
        $params = array(
            'redirect_uri' => $this->callbackUrl(),
            'grant_type' => 'authorization_code',
            'code' => trim($_GET['code'])
        );
        $params = $this->addParams(array('client_id', 'client_secret', 'state'), $params);

        $response = $this->http->post($url, $params);

        $results = json_decode($response);

        if (empty($results->data->access_token)) {
            return $this->error(
                'Failed when attempting to obtain access token',
                'access_token_error',
                $response
            );
        }

    	$data = array('access_token' => $results->data->access_token);
        $user = $this->http->get('https://api.elance.com/api2/profiles/my', $data);
        $user = $this->recursiveGetObjectVars(json_decode($user));

        if (empty($user) || isset($user['message'])) {
            $message = 'Failed when attempting to query Live Connect API for user information';
            if (isset($user['message'])) {
                $message = $user['message'];
            }
            return $this->error(
                $message,
                'userinfo_error',
                $user
            );
        }

        $response = $this->response($user);
        $response->credentials = array(
            'token' => $results->access_token,
            'refresh_token' => $results->data->refresh_token,
            'expires' => date('c', $callbackTime + $results->data->expires_in)
        );
        return $response;
    }
}
