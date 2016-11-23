<?php
require_once('config.php');
openlog("DOJAZDY (".basename(__FILE__).")", LOG_PID, LOG_LOCAL0);

if (isset($_POST['tablica']))
{
	$opcje=json_decode($_POST['tablica'],true);
	switch($opcje['zdarzenie'])
	{
		case 'zapisz_dojazdy':
			if(!isset($opcje['pin']) OR $opcje['pin']<>'236543')
			{
				$pojemnik['zezwolenie']=false;
				$pojemnik['komunikat']='Błąd zapisu, niepoprawny PIN !';					
			}else
			{
				$mysqli = new mysqli($database['host'], $database['user'], $database['password'], $database['name']);
				if ($mysqli->connect_errno) 
				{
					loguj_zakoncz("Błąd połączenia z MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
				}
				$mysqli->set_charset('utf8');
				
				for($i=0;$i<count($opcje['dane']['kierowca']);$i++)
				{
					$odcinek=explode('_',$opcje['dane']['odcinek'][$i]);
					$zapytanie=	"INSERT INTO `przejazdy`(`pasazer`, `kierowca`, `trasa_id`, `dystans`, `data_przejazdu`) ".
					"VALUES (".$opcje['dane']['pasazer'][$i].",".$opcje['dane']['kierowca'][$i].",".$odcinek[0].",".$opcje['dane']['dlugosc'][$i].",'".$opcje['dane']['data']."')";
					$mysqli->query($zapytanie);
					$id=$mysqli->insert_id;				
				}

				$mysqli->close();
				$pojemnik['zezwolenie']=true;
				$pojemnik['komunikat']='OK';				
			}

		break;
		default:
			$pojemnik['zezwolenie']=false;
			$pojemnik['komunikat']='Nieobsługiwane zdarzenie '.$opcje['zdarzenie'];	
	}
	echo json_encode($pojemnik);
}

closelog();
function loguj_zakoncz($do_zalogowania)
{
	syslog(LOG_WARNING, $do_zalogowania);
	closelog();
	exit(0);
}
?>