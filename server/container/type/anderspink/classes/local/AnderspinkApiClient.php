<?php

namespace container_anderspink\local;

class AnderspinkApiClient
{
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAIL    = 'fail';

    protected const URL     = 'https://anderspink.com/api/';
    protected const VERSION = 'v3';
    protected string $apiKey;
    protected array  $headers;

    public function __construct(string $apiKey)
    {
        $this->apiKey  = $apiKey;
        $this->headers = [
            "X-Api-Key: {$this->apiKey}",
        ];
    }

    /**
     * Validate ApiKey
     *
     * @return bool
     */
    public function validateKey()
    {
        $url = self::URL . self::VERSION . '/briefings';

        $response = $this->curl($url);

        return $response->status == self::STATUS_SUCCESS;
    }

    /**
     * @return false|mixed
     */
    public function fetchBriefings()
    {
        $url = self::URL . self::VERSION . '/briefings';

        $response = $this->curl($url);

        if ($response->status == self::STATUS_SUCCESS) {
            return $response->data;
        }

        return false;
    }

    /**
     * @return false|mixed
     */
    public function fetchBoards()
    {
        $url = self::URL . self::VERSION . '/boards';

        $response = $this->curl($url);

        if ($response->status == self::STATUS_SUCCESS) {
            return $response->data;
        }

        return false;
    }

    /**
     * @param  int  $id
     * @param  string  $type
     * @param  int  $page
     * @param  int|null  $limit
     * @param  bool  $sync
     *
     * @return false|mixed
     */
    public function fetchArticles(int $id, string $type, int $page = 1, int $limit = null, bool $sync = false)
    {
        if ($page === 1) {
            $offset = 0;
        } else {
            $offset = ($page - 1) * 10;
        }

        $url = self::URL . self::VERSION . "/{$type}/" . $id . "?offset={$offset}";

        if ($sync == true) {
            $url .= "&time=1-week";
        } else {
            $url .= "&time=4-week";
        }

        if ($limit) {
            $url .= "&limit={$limit}";
        }

        $response = $this->curl($url);

        if (isset($response->status) && $response->status == self::STATUS_SUCCESS && sizeof($response->data->articles) > 0) {
            return $response->data;
        }

        return false;
    }

    /**
     * CURL Client
     *
     * @param string $url
     * @return mixed
     */
    private function curl(string $url)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = json_decode(curl_exec($ch));

        curl_close($ch);

        return $response;
    }
}