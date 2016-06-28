function submitForm() {

	var regexpPhone = /^((8|7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$/,
		phone = $("#phone").val();

	if (phone.match(regexpPhone)) {
		$.post("../data/process.php",
			$("#send-message").serialize(), "json");
			$("#send-message").trigger('reset');
			$("#send_success").modal('show');
		return true;
	}else{
		$("#phone").addClass("has-error");
	}
	return false;

}
// Обработка модальной формы
function submitFormModal() {

	var regexpPhone = /^((8|7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$/,
		phone = $("#send-message-modal #phone").val();
		console.log(phone);

	if (phone.match(regexpPhone)) {
		$.post("../data/process.php",
			$("#send-message-modal").serialize(), "json");
			$(".bs-example-modal-lg").modal('hide');
			$("#send-message-modal").trigger('reset');
			$("#send_success").modal('show');
		return true;
	}else{
		$("#send-message-modal #phone").addClass("has-error");
	}
	return false;

}