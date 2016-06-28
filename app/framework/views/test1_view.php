<h1>TECT 1</h1>
<form action="/test1/check" method="post">
	<input type="email" name="email" value="" placeholder="">
	<input type="submit" name="submit" value="Check">
</form>

<?php
	foreach ($data as $key) {
		if (isset($data['check'])) {
			foreach ($data['check'] as $key => $value) {
				echo '<pre>';
				print_r($key);
				echo ': ';
				print_r($value);
				echo '</pre>';
			}
		}
	}
?>