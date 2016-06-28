<?php
class Controller_Test1 extends Controller
{
	private $regex = '/^[_A-Za-z0-9-]+(\.[_A-Za-z0-9-]+)*@[A-Za-z0-9-]+(\.[A-Za-z0-9-]+)*(\.[A-Za-z]{2,4})$/';

	private $data = array();

	function __construct()
	{
		$this->model = new Model_Test1();
		$this->view = new View();
	}

	function index()
	{
		redirect('/');
	}

	function action_index()
	{
		$data['index'] = $this->model->get_data();

		//$data = $this->validate_smtp($this->validate_host($data));

		$this->view->generate('test1_view.php', 'template_view.php', $data);
	}

	function action_check() 
	{
		$email = $_POST['email'];
		$check['email'] = $email;
		$check['host'] = $this->model->split_host($email);
		if ($this->model->validate_email($email)) {
			$check['email_valid'] = true;
			if ($this->model->validate_host($email)) {
				$check['host_mx_valid'] = true;
				getmxrr ($check['host'], $mxhosts, $weight);
				$check['mx_host'] = $this->model->mx_ret($check['host']);
				if ($this->model->validate_smtp($email)) {
					$check['exist'] = 'Yes';
				} else {
					$check['exist'] = 'No';
				}
			} else {
				$check['host_mx_valid'] = 'No';
			}
		} else {
			$check['email_valid'] = 'No valid';
		}

		$data['check'] = $check;
		$this->view->generate('test1_view.php', 'template_view.php', $data);
	}
}
?>