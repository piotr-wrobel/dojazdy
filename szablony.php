<?php
require_once('config.php');
openlog("DOJAZDY (".basename(__FILE__).")", LOG_PID, LOG_LOCAL0);
if (isset($_POST['tablica']))
{
	$opcje=json_decode($_POST['tablica'],true);
	switch($opcje['szablon'])
	{
		case '1':
?>
			<div class="obszar_belka">
			<div class="pozycja_belka">
			<span>Zdefiniuj przejazdy w dniu</span>
			<select NAME="dzien_przejazdu" SIZE="1" id="dzien_przejazdu">
				<OPTION VALUE="0"    	>-
<?php
				for($i=0;$i<5;$i++)
				{
?>
					<OPTION VALUE="<?php echo date('Y/m/d',strtotime('-'.$i.' days'));?>"    	><?php echo date('Y/m/d l',strtotime('-'.$i.' days'));?>
<?php					
				}
?>
			</select>
			</div>
			<div class="pozycja_belka">
			<span>Wybierz ilość odcinków składowych</span>
			<select NAME="ile_pasazerow" SIZE="1" id="ile_pasazerow" onChange="dodajDojazdy(2,this.options[this.selectedIndex].value)">
				<OPTION VALUE="0"    	>-
				<OPTION VALUE="1"    	>1
				<OPTION VALUE="2" 		>2
				<OPTION VALUE="3" 		>3
				<OPTION VALUE="4" 		>4
				<OPTION VALUE="5" 		>5
			</select>
			</div>
			</div>
			<div class="obszar_tabelka" id="obszar_tabelka"></div>
<?php		
		break;
		case '2':
			if($opcje['parametr2']==0)
			{
?>
				<div id="tabelka">
				<table class="tabelka"><tr><th>Kierowca</th><th>Pasażer</th><th>Odcinek</th><th>Dystans (km)</th></tr>
<?php			
			}

			$mysqli = new mysqli($database['host'], $database['user'], $database['password'], $database['name']);
			if ($mysqli->connect_errno) 
			{
				loguj_zakoncz("Błąd połączenia z MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
			}
			$mysqli->set_charset('utf8');
			$zapytanie=	"SELECT id,imie,nazwisko,kierowca FROM slownik_uczestnicy WHERE aktywny_do > NOW() AND aktywny_od < NOW() ORDER BY imie,nazwisko";
			 //error_log($zapytanie);
			$result = $mysqli->query($zapytanie);

			while ($row = $result->fetch_assoc())
			{
				$uczestnicy[$row['id']]['imie']=$row['imie'];
				$uczestnicy[$row['id']]['nazwisko']=$row['nazwisko'];
				$uczestnicy[$row['id']]['kierowca']=$row['kierowca'];

			}
			$mysqli->close();


			$mysqli = new mysqli($database['host'], $database['user'], $database['password'], $database['name']);
			if ($mysqli->connect_errno) 
			{
				loguj_zakoncz("Błąd połączenia z MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
			}
			$mysqli->set_charset('utf8');
			$zapytanie=	"SELECT id,nazwa,dlugosc FROM slownik_odcinki WHERE aktywny=1 ORDER BY nazwa";
			 //error_log($zapytanie);
			$result = $mysqli->query($zapytanie);

			while ($row = $result->fetch_assoc())
			{
				$odcinki[$row['id']]['nazwa']=$row['nazwa'];
				$odcinki[$row['id']]['dlugosc']=$row['dlugosc'];

			}
			$mysqli->close();			
			
			for($i=$opcje['parametr2']+1;$i<=$opcje['parametr'];$i++)
			{
?>
				<tr name="wiersz_dojazdowy">
				<td>
					<select NAME="kierowca_<?php echo $i;?>" SIZE="1" id="kierowca_<?php echo $i;?>" onChange="zmianaKierowcy(this)">
					<OPTION VALUE="0"    	>-
<?php					
					foreach($uczestnicy as $id=>$uczestnik)
					{
						if($uczestnik['kierowca'])
							echo "<OPTION VALUE=\"".$id."\">".$uczestnik['imie']." ".$uczestnik['nazwisko'];
					}
?>
					</select>
				</td>
				<td>
					<select NAME="pasazer_<?php echo $i;?>" SIZE="1" id="pasazer_<?php echo $i;?>" onChange="zmianaPasazera(this)">
					<OPTION VALUE="0"    	>-
<?php					
					foreach($uczestnicy as $id=>$uczestnik)
					{
						echo "<OPTION VALUE=\"".$id."\">".$uczestnik['imie']." ".$uczestnik['nazwisko'];
					}
?>
					</select>
				</td>
				<td>
					<select NAME="odcinek_<?php echo $i;?>" SIZE="1" id="odcinek_<?php echo $i;?>" onChange="sugerujDlugosc(this,'dlugosc_<?php echo $i;?>')">
					<OPTION VALUE="0_0" >-
<?php					
					foreach($odcinki as $id=>$odcinek)
					{
							echo "<OPTION VALUE=\"".$id."_".$odcinek['dlugosc']."\" >".$odcinek['nazwa']." (".$odcinek['dlugosc']."km)";
					}
?>
					</select>
				</td>
				<td>
					<input value="0" size=3 type=input id="dlugosc_<?php echo $i;?>"/>
				</td>
				</tr>
<?php
			}
			if($opcje['parametr2']==0)
			{
?>
				</table>
				<div class="pin_zapisu">Podaj PIN: <input size="6" type="password" id="pin"></div>
				<div><input id="zapisz_dojazdy" onclick="zapiszDojazdy()" value="Zapisz" type="button"></div>
				</div>
<?php			
			}
		break;
		case '11': //Szablon 1 dla rozliczenia
?>
			<div class="obszar_belka">
			<div class="pozycja_belka">
			<span>Pokaż rozliczenie po dniu: </span>
			<select NAME="dzien_rozliczenia" SIZE="1" id="dzien_rozliczenia" onChange="biezaceRozliczenie(2,this.options[this.selectedIndex].value)">
				<OPTION VALUE="0"    	>-
<?php
				$mysqli = new mysqli($database['host'], $database['user'], $database['password'], $database['name']);
				if ($mysqli->connect_errno) 
				{
					loguj_zakoncz("Błąd połączenia z MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
				}
				$mysqli->set_charset('utf8');
				$zapytanie=	"SELECT data FROM (SELECT DATE_FORMAT(data_przejazdu, '%Y/%m/%d %W') AS data ".
							"FROM `przejazdy` UNION ALL SELECT DATE_FORMAT(NOW(), '%Y/%m/%d %W') AS data) as tabelka ".
							"GROUP BY data ORDER BY data DESC LIMIT 30";
				 //error_log($zapytanie);
				$result = $mysqli->query($zapytanie);

				while ($row = $result->fetch_assoc())
				{
?>
					<OPTION VALUE="<?php echo $row['data'];?>"    	><?php echo $row['data'];?>
<?php

				}
				$mysqli->close();
?>
			</select>
			</div>
			</div>
			<div class="obszar_tabelka" id="obszar_tabelka"></div>
<?php		
		break;
		case '12': //Szablon 2 dla rozliczenia
?>
			<div id="tabelka">
			<table class="tabelka">
<?php
			if(isset($opcje['powrot']))
			{
?>
			<tr><th colspan="3" style="border:1px solid red">Rozliczenie po dniu <?php echo $opcje['parametr'];?></th></tr>				
<?php
			}
?>			
			<tr><th>Kto winny</th><th>Komu winny</th><th>Dystans (km)</th></tr>
<?php
			$mysqli = new mysqli($database['host'], $database['user'], $database['password'], $database['name']);
			if ($mysqli->connect_errno) 
			{
				loguj_zakoncz("Błąd połączenia z MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
			}
			$mysqli->set_charset('utf8');
			$zapytanie=	"CALL `rozliczenie_na_dzien`('".date('Y-m-d',strtotime($opcje['parametr'].' +1day'))."');";
			$result = $mysqli->query($zapytanie);
			while ($row = $result->fetch_assoc())
			{

				$color_r=rand(150,255);
				$color_g=rand(150,255);
				$color_b=rand(150,255);
?>
				<tr style="background-color:rgb(<?php echo $color_r.','.$color_g.','.$color_b; ?>)">
				<td>
					<?php echo $row['kto_winny'];?>
				</td>
				<td>
					<?php echo $row['komu_winny'];?>
				</td>
				<td>
					<?php echo $row['ile'];?>
				</td>
				</tr>
<?php
			}
			$result->close();
			$mysqli->close();			
?>
			</table>
<?php
			if(isset($opcje['powrot']))
			{
?>
			<div><input type="button" id="powrot" onclick="pokazPrzejazdy(2,'<?php echo $opcje['powrot'];?>')" value="Powrót"></div>				
<?php
			}
?>			
			</div>
<?php			
		break;
		case '21':
?>
			<div class="obszar_belka">
			<div class="pozycja_belka">
			<span>Pokaż przejazdy w miesiącu: </span>
			<select NAME="miesiac_przejazdow" SIZE="1" id="miesiac_przejazdow" onChange="pokazPrzejazdy(2,this.options[this.selectedIndex].value)">
				<OPTION VALUE="0"    	>-
<?php
				$mysqli = new mysqli($database['host'], $database['user'], $database['password'], $database['name']);
				if ($mysqli->connect_errno) 
				{
					loguj_zakoncz("Błąd połączenia z MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
				}
				$mysqli->set_charset('utf8');
				$zapytanie=	"SELECT DATE_FORMAT(data_przejazdu, '%m/%Y') AS data FROM `przejazdy` GROUP BY DATA ORDER by data DESC";
				 //error_log($zapytanie);
				$result = $mysqli->query($zapytanie);

				while ($row = $result->fetch_assoc())
				{
?>
					<OPTION VALUE="<?php echo $row['data'];?>"    	><?php echo $row['data'];?>
<?php

				}
				$mysqli->close();
?>
			</select>
			</div>
			</div>
			<div class="obszar_tabelka" id="obszar_tabelka"></div>
<?php
		break;
		case '22':
?>
			<div id="tabelka">
			<table class="tabelka"><tr><th>Kierowca</th><th>Pasażer</th><th>Trasa</th><th>Dystans (km)</th><th>Data przejazdu</th></tr>
<?php
			$rozbite=explode('/',$opcje['parametr']);
			$data_od=date('Y-m-d H:i:s',strtotime($rozbite[1].'/'.$rozbite[0].'/01 00:00:00 -1second'));
			$data_do=date('Y-m-d H:i:s',strtotime($rozbite[1].'/'.$rozbite[0].'/01 00:00:00 +1month'));
			$mysqli = new mysqli($database['host'], $database['user'], $database['password'], $database['name']);
			if ($mysqli->connect_errno) 
			{
				loguj_zakoncz("Błąd połączenia z MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
			}
			$mysqli->set_charset('utf8');
			$zapytanie=	"SELECT kierowca,pasazer,trasa,dystans,DATE_FORMAT(data_przejazdu, '%Y/%m/%d') AS data_przejazdu,DAY(data_przejazdu) AS dzien 
							FROM `przejazdy_pelny` WHERE data_przejazdu > '".$data_od."' AND data_przejazdu < '".$data_do."'  ORDER BY data_przejazdu,id";
			//error_log($zapytanie);
			$result = $mysqli->query($zapytanie);
			$dzien=0;
			while ($row = $result->fetch_assoc())
			{
				if($dzien<>$row['dzien'])
				{
					$dzien=$row['dzien'];
					$color_r=rand(150,255);
					$color_g=rand(150,255);
					$color_b=rand(150,255);
				}
?>
				<tr onclick="biezaceRozliczenie(2,'<?php echo $row['data_przejazdu'];?>','<?php echo $opcje['parametr'];?>')" title="Kliknij by zobaczyć rozliczenie po tym dniu..." class="wiersz_rozliczenia" style="background-color:rgb(<?php echo $color_r.','.$color_g.','.$color_b; ?>)">
				<td class="wiersz_rozliczenia">
					<?php echo $row['kierowca'];?>
				</td>
				<td class="wiersz_rozliczenia">
					<?php echo $row['pasazer'];?>
				</td>
				<td class="wiersz_rozliczenia">
					<?php echo $row['trasa'];?>
				</td>
				<td>
					<?php echo $row['dystans'];?>
				</td>
				<td class="wiersz_rozliczenia">
					<?php echo $row['data_przejazdu'].' '.date('l',strtotime($row['data_przejazdu']));?>
				</td>					
				</tr>
<?php
			}
			$result->close();
			$mysqli->close();			
?>
			</table>
			</div>
<?php	
		break;
	}
}
closelog();

function loguj_zakoncz($do_zalogowania)
{
	syslog(LOG_WARNING, $do_zalogowania);
	closelog();
	exit(0);
}

function clearStoredResults(){
    global $mysqli;

    do {
         if ($res = $mysqli->store_result()) {
           $res->free();
         }
        } while ($mysqli->more_results() && $mysqli->next_result());        

}
?>
