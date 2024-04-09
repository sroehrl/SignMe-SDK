<?php

namespace Retech\Celest\SignMe;

use Retech\Celest\SignMe\Entity\Company;
use Retech\Celest\SignMe\Entity\Document;
use Retech\Celest\SignMe\Exceptions\ConnectionException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SignMeClient
{
    private HttpClientInterface $client;

    private string $token;

    private string $expires;

    private string $baseUrl;

    public function __construct($baseUrl = 'https://sign.celest.services/api/')
    {
        $this->baseUrl = $baseUrl;
        $this->client = HttpClient::create([
            'base_uri' => $baseUrl,
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ]);
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }


    /**
     * @throws ConnectionException
     */
    public function authenticate($clientId = null, $apiKey = null) : static
    {
        try{
            $response = $this->client->request('POST', 'oauth/token', [
                'json' => [
                    'clientId' => $clientId ?? getenv('SIGN_ME_CLIENT_ID'),
                    'apiKey' => $apiKey ?? getenv('SIGN_ME_API_KEY'),
                ]
            ]);
            if($response->getStatusCode() === 200) {
                $data = $response->toArray();
                $this->token = $data['token'];
                $this->expires = $data['expires'];
                $this->client = $this->client->withOptions(['headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                    'Content-Type' => 'application/json'
                ]]);
            } else {
                throw new ConnectionException('Unable to log in with these credentials');
            }
        } catch (TransportExceptionInterface | ServerExceptionInterface | RedirectionExceptionInterface | DecodingExceptionInterface | ClientExceptionInterface $e) {
            throw new ConnectionException($e->getMessage());
        }

        return $this;
    }

    /**
     * @throws ConnectionException
     */
    public function get(string $url) : array
    {
        try{
            $response = $this->client->request('GET', $url);
            return $response->toArray();
        } catch (TransportExceptionInterface | ServerExceptionInterface | RedirectionExceptionInterface | DecodingExceptionInterface | ClientExceptionInterface $e) {
            throw new ConnectionException($e->getMessage());
        }
    }

    /**
     * @throws ConnectionException
     */
    public function put(string $url, array $payload = [] ): array
    {
        try{
            $response = $this->client->request('PUT', $url, [
                'json' => $payload
            ]);
            return $response->toArray();
        } catch (TransportExceptionInterface | ServerExceptionInterface | RedirectionExceptionInterface | DecodingExceptionInterface | ClientExceptionInterface $e) {
            throw new ConnectionException($e->getMessage());
        }
    }

    /**
     * @throws ConnectionException
     */
    public function post(string $url, array $payload = [] ): array
    {
        try {
            $response = $this->client->request('POST', $url, [
                'json' => $payload
            ]);
            return $response->toArray();
        } catch (TransportExceptionInterface | ServerExceptionInterface | RedirectionExceptionInterface | DecodingExceptionInterface | ClientExceptionInterface $e) {
            throw new ConnectionException($e->getMessage());
        }
    }

    /**
     * @throws ConnectionException
     */
    public function delete(string $url) : array
    {
        try{
            $response = $this->client->request('DELETE', $url);
            return $response->toArray();
        } catch (TransportExceptionInterface | ServerExceptionInterface | RedirectionExceptionInterface | DecodingExceptionInterface | ClientExceptionInterface $e) {
            throw new ConnectionException($e->getMessage());
        }
    }

    /**
     * @throws ConnectionException
     */
    public function getCompany(): Company
    {
        try{
            return new Company($this->get('company'), $this);
        } catch (ServerExceptionInterface | RedirectionExceptionInterface | ClientExceptionInterface $e) {
            throw new ConnectionException($e->getMessage());
        }
    }

    /**
     * @throws ConnectionException
     */
    public function getDocument(string $slug): Document
    {
        try{
            return new Document($this->get('documents/' . $slug), $this);
        } catch (ServerExceptionInterface | RedirectionExceptionInterface | ClientExceptionInterface $e) {
            throw new ConnectionException($e->getMessage());
        }
    }

    /**
     * @throws ConnectionException
     */
    public function getDocuments($page = 1)
    {
        try{
            $result = $this->get('documents?pageSize=50&page=' . $page);
            $documents = [];
            foreach ($result['collection'] as $document) {
                $documents[] = new Document($document, $this);
            }
            return [
                'documents' => $documents,
                'page' => $result['page'],
                'pageSize' => $result['pageSize'],
                'pages' => $result['pages'],
                'total' => $result['total']
            ];
        } catch (ServerExceptionInterface | RedirectionExceptionInterface | ClientExceptionInterface $e) {
            throw new ConnectionException($e->getMessage());
        }

    }

    /**
     * @throws ConnectionException
     * @throws DecodingExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function uploadDocument(string $name, string $file):Document
    {
        try {
            $response = $this->client->request('POST', 'documents', [
                'body' => [
                    'name' => $name,
                    'file' => fopen($file, 'r'),
                    'signatures' => '',
                    'fields' => '',
                ],
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ]
            ]);
            $code = $response->getStatusCode();
            if($code !== 200){
                throw new ConnectionException($response->getContent());
            }
            return new Document($response->toArray(), $this);
        } catch (TransportExceptionInterface $e) {
            $previousException = $e->getPrevious();
            $fullErrorMessage = $previousException->getMessage();
            throw new ConnectionException($e->getMessage());
        }

    }

}