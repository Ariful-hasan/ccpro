<?php

class gPlexEncryption
{
	static function hex2bin($h)
	{
		if (!is_string($h)) return null;
		$r='';
		$len = strlen($h) - 1;
		for ($a=0; $a<$len; $a+=2) {
			$r.=chr(hexdec($h{$a}.$h{($a+1)}));
		}
		return $r;
	}

	static function AESDecrypt($str, $key, $iv)
	{
	//	return gPlexEncryption::xor_decrypt($str, $key);
		$str = gPlexEncryption::hex2bin($str);
	    $block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
	
		if (strlen($iv) < $block) return '';
	    $str = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $str, MCRYPT_MODE_CBC, $iv);

	    $pad = ord($str[($len = strlen($str)) - 1]);
	    return substr($str, 0, strlen($str) - $pad);
	}

	static function AESEncrypt($str, $key, $iv)
	{
	//	return gPlexEncryption::xor_encrypt($str, $key);
		$block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
		$pad = $block - (strlen($str) % $block);
		$str .= str_repeat(chr($pad), $pad);

		$dec = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $str, MCRYPT_MODE_CBC, $iv);
		$dec = bin2hex($dec);
	
	    return $dec;
	}

	static function xor_encrypt($string, $key)
	{
		$resp = gPlexEncryption::xorMessage($string, $key);
		return base64_encode($resp);
	}

	static function xor_decrypt($string, $key)
	{
		$string = base64_decode($string);
		$resp = gPlexEncryption::xorMessage($string, $key);
		return $resp;
	}

	static function xorMessage($string, $key)
	{
		if (empty($string) || empty($key)) return '';
		$string_length = strlen($string);
		$key_length = strlen($key);
	
		for ($i=0; $i<$string_length; $i++) {
			$pos = $i % $key_length;
			$replace = ord ($string[$i]) ^ ord($key[$pos]);
			$string[$i] = chr($replace);
		}

		return $string;
	}

}
