<?php
class mail {
    public function send($text) {

    $adresat = '001caton@gmail.com'; 	// pod ten adres zostanie wysłana 
	@$email = $_POST['email'];
	@$content = $_POST['content'];
	$header = 	"From: api \nContent-Type:".
			' text/plain;charset="iso-8859-2"'.
			"\nContent-Transfer-Encoding: 8bit";
	if (mail($adresat,'www.gsmkody.test.koziolek.biz',$text, $header))
		echo json_encode(array('MESSAGE'=>array(array('MESSAGE'=>'The error message has been sent.'))));
       }
}
?>