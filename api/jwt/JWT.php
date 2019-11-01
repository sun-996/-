<?php
class JWT {

    public static $leeway = 0;
    public static $timestamp = null;
    public static $supported_algs = array(
        'HS256' => array('hash_hmac', 'SHA256'),
        'HS512' => array('hash_hmac', 'SHA512'),
        'HS384' => array('hash_hmac', 'SHA384'),
        'RS256' => array('openssl', 'SHA256'),
        'RS384' => array('openssl', 'SHA384'),
        'RS512' => array('openssl', 'SHA512'),
    );

    public static function decode($jwt, $key, array $allowed_algs = array()) {
        $timestamp = is_null(static::$timestamp) ? time() : static::$timestamp;

        if (empty($key)) {
            throw new Exception('密钥不能为空');
        }
        $tks = explode('.', $jwt);
        if (count($tks) != 3) {
            throw new Exception('错误的段数');
        }
        list($headb64, $bodyb64, $cryptob64) = $tks;
        if (null === ($header = static::jsonDecode(static::urlsafeB64Decode($headb64)))) {
            throw new Exception('无效的头编码');
        }
        if (null === $payload = static::jsonDecode(static::urlsafeB64Decode($bodyb64))) {
            throw new Exception('声明编码无效');
        }
        if (false === ($sig = static::urlsafeB64Decode($cryptob64))) {
            throw new Exception('签名编码无效');
        }
        if (empty($header->alg)) {
            throw new Exception('空算法');
        }
        if (empty(static::$supported_algs[$header->alg])) {
            throw new Exception('不支持算法');
        }
        if (!in_array($header->alg, $allowed_algs)) {
            throw new Exception('不允许使用算法');
        }
        if (is_array($key) || $key instanceof \ArrayAccess) {
            if (isset($header->kid)) {
                if (!isset($key[$header->kid])) {
                    throw new Exception('kid无效，无法查找正确的密钥');
                }
                $key = $key[$header->kid];
            } else {
                throw new Exception('kid为空，无法查找正确的密钥');
            }
        }

        // 检查签名
        if (!static::verify("$headb64.$bodyb64", $sig, $key, $header->alg)) {
            throw new Exception('签名验证失败！');
        }

        // 检查是否定义了NBF
        // 令牌实际上可以使用
        if (isset($payload->nbf) && $payload->nbf > ($timestamp + static::$leeway)) {
            throw new Exception(
                '无法处理之前的令牌' . date(DateTime::ISO8601, $payload->nbf)
            );
        }

        if (isset($payload->iat) && $payload->iat > ($timestamp + static::$leeway)) {
            throw new Exception(
                '无法处理之前的令牌' . date(DateTime::ISO8601, $payload->iat)
            );
        }

        if (isset($payload->exp) && ($timestamp - static::$leeway) >= $payload->exp) {
            throw new Exception('过期令牌');
        }

        return $payload;
    }

    public static function encode($payload, $key, $alg = 'HS256', $keyId = null, $head = null) {
        $header = array('typ' => 'JWT', 'alg' => $alg);
        if ($keyId !== null) {
            $header['kid'] = $keyId;
        }
        if ( isset($head) && is_array($head) ) {
            $header = array_merge($head, $header);
        }
        $segments = array();
        $segments[] = static::urlsafeB64Encode(static::jsonEncode($header));
        $segments[] = static::urlsafeB64Encode(static::jsonEncode($payload));
        $signing_input = implode('.', $segments);

        $signature = static::sign($signing_input, $key, $alg);
        $segments[] = static::urlsafeB64Encode($signature);

        return implode('.', $segments);
    }

    public static function sign($msg, $key, $alg = 'HS256'){
        if (empty(static::$supported_algs[$alg])) {
            throw new Exception('不支持算法');
        }
        list($function, $algorithm) = static::$supported_algs[$alg];
        switch($function) {
            case 'hash_hmac':
                return hash_hmac($algorithm, $msg, $key, true);
            case 'openssl':
                $signature = '';
                $success = openssl_sign($msg, $signature, $key, $algorithm);
                if (!$success) {
                    throw new Exception("OpenSSL无法对数据进行签名");
                } else {
                    return $signature;
                }
        }
    }

    private static function verify($msg, $signature, $key, $alg){
        if (empty(static::$supported_algs[$alg])) {
            throw new Exception('不支持算法');
        }

        list($function, $algorithm) = static::$supported_algs[$alg];
        switch($function) {
            case 'openssl':
                $success = openssl_verify($msg, $signature, $key, $algorithm);
                if ($success === 1) {
                    return true;
                } elseif ($success === 0) {
                    return false;
                }
                throw new Exception(
                    'OpenSSL错误: ' . openssl_error_string()
                );
            case 'hash_hmac':
            default:
                $hash = hash_hmac($algorithm, $msg, $key, true);
                if (function_exists('hash_equals')) {
                    return hash_equals($signature, $hash);
                }
                $len = min(static::safeStrlen($signature), static::safeStrlen($hash));

                $status = 0;
                for ($i = 0; $i < $len; $i++) {
                    $status |= (ord($signature[$i]) ^ ord($hash[$i]));
                }
                $status |= (static::safeStrlen($signature) ^ static::safeStrlen($hash));

                return ($status === 0);
        }
    }

    public static function jsonDecode($input){
        if (version_compare(PHP_VERSION, '5.4.0', '>=') && !(defined('JSON_C_VERSION') && PHP_INT_SIZE > 4)) {
            $obj = json_decode($input, false, 512, JSON_BIGINT_AS_STRING);
        } else {
            $max_int_length = strlen((string) PHP_INT_MAX) - 1;
            $json_without_bigints = preg_replace('/:\s*(-?\d{'.$max_int_length.',})/', ': "$1"', $input);
            $obj = json_decode($json_without_bigints);
        }

        if (function_exists('json_last_error') && $errno = json_last_error()) {
            static::handleJsonError($errno);
        } elseif ($obj === null && $input !== 'null') {
            throw new Exception('结果非零输入与零');
        }
        return $obj;
    }

    public static function jsonEncode($input){
        $json = json_encode($input);
        if (function_exists('json_last_error') && $errno = json_last_error()) {
            static::handleJsonError($errno);
        } elseif ($json === 'null' && $input !== null) {
            throw new Exception('结果非零输入与零');
        }
        return $json;
    }

    public static function urlsafeB64Decode($input){
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }

    public static function urlsafeB64Encode($input){
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

    private static function handleJsonError($errno){
        $messages = array(
            JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
            JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
            JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
            JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON',
            JSON_ERROR_UTF8 => 'Malformed UTF-8 characters' //PHP >= 5.3.3
        );
        throw new Exception(
            isset($messages[$errno])
            ? $messages[$errno]
            : 'Unknown JSON error: ' . $errno
        );
    }

    private static function safeStrlen($str){
        if (function_exists('mb_strlen')) {
            return mb_strlen($str, '8bit');
        }
        return strlen($str);
    }
}