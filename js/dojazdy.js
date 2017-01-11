function $g(element){return document.getElementById(element);}
function procesujStatus(tresc)
{
	var otwierajacy='<status>';
	var zamykajacy='</status>';
	var zamkniecie=tresc.lastIndexOf(zamykajacy);
	if(zamkniecie<0)
		return {status:'wyloguj',html:''}; // Nie było węzła </status> - na drzewo
	
	var html=zamkniecie+zamykajacy.length; // Mamy indeks następujące po weźle status znaku, to już treść html
	html=tresc.slice(html); // Wyłuskujemy tresc html
	var status=tresc.slice(0,zamkniecie); // Przed wezlem </status> mamy zawartosc statusu, ale z tagiem <status>
	var otwarcie=status.indexOf(otwierajacy); //Szukamy <status>
	if(otwarcie<0)
		return {status:'wyloguj',html:''}; // Nie bylo węzła <status> - na drzewo
	
	status=status.slice(otwarcie+otwierajacy.length); //Wyluskujemy czysty status
	return {status:status,html:html};
}

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
		},
		success : function(wyjscie,status,xhrObj) {
			wyjscie=procesujStatus(wyjscie);
			switch(krok)
			{
				case 1:
					$g('obszar_r').innerHTML=wyjscie.html;
				break;
				case 2:
					$g('obszar_tabelka').innerHTML=wyjscie.html;
				break;
			}
			if(wyjscie.status!='ok')
			{
				wyloguj(true);
			}
		},
		error : function(xhrObj,status,wyjatek) { 
			alert("Błąd komunikacji z serwerem: "+status+' ('+wyjatek +')');
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
		},
		success : function(wyjscie,status,xhrObj) {
			wyjscie=procesujStatus(wyjscie);
			switch(krok)
			{
				case 1:
					$g('obszar_r').innerHTML=wyjscie.html;
				break;
				case 2:
					$g('obszar_tabelka').innerHTML=wyjscie.html;
				break;
			}				
			if(wyjscie.status!='ok')
			{
				wyloguj(true);
			}
		},
		error : function(xhrObj,status,wyjatek) { 
			alert("Błąd komunikacji z serwerem: "+status+' ('+wyjatek +')');
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
			},
			success : function(wyjscie,status,xhrObj) {
				wyjscie=procesujStatus(wyjscie);
				switch(krok)
				{
					case 1:
						$g('obszar_r').innerHTML=wyjscie.html;
					break;
					case 2:
						if(wierszy==0)
							$g('obszar_tabelka').innerHTML=wyjscie.html;
						else
						{	
							$("table").append(wyjscie.html);
						}
					break;
				}				
				if(wyjscie.status!='ok')
				{
					wyloguj(true);
				}
			},
			error : function(xhrObj,status,wyjatek) { 
				alert("Błąd komunikacji z serwerem: "+status+' ('+wyjatek +')');
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
	$.ajax({ 
		url: "odbiornik.php",
		async: true,
		method: 'POST',
		timeout: 10000,
		data : {
			"tablica" : JSON.stringify(DW)
		},
		beforeSend : function(xhrObj,status) {
		},
		success : function(wyjscie,status,xhrObj) {
			SP=JSON.parse(wyjscie);
			if (SP.zezwolenie)
			{
				alert('Rekordy zapisane');
				menu(1);
				
			} else
			{
				alert(SP.komunikat);
			}
		},
		error : function(xhrObj,status,wyjatek) { 
			alert("Błąd komunikacji z serwerem: "+status+' ('+wyjatek +')');
		}    
	}); 		
}

function wyloguj(komunikat)
{
	var ciasteczka = document.cookie;
	if(ciasteczka.indexOf('doJazdy_c1')>-1)
	{	
		if(typeof komunikat!='undefined' && komunikat==true)
			alert('Twoja sesja wygasła, zaloguj się ponownie !');
		setCookie('doJazdy_c1', '', -1);
		location.reload(true);
	}
}


function setCookie(cname, cvalue, exdays) {
    if(exdays>=0)
	{
		var d = new Date();
		d.setTime(d.getTime() + (exdays*24*60*60*1000));
		var expires = "expires="+ d.toUTCString();
    }else
	{
		var expires = "expires=Thu, 01 Jan 1970 00:00:00 UTC";
		cvalue='';
	}
	document.cookie = cname + "=" + cvalue + ";" + expires + ";path="+location.pathname+";secure=true;";
}

function zaloguj(password)
{

	var SP=new Object();
	var DW=new Object();
	DW.zdarzenie='zaloguj';
	DW.haslo=password;
	$.ajax({ 
		url: "odbiornik.php",
		async: true,
		method: 'POST',
		timeout: 10000,
		data : {
			"tablica" : JSON.stringify(DW)
		},
		beforeSend : function(xhrObj,status) {
		},
		success : function(wyjscie,status,xhrObj) {
			SP=JSON.parse(wyjscie);
			if (SP.zezwolenie)
			{
				setCookie('doJazdy_c1', SP.sesja, 180);
				location.reload(true);
				
			} else
			{
				alert(SP.komunikat);
			}
		},
		error : function(xhrObj,status,wyjatek) { 
			alert("Błąd komunikacji z serwerem: "+status+' ('+wyjatek +')');
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
		case 4:
			password_prompt("Podaj kod dostępu:", "Zaloguj", function(password) {
				if(password.length>3)
					zaloguj(password);
			});
		break;
		case 5:
			wyloguj();
		break;
	}
}

function start()
{
	menu(1);
}