<!DOCTYPE html>
<html>
<head>
<title>Google Map</title>
<script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>
<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyDthiiVry00NZN8ZhvbCetA9a3jUMfZw9s&sensor=false"></script>
</head>
<body>
      <style>
      /* Always set the map height explicitly to define the size of the div
       * element that contains the map. */
      #google_map {
        height: 50%;
        width: 60%;
      }
      /* Optional: Makes the sample page fill the window. */
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }
      
    </style>

<div id="google_map"></div>
<script type="text/javascript">
    
    
$(document).ready(function() {



	var mapCenter = new google.maps.LatLng(41.55032, -8.42005); //Google map Coordinates
	var map;
        var i=0;
	map_initialize(); // initialize google map
	
	//############### Google Map Initialize ##############
	function map_initialize()
	{
			var googleMapOptions = 
			{ 
				center: mapCenter, // map center
				zoom: 17, //zoom level, 0 = earth view to higher value
				maxZoom: 18,
				minZoom: 3,
				zoomControlOptions: {
				style: google.maps.ZoomControlStyle.SMALL //zoom control size
			},
				scaleControl: true, // enable scale control
				mapTypeId: google.maps.MapTypeId.ROADMAP // google map type
			};
		
		   	map = new google.maps.Map(document.getElementById("google_map"), googleMapOptions);			
			
			//Load Markers from the XML File, Check (map_process.php)
                        //CARREGA OS PONTOS
			$.get("map_process.php", function (data) {
				$(data).find("marker").each(function () {
					  var name 		= $(this).attr('name');
					  var address 	= '<p>'+ $(this).attr('address') +'</p>';
					  
					  var point 	= new google.maps.LatLng(parseFloat($(this).attr('lat')),parseFloat($(this).attr('lng')));
					  create_marker(point, name, address, false, false, false, "icons/pin_blue.png");
				});
			});	
			
			//Right Click to Drop a New Marker ADICIONAR UM NOVO MARCO
			google.maps.event.addListener(map, 'rightclick', function(event) {
                        addListing(event.latLng);
				//Edit form to be displayed with new marker
				

				//Drop a new Marker with our Edit Form
				//create_marker(event.latLng, 'New Marker', EditForm, true, true, false, "icons/pin_green.png");
			});
	
        
        
	}
	
   
   function addListing(location) {
    
        var addMarker;
          var iMax=1;

          if(i<iMax) {
         i++;
 
        var EditForm = '<p><div class="marker-edit">'+
				'<form action="ajax-save.php" method="POST" name="SaveMarker" id="SaveMarker">'+
				'<label for="pName"><span class="span">Nome da Rua :</span> <br /> <input type="text" name="pName" class="save-name" placeholder="Ex: Rua D. Frei Caetano" maxlength="40" /></label> <br /> <br />'+
				'<label for="pDesc"><span class="span">Descrição da Rua :</span> <br /> <br /><textarea name="pDesc" class="save-desc" placeholder="Insira uma descrição do Local " maxlength="150"></textarea></label>'+
                                ' <br />'+
                                 '</form>'+
				'</div>\n</p><button name="save-marker" class="save-marker">Guardar Localização</button>';
           create_marker(location, 'New Marker', EditForm, true, true, false, "icons/pin_green.png");
                        
          

          google.maps.event.addListener(addMarker, 'dblclick', function() {
            addMarker.setMap(null);
            i=1;
          });

         
          } else {
              console.log('you can only post' , i-1, 'markers');
              }
        }

        
	//############### Create Marker Function ############## FUNÇAO DE CRIAR NOVO MARCO
	function create_marker(MapPos, MapTitle, MapDesc,  InfoOpenDefault, DragAble, Removable, iconPath)
	{	  	  		  
		
		//new marker
		var marker = new google.maps.Marker({
			position: MapPos,
			map: map,
			draggable:DragAble,
			animation: google.maps.Animation.DROP,
			title:"Hello World!",
			icon: iconPath
		});
		
		//Content structure of info Window for the Markers
		var contentString = $('<div class="marker-info-win1">'+
		'<div class="marker-inner-win"><span class="info-content">'+
		'<h1 class="marker-heading">'+MapTitle+'</h1>'+
		MapDesc+ 
		'</span>'+
		'</div></div>');	

		
		//Create an infoWindow
		var infowindow = new google.maps.InfoWindow();
		//set the content of infoWindow
		infowindow.setContent(contentString[0]);

		//Find remove button in infoWindow
		
		var saveBtn 	= contentString.find('button.save-marker')[0];

		
		
		if(typeof saveBtn !== 'undefined') //continue only when save button is present
		{
			//add click listner to save marker button
			google.maps.event.addDomListener(saveBtn, "click", function(event) {
				var mReplace = contentString.find('span.info-content'); //html to be replaced after success
				var mName = contentString.find('input.save-name')[0].value; //name input field value
				var mDesc  = contentString.find('textarea.save-desc')[0].value; //description input field value
				
				
				if(mName =='' || mDesc =='')
				{
					alert("Introduza no Nome e a Descrição do local!");
				}else{
					save_marker(marker, mName, mDesc, mReplace); //call save marker function
				}
			});
		}
		
		//add click listner to save marker button		 
		google.maps.event.addListener(marker, 'click', function() {
				infowindow.open(map,marker); // click on marker opens info window 
	    });
		  
		if(InfoOpenDefault) //whether info window should be open by default
		{
		  infowindow.open(map,marker);
		}
	}
	
	
	
	//############### Save Marker Function ##############
	function save_marker(Marker, mName, mAddress, replaceWin)
	{
		//Save new marker using jQuery Ajax
		var mLatLang = Marker.getPosition().toUrlValue(); //get marker position
		var myData = {name : mName, address : mAddress, latlang : mLatLang, }; //post variables
		console.log(replaceWin);		
		$.ajax({
		  type: "POST",
		  url: "map_process.php",
		  data: myData,
		  success:function(data){
				replaceWin.html(data); //replace info window with new html
				Marker.setDraggable(false); //set marker to fixed
				Marker.setIcon('icons/pin_blue.png'); //replace icon
            },
            error:function (xhr, ajaxOptions, thrownError){
                alert(thrownError); //throw any errors
            }
		});
	}

});
</script>

<style type="text/css">
h1.heading{text-align:center;font: "Georgia" , "Times New Roman","Times, serif";font-size: 100px;}
h1.marker-heading{color: #585858;;font: 26px "Trebuchet MS", Arial;border-bottom: 1px dotted #D8D8D8;}
/* width and height of google map */

/* Marker Edit form */

.marker-edit  span {width: 200px;float: left;font-size: 20px;}
.marker-edit  textarea{width:90%;height: 50%;margin:0px;padding-left: 5px;border: 1px solid #DDD;border-radius: 3px;}
.marker-edit input {width: 80%;margin:0px;padding-left: 5px;border: 1px solid #DDD;border-radius: 3px;}
.marker-edit {width: 100%; height:  80%;}


/* Marker Info Window */

.marker-info-win {height:300px; width: 100%;margin-right: -20px;}



div.marker-inner-win{max-width: 500px;padding: 5px;}




button.save-marker{ background-color: #008CBA;
    border: none;
    color: white;
    padding: 15px 32px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;
    margin: 4px 2px;
    cursor: pointer;
}


</style>
</head>


</body>
</html>