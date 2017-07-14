<?php

class poule_update{
	public function __construct($old_version){
		
		//new
		if($old_version < 2.2){
			require_once(POULE_PATH . 'update/update-old.php');
		}else{
			switch ($old_version) {
				case '2.2':
					require_once(POULE_PATH . 'update/update-22.php'); //OK
					break;
				case '2.2.0.0.1':
					require_once(POULE_PATH . 'update/update-22001.php'); //OK
					break;
				case '2.2.0.0.2':
					require_once(POULE_PATH . 'update/update-22002.php'); //OK
					break;
				case '2.2.0.1':
					require_once(POULE_PATH . 'update/update-2201.php');
					break;
				case '2.2.0.1.1':
					require_once(POULE_PATH . 'update/update-22011.php');
					break;
				case '2.2.0.1.2':
					require_once(POULE_PATH . 'update/update-22012.php');
					break;
				case '2.2.0.2':
					require_once(POULE_PATH . 'update/update-2202.php');
					break;
			}
		}
	}
}
?>