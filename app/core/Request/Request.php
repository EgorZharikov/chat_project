<?php

namespace App\core\Request;


class Request
{
    public function __construct(
        public readonly array $get,
        public readonly array $post,
        public readonly array $server,
        public readonly array $cookie
    ) {
    }
    public static function createFromGlobals(): static
    {
        return new self(
            $_GET,
            $_POST,
            $_SERVER,
            $_COOKIE,
        );
    }


    public function uri(): string
    {
        return strtok($this->server['REQUEST_URI'], '?');
    }

    public function method(): string
    {
        return $this->server['REQUEST_METHOD'];
    }

}
