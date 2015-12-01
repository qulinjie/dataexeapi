<?php
//create pem file
//run openssl genrsa -out key.pem 1024
//This file is generated variables needed for the operation
list($keylength, $modulus, $public, $private,$modulus_js,$private_js) = read_ssl_key("key.pem");
echo "keylength:(php and js)(private length)<br>";
echo $keylength;
echo "<br>";
echo "modulus:(php)(10)(pubic key)<br>";
echo $modulus;
echo "<br>";
echo "modulus:(js)(16)(pubic key)<br>";
echo $modulus_js;
echo "<br>";
echo "public:(php)(10)(public exponent)<br>";
echo $public;
echo "<br>";
echo "public:(js)(16)(public exponent)<br>";
echo "10001";
echo "<br>";
echo "private:(php)(10)(private key)<br>";
echo $private;
echo "<br>";
echo "private:(js)(16)(private key)<br>";
echo $private_js;


//function 
function read_ssl_key($filename)
	{
		exec("openssl rsa -in $filename -text -noout", $raw); 

		// read the key length
		$keylength = (int) expect($raw[0], "Private-Key: (");

		// read the modulus
		expect($raw[1], "modulus:");
		for($i = 2; $raw[$i][0] == ' '; $i++) $modulusRaw .= trim($raw[$i]);

		// read the public exponent
		$public = (int) expect($raw[$i], "publicExponent: "); 

		// read the private exponent
		expect($raw[$i + 1], "privateExponent:");
		for($i += 2; $raw[$i][0] == ' '; $i++) $privateRaw .= trim($raw[$i]);

		// Just to make sure
		expect($raw[$i], "prime1:");

		// Conversion to decimal format for bcmath 
		$modulus = bc_hexdec($modulusRaw);
		$private = bc_hexdec($privateRaw);

		return array($keylength, $modulus['php'], $public, $private['php'],$modulus['js'], $private['js']);
	}
	
	/*
	 * Convert a hexadecimal number of the form "XX:YY:ZZ:..." to decimal 
	 * Uses BCmath, but the standard normal hexdec function for the components
	 */
	function bc_hexdec($hex)
	{
		$coefficients = explode(":", $hex);
		$result_js= implode("",$coefficients);
		$i = 0;
		$result = 0;
		foreach(array_reverse($coefficients) as $coefficient)
		{
			$mult = bcpow(256, $i++);
			$result = bcadd($result, bcmul(hexdec($coefficient), $mult));
		}

		return array('php'=>$result,'js'=>$result_js);
	}
		/*
	 * If the string has the given prefix, return the remainder. 
	 * If not, die with an error
	 */
	function expect($str, $prefix)
	
	{
		if(substr($str, 0, strlen($prefix)) == $prefix)
			return substr($str, strlen($prefix));
		else
			die("Error: expected $prefix");
	}