<?php

namespace MonologPushoverCurl;

use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;
use Monolog\Handler\AbstractProcessingHandler;

/**
 * Implementation of Pushover.net notifications for logs using cURL as the transport mechanism. Allows for HTTP Proxy use where required.
 *
 * @author Jonathan Shaw <jonathan.shaw@pzbp.co.uk>
 */
class PushoverCurlHandler extends AbstractProcessingHandler
{
    private bool $initialized = false;
    private string $token;
    private string $user;
    private string $title;
    private string $proxyUrl;
    private \CurlHandle $curl;

    /**
     * @param string       $token   Pushover api token
     * @param string       $user    Pushover user id the message will be sent to
     * @param string|null  $title   Title sent to the Pushover API
     * @param string|null  $proxyUrl Whether to connect via a HTTP Proxy.
     * @param int|string|Level $level The minimum logging level at which this handler will be triggered.
     * @param bool $bubble Whether the messages that are handled can bubble up the stack or not.
     *
     */
    public function __construct(
        string $token,
        string $user,
        ?string $title = null, 
        ?string $proxyUrl = null,
        int|string|Level $level = Level::Critical,
        bool $bubble = true
    ) {
        parent::__construct($level, $bubble);

        $this->token = $token;
        $this->user  = $user;
        $this->title = $title ?? (string) gethostname();
        $this->proxyUrl = $proxyUrl ?? false;
    }

    protected function write(LogRecord $record): void
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        curl_setopt($this->curl, CURLOPT_POSTFIELDS, array(
            "token" => $this->token,
            "user" => $this->user,
            "title" => $this->title,
            "message" => $record->formatted,
            "priority" => 2,
            "retry" => "300",
            "expire" => "3600"
        )
        );

        curl_exec($this->curl);
    }

    private function initialize()
    {
        $this->curl = curl_init();
        curl_setopt_array(
            $this->curl,
            array(
                CURLOPT_URL => 'https://api.pushover.net/1/messages.json',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_PROXY => $this->proxyUrl,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
            )
        );

        $this->initialized = true;
    }
}