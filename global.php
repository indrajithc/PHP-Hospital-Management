<?php


define( 'SYSTEM_NAME', 'hospital' ); 
define( 'DISPLAY_NAME', 'TALUK HEADQUARTERS HOSPITAL' );
define( 'SYSTEM_ROOT', '/hospital' );



define("ENCRYPTION_KEY", "!@1#Y$%^g&k*");
	// encrypt/decrypt($encrypted, ENCRYPTION_KEY);


define( 'MAIN01', 'admin');
define( 'MAIN02', 'staff');
define( 'MAIN03', 'record');
define( 'MAIN04', 'pharmacy');
define( 'MAIN05', 'employee');


/*
 * ==== 1 =>  admin 
 * ==== 2 =>  staff 
 * ==== 3 =>  record
 * ==== 4 =>  pharmacy 
 * ==== 5 =>  employee 
*/



function siteURL() {
	$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https" : "http"; 
	return $protocol;
} 

$SPROTOCOL = siteURL();

define( 'ROOT',  $SPROTOCOL. ':' . '//' . $_SERVER['SERVER_NAME'] .':' . $_SERVER['SERVER_PORT']  ); 
define( 'DIRECTORY',  SYSTEM_ROOT . '/'); 
define( 'PATH', $SPROTOCOL. '://' . $_SERVER['SERVER_NAME'] .':' . $_SERVER['SERVER_PORT']  . DIRECTORY ); 





define( 'DIRECTORY_ADMIN', DIRECTORY . MAIN01 . '/' ); 
define( 'DIRECTORY_STAFF', DIRECTORY . MAIN02 . '/' ); 
define( 'DIRECTORY_RECORD', DIRECTORY . MAIN03 . '/' ); 
define( 'DIRECTORY_PHARMACY', DIRECTORY . MAIN04 . '/' ); 
define( 'DIRECTORY_EMPLOYEE', DIRECTORY . MAIN05 . '/' ); 





define( 'PATH_ADMIN', PATH . MAIN01 ); 
define( 'PATH_STAFF', PATH . MAIN02 ); 
define( 'PATH_RECORD', PATH . MAIN03 ); 
define( 'PATH_PHARMACY', PATH . MAIN04 ); 
define( 'PATH_EMPLOYEE', PATH . MAIN05 ); 





define( 'TERMS_&_CONDITIONS', '#');

// echo DIRECTORY_ADMIN;
// echo PATH_ADMIN;


function setLocation ( $nowPath ){ echo '<script type="text/javascript">location.href = "' . $nowPath . '" ;</script>'; }
function encrypt($pure_string, $encryption_key = "25c6c7ff35b9979b151f2136cd13b0ff") {
	if ($encryption_key == "25c6c7ff35b9979b151f2136cd13b0ff") {
		return strtr(base64_encode($pure_string), '+/=', '-_,');
	}
	$iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
	$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
	$encrypted_string = mcrypt_encrypt(MCRYPT_BLOWFISH, $encryption_key, utf8_encode($pure_string), MCRYPT_MODE_ECB, $iv);
	return $encrypted_string;
}

/*
 * Returns decrypted original string
 */	
function decrypt($encrypted_string, $encryption_key = "25c6c7ff35b9979b151f2136cd13b0ff") {
	if ($encryption_key == "25c6c7ff35b9979b151f2136cd13b0ff") {
		return base64_decode(strtr($encrypted_string, '-_,', '+/='));
	}

	$iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
	$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
	$decrypted_string = mcrypt_decrypt(MCRYPT_BLOWFISH, $encryption_key, $encrypted_string, MCRYPT_MODE_ECB, $iv);
	return $decrypted_string;
}





?>
