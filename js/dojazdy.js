function $g(element){return document.getElementById(element);}

function pokazPrzejazdy(krok,data)
{
	if(krok==2 && data==0)
	{
		$g('obszar_tabelka').innerHTML='';
		return;
	}
	var SP=new Object();
	var DW=new Object();
	DW.szablon=krok+20; //dla biezacego rozliczenia szablony od 11 wzwyż
	DW.parametr=data;
	$.ajax({ 
		url: "szablony.php",
		async: true,
		method: 'POST',
		timeout: 10000,
		data : {
			"tablica" : JSON.stringify(DW)
		},
		beforeSend : function(xhrObj,status) {
			//waitOn();
		},
		success : function(wyjscie,status,xhrObj) {
			switch(krok)
			{
				case 1:
					$g('obszar_r').innerHTML=wyjscie;
				break;
				case 2:
					$g('obszar_tabelka').innerHTML=wyjscie;
				break;
			}
			
		},
		error : function(xhrObj,status,wyjatek) { 
			//waitOff();
			alert("Błąd komunikacji z serwerem: "+status+' ('+wyjatek +')');
			//****
		}    
	});	
}

function biezaceRozliczenie(krok,data,powrot)
{
	if(krok==2 && data==0)
	{
		$g('obszar_tabelka').innerHTML='';
		return;
	}
	var SP=new Object();
	var DW=new Object();
	DW.szablon=krok+10; //dla biezacego rozliczenia szablony od 11 wzwyż
	DW.parametr=data;
	DW.powrot=powrot;
	$.ajax({ 
		url: "szablony.php",
		async: true,
		method: 'POST',
		timeout: 10000,
		data : {
			"tablica" : JSON.stringify(DW)
		},
		beforeSend : function(xhrObj,status) {
			//waitOn();
		},
		success : function(wyjscie,status,xhrObj) {
			switch(krok)
			{
				case 1:
					$g('obszar_r').innerHTML=wyjscie;
				break;
				case 2:
					$g('obszar_tabelka').innerHTML=wyjscie;
				break;
			}
			
		},
		error : function(xhrObj,status,wyjatek) { 
			//waitOff();
			alert("Błąd komunikacji z serwerem: "+status+' ('+wyjatek +')');
			//****
		}    
	});
}

function dodajDojazdy(krok,pasazerow)
{
	var wierszy=$("tr[name='wiersz_dojazdowy']").length;
	if(pasazerow>wierszy || krok==1)
	{
		var SP=new Object();
		var DW=new Object();
		DW.szablon=krok;
		DW.parametr=pasazerow;
		DW.parametr2=wierszy;
		$.ajax({ 
			url: "szablony.php",
			async: true,
			method: 'POST',
			timeout: 10000,
			data : {
				"tablica" : JSON.stringify(DW)
			},
			beforeSend : function(xhrObj,status) {
				//waitOn();
			},
			success : function(wyjscie,status,xhrObj) {
				switch(krok)
				{
					case 1:
						$g('obszar_r').innerHTML=wyjscie;
					break;
					case 2:
						if(wierszy==0)
							$g('obszar_tabelka').innerHTML=wyjscie;
						else
						{	
							$("table").append(wyjscie);
						}
					break;
				}
				
			},
			error : function(xhrObj,status,wyjatek) { 
				//waitOff();
				alert("Błąd komunikacji z serwerem: "+status+' ('+wyjatek +')');
				//****
			}    
		});
	} else
	{
		$("tr[name='wiersz_dojazdowy']").remove(":not(:lt("+pasazerow+"))");
		if(pasazerow==0)
			$("#tabelka").remove();
	}
}

function sugerujDlugosc(obiekt,id)
{
	var wartosc=obiekt.options[obiekt.selectedIndex].value;
	$g(id).value=wartosc.slice(wartosc.lastIndexOf('_')+1);
}

function zmianaKierowcy(obiekt)
{
	var wiersz=obiekt.id.slice(obiekt.id.lastIndexOf('_')+1);
	var wybrany=obiekt.options[obiekt.selectedIndex].value;
	var pasazerObj=$g('pasazer_'+wiersz);
	pasazerObj.selectedIndex=0;
	for ( var i=0;i < pasazerObj.length; i++)
	{
		if (pasazerObj.options[i].value==wybrany && wybrany!=0)
		{ 
			pasazerObj.options[i].disabled='disabled';

		}else
		{
			pasazerObj.options[i].disabled='';
		}
	}
}

function zmianaPasazera(obiekt){}

function zapiszDojazdy()
{
	var wierszy=$("tr[name='wiersz_dojazdowy']").length;
	var blad=0;
	var zebrane= new Object();
	zebrane.kierowca=[];
	zebrane.pasazer=[];
	zebrane.odcinek=[];
	zebrane.dlugosc=[];
	$("select").each(function(){
		if(this.selectedIndex==0) blad++;
	});
	if(blad > 0)
	{
		alert('Nie wszystkie pola zostały zdefiniowane - popraw !')
		return;
	}
	zebrane.data=$g('dzien_przejazdu').value;
	for(var i=1; i<wierszy+1; i++)
	{
		zebrane.kierowca.push($g('kierowca_'+i).value);
		zebrane.pasazer.push($g('pasazer_'+i).value);
		zebrane.odcinek.push($g('odcinek_'+i).value);
		zebrane.dlugosc.push($g('dlugosc_'+i).value);
	}

	var SP=new Object();
	var DW=new Object();
	DW.zdarzenie='zapisz_dojazdy';
	DW.dane=zebrane;
	DW.sesja='abcde';
	DW.pin=$g("pin").value;
	//alert(JSON.stringify(DW));
	$.ajax({ 
		url: "odbiornik.php",
		async: true,
		method: 'POST',
		timeout: 10000,
		data : {
			"tablica" : JSON.stringify(DW)
		},
		beforeSend : function(xhrObj,status) {
			//waitOn();
		},
		success : function(wyjscie,status,xhrObj) {
			SP=JSON.parse(wyjscie);
			//waitOff();
			if (SP.zezwolenie)
			{
				alert('Rekordy zapisane');
				menu(1);
				
			} else
			{
				alert(SP.komunikat);
				//***
			}
		},
		error : function(xhrObj,status,wyjatek) { 
			//waitOff();
			alert("Błąd komunikacji z serwerem: "+status+' ('+wyjatek +')');
			//****
		}    
	}); 		
}


function menu(pozycja)
{
	switch(pozycja)
	{
		case 1:
			pokazPrzejazdy(1);
		break;
		
		case 2:
			biezaceRozliczenie(1);
		break;
		
		case 3:
			dodajDojazdy(1);
		break;
	}
}

function start()
{

	menu(1);
	//dodajDojazdy(1);
}