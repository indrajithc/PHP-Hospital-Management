<?php include_once('../global.php'); ?>
<?php



/*
 * ==== 1 =>  admin 
 * ==== 2 =>  staff 
 * ==== 3 =>  record
 * ==== 4 =>  pharmacy 
 * ==== 5 =>  employee 
*/


	// Authenticate user login
function auth_login() {


	if( ! isset( $_SESSION[ SYSTEM_NAME . 'userid'] )   ) {
		$cx090 = explode("/", dirname($_SERVER['SCRIPT_NAME']) ) ;
		$current_now = $cx090[count($cx090)-1];

		$dest = ROOT . $_SERVER['REQUEST_URI'];
		$_SESSION['TO'] = $dest; 

		if($current_now == MAIN01)
			header('Location:'. PATH . 'admin-login');
		else
			header('Location:' . PATH );
		exit();
	}  
	
	$flag = true;


	if( $_SESSION[ SYSTEM_NAME . 'type'] == 1 && dirname($_SERVER['SCRIPT_NAME']) . '/' !=  DIRECTORY_ADMIN )  
		$flag = false; 
	if( $_SESSION[ SYSTEM_NAME . 'type'] == 2 && dirname($_SERVER['SCRIPT_NAME']) . '/' !=  DIRECTORY_STAFF )  
		$flag = false; 
	if( $_SESSION[ SYSTEM_NAME . 'type'] == 3 && dirname($_SERVER['SCRIPT_NAME']) . '/' !=  DIRECTORY_RECORD )  
		$flag = false; 
	if( $_SESSION[ SYSTEM_NAME . 'type'] == 4 && dirname($_SERVER['SCRIPT_NAME']) . '/' !=  DIRECTORY_PHARMACY )  
		$flag = false; 
	if( $_SESSION[ SYSTEM_NAME . 'type'] == 5 && dirname($_SERVER['SCRIPT_NAME']) . '/' !=  DIRECTORY_EMPLOYEE )  
		$flag = false; 


	if( !$flag ) {
		header('Location:' . PATH );
		exit();
	}
}







function auth_use() {


	if( isset( $_SESSION[ SYSTEM_NAME . 'userid'] )   ) { 

		switch (user_type()) {
			case 1:
			setLocation(PATH_ADMIN);
			break;
			case 2:
			setLocation(PATH_STAFF);
			break;
			case 3:
			setLocation(PATH_RECORD);
			break;
			case 4:
			setLocation(PATH_PHARMACY);
			break;
			case 5:
			setLocation(PATH_EMPLOYEE);
			break;

			default:
		 		# code...
			break;
		} 

	}
	

}
	// get logged user type
function user_type() {
	return decrypt($_SESSION[SYSTEM_NAME . 'type']);
}


?>