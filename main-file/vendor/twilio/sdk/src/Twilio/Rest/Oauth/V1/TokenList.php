<?php

/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Oauth
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace Twilio\Rest\Oauth\V1;

use Twilio\Exceptions\TwilioException;
use Twilio\ListResource;
use Twilio\Options;
use Twilio\Values;
use Twilio\Version;


class TokenList extends ListResource
    {
    /**
     * Construct the TokenList
     *
     * @param Version $version Version that contains the resource
     */
    public function __construct(
        Version $version
    ) {
        parent::__construct($version);

        // Path Solution
        $this->solution = [
        ];

        $this->uri = '/token';
    }

    /**
     * Create the TokenInstance
     *
     * @param string $grantType Grant type is a credential representing resource owner's authorization which can be used by client to obtain access token.
     * @param string $clientSid A 34 character string that uniquely identifies this OAuth App.
     * @param array|Options $options Optional Arguments
     * @return TokenInstance Created TokenInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function create(string $grantType, string $clientSid, array $options = []): TokenInstance
    {

        $options = new Values($options);

        $data = Values::of([
            'GrantType' =>
                $grantType,
            'ClientSid' =>
                $clientSid,
            'ClientSecret' =>
                $options['clientSecret'],
            'Code' =>
                $options['code'],
            'CodeVerifier' =>
                $options['codeVerifier'],
            'DeviceCode' =>
                $options['deviceCode'],
            'RefreshToken' =>
                $options['refreshToken'],
            'DeviceId' =>
                $options['deviceId'],
        ]);

        $payload = $this->version->create('POST', $this->uri, [], $data);

        return new TokenInstance(
            $this->version,
            $payload
        );
    }


    /**
     * Provide a friendly representation
     *
     * @return string Machine friendly representation
     */
    public function __toString(): string
    {
        return '[Twilio.Oauth.V1.TokenList]';
    }
}