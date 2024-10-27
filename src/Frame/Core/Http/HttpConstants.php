<?php declare(strict_types=1);

namespace Frame\Core\Http;

interface HttpConstants
{
    // Common HTTP Headers
    public const string HEADER_ACCEPT = 'Accept';
    public const string HEADER_CONTENT_TYPE = 'Content-Type';
    public const string HEADER_AUTHORIZATION = 'Authorization';
    public const string HEADER_USER_AGENT = 'User-Agent';

    // Common Content Types
    public const string CONTENT_TYPE_JSON = 'application/json';
    public const string CONTENT_TYPE_FORM = 'application/x-www-form-urlencoded';
    public const string CONTENT_TYPE_MULTIPART = 'multipart/form-data';
    public const string CONTENT_TYPE_TEXT = 'text/plain';
    public const string CONTENT_TYPE_HTML = 'text/html';

    // Common HTTP Status Codes
    public const int HTTP_OK = 200;
    public const int HTTP_CREATED = 201;
    public const int HTTP_NO_CONTENT = 204;
    public const int HTTP_BAD_REQUEST = 400;
    public const int HTTP_UNAUTHORIZED = 401;
    public const int HTTP_FORBIDDEN = 403;
    public const int HTTP_NOT_FOUND = 404;
    public const int HTTP_METHOD_NOT_ALLOWED = 405;
    public const int HTTP_SERVER_ERROR = 500;
}