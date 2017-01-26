<?php 
// elõválasztási rendszer konfuguráció
class EvConfig {
  public $belsoSzavazasok = array(123,124,125,126,127,128);	 // categories
  public $oevkSzavazasok = array();  // categories
  public $orszagosListaSzavazasok = array(121);  // categories
  public $miniszterElnokSzavazasok = array(122); // categories
  public $jeloltAdd = true; // enabled
  public $jeloltEdit = true; // enabled
  public $jeloltDelete = true; // enabled
  public $szavazas = true; // enabled
  public $szavazatDelete = true; // enabled
  public $szavazatEdit = true; // enabled
  public $eredmeny = true; // enabled
  public $fordulo = 0;  // esetleges több fordulós választásokhoz 
  public $canAssurance = false; // csak az szavazhat akinál az assurance listában szerepel a szavazás->title
  
  function __construct() {
	  for ($i=9; $i<=114; $i++) {
		  $this->oevkSzavazasok[] = $i;
	  }
  }
  
  public function userAdmin($user) {
	  return (($user->groups[8] == 8) | ($user->groups[10] == 10));
  }
}
global $evConfig;
$evConfig = new EvConfig();

/*
openssl_public_encrypt($plaintext, $encrypted, $publicKey);
$pubKey = '';
$privKey = '';
$config = array(
    "digest_alg" => "sha512",
    "private_key_bits" => 2048,
    "private_key_type" => OPENSSL_KEYTYPE_RSA,
);
// Create the keypair
$res = openssl_pkey_new($config);
echo JSON_encode($res).'<br /><br />';
// Get private key
openssl_pkey_export($res, $privKey);
// Get public key
$pubKey = openssl_pkey_get_details($res);
$pubKey = $pubKey["key"];
echo 'pubKey='.JSON_encode($pubKey).'<br />';
*/
?>