<?php
require_once('config.php');
require_once('funkcje.php');

openlog("DOJAZDY (".basename(__FILE__).")", LOG_PID, LOG_LOCAL0);
if (isset($_POST['tablica']))
{
	$opcje=json_decode($_POST['tablica'],true);
	switch($opcje['zdarzenie'])
	{
		case 'zapisz_dojazdy':
			$zalogowany=zalogowany($database);
			if(!isset($opcje['pin']) OR $opcje['pin']<>$pin OR !$zalogowany)
			{
				$pojemnik['zezwolenie']=false;
				$pojemnik['komunikat']='Błąd zapisu, nie jesteś zalogowany, bądź PIN jest niepoprawny !';					
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
					$zapytanie=	"INSERT INTO `przejazdy`(`pasazer`, `kierowca`, `trasa_id`, `dystans`, `data_przejazdu`, `dodal`) ".
					"VALUES (".$opcje['dane']['pasazer'][$i].",".$opcje['dane']['kierowca'][$i].",".$odcinek[0].",".$opcje['dane']['dlugosc'][$i].",'".$opcje['dane']['data']."',".$zalogowany.")";
					$mysqli->query($zapytanie);
					$id=$mysqli->insert_id;				
				}

				$mysqli->close();
				$pojemnik['zezwolenie']=true;
				$pojemnik['komunikat']='OK';				
			}

		break;
		case 'zaloguj':
			$mysqli = new mysqli($database['host'], $database['user'], $database['password'], $database['name']);
			if ($mysqli->connect_errno) 
			{
				loguj_zakoncz("Błąd połączenia z MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
			}
			$mysqli->set_charset('utf8');
			$haslo= $mysqli->real_escape_string($opcje['haslo']);
			$zapytanie=	"CALL `zaloguj`('".$haslo."');";
			$result = $mysqli->query($zapytanie);
			if($row = $result->fetch_assoc())
			{
				$pojemnik['sesja']=$row['sesja'];
				if(strlen($pojemnik['sesja'])>0)
				{
					$pojemnik['zezwolenie']=true;
					$pojemnik['komunikat']='OK';					
				}else
				{
					$pojemnik['zezwolenie']=false;
					$pojemnik['komunikat']='Błędne hasło, lub konto wygasło';
				}

			}else
			{
				$pojemnik['zezwolenie']=false;
				$pojemnik['komunikat']='Błąd logowania';
				$pojemnik['sesja']='';
			}
			$result->close();
			$mysqli->close();			
		break;		
		default:
			$pojemnik['zezwolenie']=false;
			$pojemnik['komunikat']='Nieobsługiwane zdarzenie '.$opcje['zdarzenie'];	
	}
	echo json_encode($pojemnik);
}
closelog();
?>