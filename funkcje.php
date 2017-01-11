<?php
require_once('config.php');

function zalogowany($database)
{
	if(!isset($_COOKIE['doJazdy_c1']))
		return false;

	$mysqli = new mysqli($database['host'], $database['user'], $database['password'], $database['name']);
	if ($mysqli->connect_errno) 
	{
		loguj_zakoncz("Błąd połączenia z MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
	}
	$mysqli->set_charset('utf8');
	$sesja = $mysqli->real_escape_string($_COOKIE['doJazdy_c1']);
	$zapytanie=	"SELECT id,imie,nazwisko,kierowca FROM slownik_uczestnicy WHERE aktywny_do > NOW() AND aktywny_od < NOW() AND sesja='".$sesja."' AND sesja_wazna > NOW()";
	$result = $mysqli->query($zapytanie);

	if($row = $result->fetch_assoc())
		$wynik=$row['id'];
	else
		$wynik=false;
	$result->close();
	if($wynik!==false)
	{
		clearStoredResults($mysqli);
		$zapytanie=	"UPDATE slownik_uczestnicy su SET su.sesja_wazna=ADDDATE(NOW(),'7') WHERE su.aktywny_do > NOW() AND su.aktywny_od < NOW() AND su.sesja='".$sesja."' AND su.sesja_wazna > NOW()";
		$result = $mysqli->query($zapytanie);		
	}
	
	$mysqli->close();
	return $wynik;
}

function loguj_zakoncz($do_zalogowania)
{
	syslog(LOG_WARNING, $do_zalogowania);
	closelog();
	exit(0);
}

function clearStoredResults($mysqli)
{
    do {
         if ($res = $mysqli->store_result()) {
           $res->free();
         }
        } while ($mysqli->more_results() && $mysqli->next_result());        
}

function dniTygodniaPL($wejscie)
{
	$wejscie=preg_replace('/Monday/','Poniedziałek',$wejscie);
	$wejscie=preg_replace('/Tuesday/','Wtorek',$wejscie);
	$wejscie=preg_replace('/Wednesday/','Środa',$wejscie);
	$wejscie=preg_replace('/Thursday/','Czwartek',$wejscie);
	$wejscie=preg_replace('/Friday/','Piątek',$wejscie);
	$wejscie=preg_replace('/Saturday/','Sobota',$wejscie);
	$wejscie=preg_replace('/Sunday/','Niedziela',$wejscie);
	return $wejscie;
}
?>