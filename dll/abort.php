<?php
														
														$dir = file::inst(dirname($logfile).'/site/');$dir->delete();
														unlink($logfile);
														unset($_SESSION['wlinks']);
														unset($_SESSION['wlinksHead']);
														unset($_SESSION['assets']);
												http_response_code(200);
												die('2');

?>