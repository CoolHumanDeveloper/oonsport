function getSearchSubGroup(groupid,sub_groupid,level){
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
    //alert(ajaxRequest.readyState);
      if(ajaxRequest.readyState == 4){
          if (level==0){

            ajaxDisplayLevel_2 = document.getElementById('search_sport_sub_group_div_1');
            ajaxDisplayLevel_2.innerHTML = '';

            ajaxDisplayLevel_3 = document.getElementById('search_sport_sub_group_div_2');
            ajaxDisplayLevel_3.innerHTML = '';
         }
			 
         if (level==1){

            ajaxDisplayLevel_3 = document.getElementById('search_sport_sub_group_div_2');
            ajaxDisplayLevel_3.innerHTML = '';
         }

		  var ajaxDisplay;
		  
         ajaxDisplay = document.getElementById('search_sport_sub_group_div_' + level);
         ajaxDisplay.innerHTML = ajaxRequest.responseText;
			
      }
   }
   
    var more_selected_values = "";
    var moreSelected = $("[name='search_more_sport[]']");
    console.log(moreSelected);
    for (var i = 0; i < moreSelected.length; ++i)  { 
        more_selected_values = more_selected_values + "&search_more_sport[]=" +  moreSelected[i].value; 
        console.log(moreSelected[i].value);
    }
   
   var queryString = "?search_groupid=" + groupid  + "&search_sub_groupid=" + sub_groupid + "&search_level=" + level + more_selected_values;
   console.log(queryString);
   ajaxRequest.open("GET", "//" + location.host + "/oonsrc/js/ajax_source/search_sport_subgroup.php" + queryString, true);
   ajaxRequest.send(null); 
}