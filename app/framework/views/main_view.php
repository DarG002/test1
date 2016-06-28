<h1>Добро пожаловать!asdadsasdasd</h1>
<p>
fff
</p>
<?php
	print_r(PDO::getAvailableDrivers());

	$domain = 'google.ru';
	if (checkdnsrr($domain,"MX") && checkdnsrr($domain,"A")) {
		echo $domain.' - yes';
		$mx = dns_get_record($domain);
		echo "<pre>";
		print_r($mx);
		echo "<pre>";
	} else {
		echo $domain.' - no';
	};

?>