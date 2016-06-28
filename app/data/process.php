<?
$adminemail = "jon5on@yandex.ru"; //На какой ящик шлем письмо

	// echo '<pre>';
	// print_r($_POST);
	// echo '</pre>';

	$_model = $_POST['model'];
	$_marka = $_POST['marka'];
	$_year = $_POST['year'];
	$_probeg = $_POST['probeg'];
	$_state = $_POST['state'];
	$_cost = $_POST['cost'];
	$_name = $_POST['name'];
	$_phone = $_POST['phone'];

	switch ($_state) {
		case '1':
			$_state = "Отличное";
			break;
		case '2':
			$_state = "Требует ремонта";
			break;
		case '3':
			$_state = "Битое";
			break;
	}

	$return_arr = array();

	if($_phone=="")
	{
		$return_arr["err"] = 'error';
	}
	else
	{

		$to = $adminemail;
		$from = $adminemail;
		$subject = "Заказ выкупа!";

		$message =  'Имя: &nbsp;&nbsp; ' . $_name . '<br>'
					.'Телефон: &nbsp;&nbsp; ' . $_phone . '<br>'
					.'Модель: &nbsp;&nbsp; ' . $_model . '<br>'
					.'Марка: &nbsp;&nbsp; ' . $_marka . '<br>'
					.'Год выпуска: &nbsp;&nbsp; ' . $_year . '<br>'
					.'Пробег: &nbsp;&nbsp; ' . $_probeg . '<br>'
					.'Состояние: &nbsp;&nbsp; ' . $_state . '<br>'
					.'Желаемая цена: &nbsp;&nbsp; ' . $_cost . '<br>';

		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
		$headers .= "Content-Transfer-Encoding: 7bit\r\n";
		$headers .= "From: " . $from . "\r\n";

		@mail($to, $subject, $message, $headers);	

	}

	echo json_encode($return_arr);

?>