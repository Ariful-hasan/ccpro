<?php

class Security
{
    private  $method = "AES-256-CBC";
   // private  $pass = "8f1cc582581bdc140efac0f32d718340";
    private  $password = "";
    private  $iv = "";

    public function __construct()
    {
        $this->password = substr(hash('sha256', "8f1cc582581bdc140efac0f32d718340",true), 0, 32);
        $this->iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);
    }


    public function encrypt($message)
    {
        return base64_encode(openssl_encrypt($message, $this->method, $this->password, OPENSSL_RAW_DATA, $this->iv));
    }

    public function decrypt($text)
    {
        return openssl_decrypt(base64_decode($text),  $this->method, $this->password, OPENSSL_RAW_DATA, $this->iv);
    }


}
