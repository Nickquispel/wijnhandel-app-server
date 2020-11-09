<?php

use App\Kernel;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__) . '/config/bootstrap.php';

if ($_SERVER['APP_DEBUG']) {
    umask(0000);

    Debug::enable();
}

$auth_token = $username = $password = null;
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $auth_token = $_SERVER['HTTP_AUTHORIZATION'];
} elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
    $auth_token = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
}
if ($auth_token != null) {
    if (strpos(strtolower($auth_token), 'basic') === 0) {
        list($username, $password) = explode(':', base64_decode(substr($auth_token, 6)));
    }
}


if (
    $username == "vianen" &&
    $password == "verbouwing2020"
) {

    if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? $_ENV['TRUSTED_PROXIES'] ?? false) {
        Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST);
    }

    if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? $_ENV['TRUSTED_HOSTS'] ?? false) {
        Request::setTrustedHosts([$trustedHosts]);
    }

    $kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
    $request = Request::createFromGlobals();
    $response = $kernel->handle($request);
    $response->send();
    $kernel->terminate($request, $response);
} else {
    //Send headers to cause a browser to request
    //username and password from user
    header("WWW-Authenticate: " .
        "Basic realm=\Wijnhandel admin\"");
    header("HTTP/1.0 401 Unauthorized");
    //Show failure text, which browsers usually
    //show only after several failed attempts
    print("This page is protected by HTTP ");
}
