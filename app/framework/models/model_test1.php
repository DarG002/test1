<?php
class Model_Test1 extends Model
{

	private $regex = '/^[_A-Za-z0-9-]+(\.[_A-Za-z0-9-]+)*@[A-Za-z0-9-]+(\.[A-Za-z0-9-]+)*(\.[A-Za-z]{2,4})$/';

	//все популярные хосты позволяющие делать SMTP верификацию
	private static $mx_smtp_a = array();

	function __construct() {
		self::$mx_smtp_a = $this->get_popular_hosts();
	}

	public function get_data()
	{
		$sql = "SELECT * FROM not_verify_emails";
		$where = array(
				'where' => 'm_mail',
				'like'	=> '"%gmail.com"' 
				);

		$data = db::getAll(db::getWhere($sql, $where));

		return $data;
	}

	function get_popular_hosts()
	{
		$sql = "SELECT host FROM popular_email_hosts";
		$where = array(
				'where' => 'rcptto_enable',
				'like'	=> '"1"'
				);
		
		$query = db::getAll(db::getWhere($sql, $where));
		foreach ($query as $key => $value) {
			$data[$key] = $value['host'];
		}

		return $data;
	}

	function server_validation($host)
	{
		if (isset($host)) {
			return (checkdnsrr($host,"MX") && checkdnsrr($host,"A"));
		} else {
			return false;
		}
	}

	function validate_email($email)
	{
		return (preg_match(@$this->regex, $email));
	}

	function split_host($email)
	{
		list ($user, $host)  = split ("@", $email, 2);
		return $host;
	}

	function validate_host($email)
	{
		if ($this->validate_email($email)) {
			$host = $this->split_host($email);
			if ($this->server_validation($host)) {
				return true;
			}
		}	
		return false;
	}

	function validate_smtp($email) 
	{
		$host = $this->split_host($email);
		$mx_host = $this->mx_ret($host);
		if (in_array($mx_host, self::$mx_smtp_a)) {
			return $this->smtp_verification($email,$host);
		} else {
			return false;
		}
	}

	function mx_ret($host)
	{
		getmxrr ($host, $mxhosts, $weight);
		$res = explode('.',$mxhosts[0]);
		$res = array_slice($res, -2);
		return $res[0].'.'.$res[1];
	}

	// SMTP проверка реальности email
	// Не все хосты позволяют ее делать
	function smtp_verification($email, $host)
	{
		//$i = 0;
		// Проверяем есть ли MX на хотсе
		if (getmxrr ($host, $mxhosts, $weight)) {
			//do {
				$fp = fsockopen($mxhosts[0], 25);
				if ($fp) {
					set_socket_blocking ($fp, true);
					$output = fgets ($fp, 100);
					if (ereg('^220', $output)) {
						fputs ($fp, "HELO $mxhosts[$i]\n"); 
						$output = fgets ($fp, 100);
						fputs ($fp, "MAIL FROM: <info@".$host.">\n"); 
						$output = fgets ($fp, 100); 
						fputs ($fp, "RCPT TO: <$email>\n"); 
						$output = fgets ($fp, 100);
						if (ereg ("^250", $output)) { 
                        	return true;
                        	break;
                    	} else {
                    		return false;
							break;
						}
					}
					fputs ($fp, "QUIT\n"); 
					fclose($fp);
				}
			//	$i++;
			//} while ($i < count($mxhosts));
		} else {
			return false;
		}		
	}

	private function smtp_check($host)
	{
		
	}

}?>