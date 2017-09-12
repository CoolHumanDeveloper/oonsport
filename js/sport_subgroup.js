function getSubGroup(groupid,sub_groupid,level){
   var ajaxRequest;  // The variable that makes Ajax possible!
   try{
   
      // Opera 8.0+, Firefox, Safari
      ajaxRequest = new XMLHttpRequest();
   }catch (e){
      
      // Internet Explorer Browsers
      try{
         ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
      }catch (e) {
         
         try{
            ajaxRequest = new ActiveXObject("Microsoft.XMLHTTPS");
         }catch (e){
         
            // Something went wrong
            alert("Your browser broke!");
            return false;
         }
      }
   }
   
   ajaxRequest.onreadystatechange = function(){
   
      if(ajaxRequest.readyState == 4){
		  
          if (level==0) {
              ajaxDisplayLevel_2 = document.getElementById('sport_sub_group_div_1');
              ajaxDisplayLevel_2.innerHTML = '';
              ajaxDisplayLevel_3 = document.getElementById('sport_sub_group_div_2');
              ajaxDisplayLevel_3.innerHTML = '';
         }
			 
          if (level==1) {
              ajaxDisplayLevel_3 = document.getElementById('sport_sub_group_div_2');
              ajaxDisplayLevel_3.innerHTML = '';
         }

		  var ajaxDisplay;
		  
         ajaxDisplay = document.getElementById('sport_sub_group_div_' + level);
         ajaxDisplay.innerHTML = ajaxRequest.responseText;
			
      }
   }
   
   var queryString = "?groupid=" + groupid  + "&sub_groupid=" + sub_groupid + "&level=" + level ;
   ajaxRequest.open("GET", "//" + location.host + "/js/ajax_source/sport_subgroup.php" + queryString, true);
   ajaxRequest.send(null); 
}