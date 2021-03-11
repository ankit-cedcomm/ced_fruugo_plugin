<?php
/**
 * FruugoRequest Class to handle different rquests.
 *
 * @class FruugoRequest
 *
 * @version 1.0.0
 */
if (! class_exists ( 'FruugoRequest' )) {
	class FruugoRequest {
		/**
		 * Fruugo Constructor.
		 */
		public function __construct($fruugoUser = "", $fruugoPass = "") {
			$ced_fruugo_save_details = get_option('ced_fruugo_details');
			$ced_fruugo_keystring = $ced_fruugo_save_details['userString'];
			$ced_fruugo_shared_string = $ced_fruugo_save_details['passString'];
			$this->_fruugoUser = $ced_fruugo_keystring;
			$this->_fruugoPass = $ced_fruugo_shared_string;
			$this->_apiHost = 'https://www.fruugo.com/';
		}

		public function CPostRequest($method,$postFields)
		{

			$url= $this->_apiHost.$method;
			$username = $this->_fruugoUser;
			$password = $this->_fruugoPass;
			$headers = array();
			$headers[] = "Content-Type: application/xml";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
			curl_setopt($ch, CURLOPT_HEADER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
			$serverOutput = curl_exec($ch);
			$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
			$header = substr($serverOutput, 0, $header_size);
			$body = substr($serverOutput, $header_size);
			curl_close($ch);
			return $body;
		}


		public function CGetRequest($method,$params = array())
		{

			$error='';
			$url= $this->_apiHost.$method;
			$username = $this->_fruugoUser;
			$password = $this->_fruugoPass;
			$headers = array();
			$headers[] = "Content-Type: application/x-www-form-urlencoded";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt( $ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); 
			curl_setopt($ch, CURLOPT_TIMEOUT, 400);
			curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password );
			$serverOutput = curl_exec($ch);
			$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
			$header = substr($serverOutput, 0, $header_size);
			$body = substr($serverOutput, $header_size);
			$error=  curl_error($ch);
			curl_close($ch);
			if($error!=''){
			?>
			<div class="notice notice-error is-dismissible">
			<p><?php _e( $error, 'sample-text-domain' ); ?></p>
			</div>
			<?php
			}
			else{
			return $serverOutput;
			}
		}
	}
}