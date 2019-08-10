<?php
include_once('../global.php');
include_once( 'connection.php' ); 
include_once( 'save_image.php' ); 

try { 
	global $a;
	$a = new Database();

} catch (Exception $e) {

}

try {
	date_default_timezone_set("Asia/Kolkata");
} catch (Exception $e) {

}




function get_client_ip() {
	$ipaddress = '';
	if (isset($_SERVER['HTTP_CLIENT_IP']))
		$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
	else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
	else if(isset($_SERVER['HTTP_X_FORWARDED']))
		$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
	else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
		$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
	else if(isset($_SERVER['HTTP_FORWARDED']))
		$ipaddress = $_SERVER['HTTP_FORWARDED'];
	else if(isset($_SERVER['REMOTE_ADDR']))
		$ipaddress = $_SERVER['REMOTE_ADDR'];
	else
		$ipaddress = 'UNKNOWN';
	return $ipaddress;
}

// 
// 
// 
// actions
// 
// 
function myActivity( $remark , $status = 1, $action = 1, $to_who = 0) {
	try {
		global $a;
		$array = array(  "user"     =>  decrypt($_SESSION[SYSTEM_NAME.'userid0'])  ,
			"username"  =>  decrypt($_SESSION[SYSTEM_NAME.'userid'])  ,
		"action"  => $action , //login
		"result"    => (int) $status,
		"to_who"  => $to_who, 
		"remark"    => $remark  ,
		"date" => date("Y-m-d H:i:s")
	);
		$result  = insertInToTable ('tbl_log', $array, $a );	
	} catch (Exception $e) {
		
	}

}
// 0 login
// -1 logout
// 1 normal
// 
// 
// 
// 


function userLogin ( $username, $password , $type, $header_token ) {


	global $a;
	$authentication = false;
	$returnArray = array('success' => -2, 
		'data' => null,
		'remark' => "Invalid Username or Password");

	$query = 'select * from tbl_user where username = :username and password = :password AND delete_status =0 AND login = 1 AND authentication = :type';
	$userid = 0;
	$params = array(
		':username' =>  $username,
		':type' =>  $type,
		':password' =>  md5($password)
	);  
	$user = $a->display( $query, $params );

	
	if( $user ) {
		if($user[0]['username'] == $username &&   md5($password) == $user[0]['password']) {
			$userid = $user[0]['id'];
			$authentication = true;

		} 
	} 


	if( $authentication ){

		$_SESSION[SYSTEM_NAME.'userid0'] = encrypt($user[0]['id']);
		$_SESSION[SYSTEM_NAME.'userid'] = encrypt($username);
		$_SESSION[SYSTEM_NAME.'type'] = encrypt($type);
		$returnArray['success'] = 1;
		$returnArray['remark'] = "yor login successful";
		

	}

	if(  $authentication &&  !is_null($header_token) && ($header_token != $_SESSION[SYSTEM_NAME .'_token'] ) )
		$_SESSION[ SYSTEM_NAME .'_token' ] = $header_token;



	$array = array(  "user"     => $userid ,
		"username"  => $username ,
		"action"  => 0 , //login
		"result"    => (int) $authentication,
		"remark"    => " login action" ,
		"date" => date("Y-m-d H:i:s")
	);
	$result  = insertInToTable ('tbl_log', $array, $a );

	return  $returnArray;



}






function checkUser ( $type ){  
	$returnArray = array('success' => -1, 
		'data' => null,
		'remark' => "session expired, login again "); 

	if(isset($_SESSION[SYSTEM_NAME.'userid']) && isset($_SESSION[SYSTEM_NAME.'type']) )
		if( decrypt($_SESSION[SYSTEM_NAME.'type']) == $type) {
			$returnArray['success'] = 1;
			$returnArray['remark'] = "access granted";
		} 
		return  $returnArray;
	}



	function setProfile( $name , $email, $mobile , $landline , $address , $description, $sex , $facebook , $twitter , $instagram, $image , $type ) {

		global $a;

		$returnArray = array('success' => -2, 
			'data' => null,
			'remark' => "Invalid input");



		$details =  array(
			'name' => $name, 
			'email' => $email, 
			'phone' => $mobile, 
			'landline' => $landline, 
			'address' => $address, 
			'description' => $description, 
			'sex' => $sex, 
			'facebook' => $facebook, 
			'twitter' => $twitter, 
			'instagram' => $instagram ,
			'image' =>  basename($image) 
		);

		$details = json_encode($details);





		try {
			
			$array = array(  "details"     => $details  ,
				"date" => date("Y-m-d H:i:s")
			);
			$result  = updateTable ('tbl_user', $array,  ' id = ' . decrypt($_SESSION[SYSTEM_NAME.'userid0']) . ' AND delete_status = 0 AND authentication =  ' . $type, $a ); 
			$returnArray['success'] = $result;
			$returnArray['remark'] = "data updated successfully";
			myActivity("profile updated");
		} catch (Exception $e) {
			
			$returnArray['success'] = 2;
			$returnArray['remark'] = "invalid data";
		}



		return  $returnArray;
	}


	function getProfile( $type) {

		global $a;

		$returnArray = array('success' => 0, 
			'data' => null,
			'remark' => "Invalid request");



		try {


			$result = selectFromTable ('details', 'tbl_user',  ' id = ' . decrypt($_SESSION[SYSTEM_NAME.'userid0']) . ' AND delete_status = 0 AND authentication =  ' . $type, $a );

			$returnArray['success'] = 3;
			$returnArray['data'] = json_decode($result);
			$returnArray['data']->image = 'files/images/admin/' . $returnArray['data']->image ; 
			$returnArray['remark'] = "data fetching success";


		} catch (Exception $e) {
			
			$returnArray['success'] = 2;
			$returnArray['remark'] = "invalid request";
		}



		return  $returnArray;

	}











	function updateDp ($data, $type) {
		$done = false;
		$path = null;
		$sitedirectory = '../files/images/admin';
		global $a;

		$returnArray = array('success' => 2, 
			'data' => null,
			'remark' => "Invalid input");


		try {
			$path =  saveImageNow($data , $sitedirectory );
			$done = true;
		} catch (Exception $e) {
			$done = false;
		}

		if( $done ) {



			try { 
				$result = selectFromTable ('details', 'tbl_user',  ' id = ' . decrypt($_SESSION[SYSTEM_NAME.'userid0']) . ' AND delete_status = 0 AND authentication =  ' . $type, $a );


				$xarray = json_decode($result);
				$xarray->image = $path['image'];


				$xarray  = json_encode($xarray);

				$array = array(  "details"     => $xarray  ,
					"date" => date("Y-m-d H:i:s")
				);
				$result  = updateTable ('tbl_user', $array,  ' id = ' . decrypt($_SESSION[SYSTEM_NAME.'userid0']) . ' AND delete_status = 0 AND authentication =  ' . $type, $a ); 
				$returnArray['success'] = $result;
				$returnArray['remark'] = "image updated successfully";


				$returnArray['data'] = 'files/images/admin/' . $path['image'];


				myActivity("profile picture updated");

			} catch (Exception $e) {

				$returnArray['success'] = 2;
				$returnArray['remark'] = "invalid request";
			}


		}








		return  $returnArray;
	}



	function getProfileBasic ( $type) {

		global $a;

		$returnArray = array('success' => 0, 
			'data' => null,
			'remark' => "Invalid request");



		try {


			$result = selectFromTable ('details', 'tbl_user',  ' id = ' . decrypt($_SESSION[SYSTEM_NAME.'userid0']) . ' AND delete_status = 0 AND authentication =  ' . $type, $a );

			$returnArray['success'] = 3;
			$tempArray = json_decode($result);

			$returnArray['data']['name']=  $tempArray->name ;  
			$returnArray['data']['email'] =  $tempArray->email ;  
			$returnArray['data']['image'] = 'files/images/admin/' . $tempArray->image ; 


			$returnArray['remark'] = "data fetching success";


		} catch (Exception $e) {
			
			$returnArray['success'] = 2;
			$returnArray['remark'] = "invalid request";
		}



		return  $returnArray;

	}





	function getLog( $type, $from = 0, $limit = 100 ) {

		global $a;

		$returnArray = array('success' => 0, 
			'data' => null ,
			'remark' => "Invalid request");



		try {


			$result = selectFromTable ('  log.id, log.user, log.username, log.action, log.result, log.remark, DATE_FORMAT(log.date, "%Y-%m-%d") AS day , DATE_FORMAT(log.date, "%H:%i:%s")  time, user.username AS to_who   ', ' `tbl_log` log LEFT JOIN tbl_user user ON user.id = log.to_who ',  ' log.user = ' . decrypt($_SESSION[SYSTEM_NAME.'userid0']) . ' AND log.delete_status = 0  ORDER BY log.date DESC LIMIT  ' .  $limit . '  OFFSET ' . $from   , $a );

			$returnArray['success'] = 3;
			$returnArray['data'] =  $result; 
			$returnArray['remark'] = "data fetching success";


		} catch (Exception $e) {
			
			$returnArray['success'] = 2;
			$returnArray['remark'] = "invalid request";
		}



		return  $returnArray;

	}



	function updateLogin( $type, $password, $newpassword  ) {
		global $a;

		$returnArray = array('success' => 2, 
			'data' => null,
			'remark' => "Invalid Current Password");

		$authentication = false;


		try {

			$returnArray['data']  =11;

			$result = selectFromTable (' id, username, password ', '   tbl_user   ',  ' id = ' . decrypt($_SESSION[SYSTEM_NAME.'userid0']) . ' AND delete_status = 0  AND login = 1 AND password = "' . md5($password) .  '"  AND authentication =  ' . $type    , $a );

			

			if(! is_null( $result))
				if( $result[0]['username'] === decrypt($_SESSION[SYSTEM_NAME.'userid']) &&   $result[0]['id'] == decrypt($_SESSION[SYSTEM_NAME.'userid0']) &&  $result[0]['password'] === md5($password)  )
					$authentication = true;

			} catch (Exception $e) {

				$authentication = false;
			}





			if( $authentication && ($password === $newpassword)   ) { 
				$returnArray['success'] = 21;
				$returnArray['remark'] = "password has been previously used. please choose a different one.";
				$authentication = false;
			}



			if( $authentication   ) {



				$array =  array( 
					'password' => md5($newpassword), 
					"date" => date("Y-m-d H:i:s")
				);

				try { 

					$result  = updateTable ('tbl_user', $array,  ' id = ' . decrypt($_SESSION[SYSTEM_NAME.'userid0']) . ' AND delete_status = 0 AND authentication =  ' . $type , $a ); 


					$returnArray['success'] = $result;
					$returnArray['remark'] = "password updated successfully";
					myActivity("password updated ");

				} catch (Exception $e) {

					$returnArray['success'] = 2;
					$returnArray['remark'] = "invalid data";
				}



			} else {

				myActivity( "attempt to change password" , 0, 1, 0);
			}







			return  $returnArray;
		}



		function getCountries() {

			global $a;

			$returnArray = array('success' => 0, 
				'data' => null ,
				'remark' => "Invalid request");



			try {


				$result = selectFromTable ('  state, state  ', ' tbl_states ',  '  1 = 1 ORDER BY  state '    , $a );

				$returnArray['success'] = 3;
				$returnArray['data'] =  $result; 
				$returnArray['remark'] = "data fetching success";


			} catch (Exception $e) {
				
				$returnArray['success'] = 2;
				$returnArray['remark'] = "invalid request";
			}



			return  $returnArray;
		}


		function getCities( $cities ) {


			global $a;

			$returnArray = array('success' => 0, 
				'data' => null ,
				'remark' => "Invalid request");



			try {


				$result = selectFromTable ('  state, cities  ', ' tbl_states ',  '  state = "' . $cities . '"  '    , $a );

				$returnArray['success'] = 3;
				$returnArray['data'] = (array) explode(",", $result[0]['cities'] ) ; 
				$tempArray = array( null );
				foreach ($returnArray['data'] as $value) {
					if( strlen($value) > 1)
						array_push( $tempArray , trim($value) . '' );  
				}

				$returnArray['data'] = $tempArray;
				$returnArray['remark'] = "data fetching success";


			} catch (Exception $e) {
				
				$returnArray['success'] = 2;
				$returnArray['remark'] = "invalid request";
			}



			return  $returnArray;

		}




		function addDoctor( $image, $address, $city, $dob, $email, $fname, $landline, $lname, $location, $oaddress, $officephone, $phone, $pin, $qualification, $remark, $sex, $state    ) {


			$done = false;
			$path = null;
			$sitedirectory = '../files/images/employee';
			global $a;

			$myActivity_status = 0;
			$myActivity_type = 0;
			$myActivity_who = 0;


			$returnArray = array('success' => 2, 
				'data' => null,
				'remark' => "Invalid input");



			$temp  = checkEmail( $email ); 
			if($temp['success'] != 1)  
				return $temp;

			$temp  = checkPhone( $phone ); 
			if($temp['success'] != 1)  
				return $temp;





			if( $image == null )
				$done = true;

			if( !$done  ) {
				try {
					$path =  saveImageNow($image , $sitedirectory );
					$done = true;
				} catch (Exception $e) {
					$done = false;
				}

			}



			if( $done  ) {






				try { 


// ;

					$password = mt_rand();

					$array = array(   
						"username"  => $email,
						"authentication"  => 2,
						"Login"  => 1,
						"Password"  => md5($password), 
						"date" => date("Y-m-d H:i:s")
					);
					$result  = insertInToTable ('tbl_user', $array, $a, true );


					$myActivity_who = $result;

					$array = array(  
						"user_id" => $result,
						"address"  => $address,
						"city"  => $city,
						"dob"  => $dob,
						"email"  => $email,
						"fname"  => $fname,
						"landline"  => $landline,
						"lname"  => $lname,
						"location"  => $location,
						"oaddress"  => $oaddress,
						"officephone"  => $officephone,
						"phone"  => $phone,
						"pin"  => $pin,
						"qualification"  => $qualification,
						"remark"  => $remark,
						"sex"  => $sex,			
						"image" => null,
						"state"  => $state,
						"date" => date("Y-m-d H:i:s")
					);

					if($path != null )		
						if(isset($path['image']))				
							$array["image"] = $path['image'];

						$result  = insertInToTable ('tbl_doctor', $array, $a );



						$returnArray['success'] = $result;
						$returnArray['remark'] = "added successfully";


						$returnArray['data'] = null;


						$myActivity_status = $result;
						$myActivity_type = 1;
					// myActivity("profile picture updated");

					} catch (Exception $e) {

						$returnArray['success'] = 2;
						$returnArray['remark'] = "invalid request";
					}



				}


				myActivity( "attempt to add new doctor " , $myActivity_status, $myActivity_type, $myActivity_who);


				return  $returnArray;

			}

			function checkEmail( $data , $id  = null) {

				global $a;

				$returnArray = array('success' => 2, 
					'data' => null ,
					'remark' => "email already exist");



				try {

					if( $id == null )
						$result = selectFromTable ('  username  ', ' tbl_user ',  '  username = "' . $data . '"  '    , $a );
					else
						$result = selectFromTable ('  username  ', ' tbl_user ',  '  username = "' . $data . '"  AND id != ' . $id    , $a );


					if($result ){
						$returnArray['success'] = 2;
					} else {

						$returnArray['success'] = 1;
						$returnArray['remark'] = " go ";
					}


				} catch (Exception $e) {

					$returnArray['success'] = 2;
					$returnArray['remark'] = "email already exist";
				}



				return  $returnArray;

			}




			function checkPhone( $data , $id  = null ) {

				global $a;

				$returnArray = array('success' => 2, 
					'data' => null ,
					'remark' => "phone number already exist");



				try {

					if( $id == null )
						$result = selectFromTable ('  phone  ', ' tbl_doctor ',  '  phone =  ' . $data . '   '    , $a );
					else
						$result = selectFromTable ('  phone  ', ' tbl_doctor ',  '  phone =  ' . $data . '  AND  user_id != ' . $id    , $a );


					if($result ){
						$returnArray['success'] = 2;
					} else {

						$returnArray['success'] = 1;
						$returnArray['remark'] = " go ";
					}


				} catch (Exception $e) {

					$returnArray['success'] = 2;
					$returnArray['remark'] = "phone number already exist";
				}



				return  $returnArray;

			}




			function getDoctor ( $limit, $offset) {



				global $a;

				$returnArray = array('success' => 0, 
					'data' => null ,
					'remark' => "Invalid request");



				try {

					/*user_id, fname, lname, email, dob, sex, phone, landline, officephone, state, city, address, oaddress, location, pin, qualification, image, remark, delete_status, DATE_FORMAT( date , '%Y-%m-%d') AS date*/

					//IF ( CHAR_LENGTH(fname) > 2 , CONCAT (SUBSTR(fname, 1, 2), "..") , fname ) 
					$result = selectFromTable (' user_id AS id , CONCAT (fname, " ", lname) AS name, email, phone, IF ( CHAR_LENGTH(qualification) > 24 , CONCAT( SUBSTR(qualification, 1, 22), "..."), qualification )AS q, image, delete_status AS d , DATE_FORMAT( date , "%Y-%m-%d") AS date  ', ' tbl_doctor ',  '  1 = 1 ORDER BY  fname ASC LIMIT  ' .  $limit . '  OFFSET ' . $offset    , $a );

					$returnArray['success'] = 3;
					$returnArray['data'] =  $result; 
					$returnArray['remark'] = "data fetching success";


				} catch (Exception $e) {

					$returnArray['success'] = 2;
					$returnArray['remark'] = "invalid request";
				}



				return  $returnArray;
			}


			function  getSingleDoctor( $id ) {


				global $a;

				$returnArray = array('success' => 0, 
					'data' => null ,
					'remark' => "Invalid request");



				try {

					/**/

		//IF ( CHAR_LENGTH(fname) > 2 , CONCAT (SUBSTR(fname, 1, 2), "..") , fname ) 
					$result = selectFromTable (' user_id AS id, fname, lname, email, dob, sex, phone, landline, officephone, state, city, address, oaddress, location, pin, qualification, image, remark, delete_status, DATE_FORMAT( date , "%Y-%m-%d") AS date ', ' tbl_doctor ',  ' user_id = ' . $id  , $a );

					$returnArray['success'] = 3;
					$returnArray['data'] =  $result[0]; 
					$returnArray['remark'] = "data fetching success";


				} catch (Exception $e) {

					$returnArray['success'] = 2;
					$returnArray['remark'] = "invalid request";
				}



				return  $returnArray;
			}


			function updateDoctor(  $id, $image, $address, $city, $dob, $email, $fname, $landline, $lname, $location, $oaddress, $officephone, $phone, $pin, $qualification, $remark, $sex, $state    ) {






				$done = false;
				$path = null;
				$sitedirectory = '../files/images/employee';
				global $a;

				$myActivity_status = 0;
				$myActivity_type = 0;
				$myActivity_who = $id;


				$returnArray = array('success' => 2, 
					'data' => null,
					'remark' => "Invalid input");



				$temp  = checkEmail( $email, $id ); 
				if($temp['success'] != 1)  
					return $temp;

				$temp  = checkPhone( $phone, $id ); 
				if($temp['success'] != 1)  
					return $temp;





				if( $image == null )
					$done = true;

				if( !$done  ) {
					try {
						if(preg_match("/^data:image\/(\w+);base64,/", $image))
							$path =  saveImageNow($image , $sitedirectory );
							else
								$path["image"] = basename($image);
							$done = true;
						} catch (Exception $e) {
							$done = false;
						}

					}



					if( $done   ) {

						$array = array(   
							"username"  => $email,
							"authentication"  => 2,
							"Login"  => 1,
							"date" => date("Y-m-d H:i:s")
						);
						$result  = updateTable ('tbl_user', $array , ' delete_status= 0 and user_id = ' . $id, $a );






						try { 

							$oldImage = selectFromTable ('  image  ', ' tbl_doctor ',  '  user_id =  ' . $id . '   '    , $a);
				// ;



							$array = array(  
								"user_id" => $result,
								"address"  => $address,
								"city"  => $city,
								"dob"  => $dob,
								"email"  => $email,
								"fname"  => $fname,
								"landline"  => $landline,
								"lname"  => $lname,
								"location"  => $location,
								"oaddress"  => $oaddress,
								"officephone"  => $officephone,
								"phone"  => $phone,
								"pin"  => $pin,
								"qualification"  => $qualification,
								"remark"  => $remark,
								"sex"  => $sex,			
								"image" => null,
								"state"  => $state,
								"date" => date("Y-m-d H:i:s")
							);

							if($path != null )		
								if(isset($path['image']))				
									$array["image"] = $path['image'];

								$result  = updateTable ('tbl_doctor', $array, ' delete_status= 0 and user_id = ' . $id , $a );



								$returnArray['success'] = $result;
								$returnArray['remark'] = "updated successfully";


								$returnArray['data'] = null;


								$myActivity_status = $result;
								$myActivity_type = 1;
									// myActivity("profile picture updated");

								$upStatus = unlink($sitedirectory . '/' . $oldImage );

							} catch (Exception $e) {

								$returnArray['success'] = 2;
								$returnArray['remark'] = "invalid request";
							}



						}


						myActivity( "attempt to update doctor " , $myActivity_status, $myActivity_type, $myActivity_who);


						return  $returnArray;

					}


















					?>