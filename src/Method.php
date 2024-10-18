<?php

enum Method
{
    case GET;
    case POST;

    public static function from(string $method): Method
    {
        return match ($method) {
            'GET' => self::GET,
            'POST' => self::POST,
        };
    }
}