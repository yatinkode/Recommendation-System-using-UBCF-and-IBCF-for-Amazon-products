<html>
<head>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<link rel = "stylesheet" type = "text/css" href = "style.css" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
</head>
<body>

<div class="container">
<div class="jumbotron" style="padding:5px; margin-bottom:0px">
  <h2 style="margin-left:5px;">Recommendation System using R</h2	> </div>
  
  
<?php

$conn = new mysqli('localhost', 'root', '', 'product') 
or die ('Cannot connect to db');

    $result = $conn->query("select username from users_table");
	
	if(isset($_POST['uname'])){
		$value_op=$_POST['uname'];
	}
	?>
	
</br>
	<form method="post" >
	<div class="divsrch">
   Select username:   <select name="uname" id="select_id">


<?php


					
    while ($row = $result->fetch_assoc()) {

                  unset($uname);
	
                  $uname = $row['username']; 

				  
				
				
					if($value_op==$uname){
					    echo '<option value="'.$uname.'" selected>'.$uname.'</option>';
					}
				  
					else{
						echo '<option value="'.$uname.'">'.$uname.'</option>';
					}

                 
}
?>

    </select>
	
	<button type="submit" class="btn btn-secondary btn-sm custom-bt">Submit</button>
	</div>
	</form>
	</br>

	<!--<p id="output"></p> -->
	
	
	<?php 
	if(isset($_POST['uname'])){
		
exec('C:\\"Program Files"\\R\\R-3.5.0\\bin\\Rscript.exe C:\xampp\htdocs\recommendamazon\amazonproductrecommend.R '.$value_op);



$filename = 'C:\xampp\htdocs\recommendamazon\output.csv';

// The nested array to hold all the arrays
$the_big_array = []; 

// Open the file for reading
if (($h = fopen("{$filename}", "r")) !== FALSE) 
{
  // Each line in the file is converted into an individual array that we call $data
  // The items of the array are comma separated
  while (($data = fgetcsv($h, 1000, ",")) !== FALSE) 
  {
    // Each individual array is being pushed into the nested array
    $the_big_array[] = $data;		
  }

  // Close the file
  fclose($h);
}
 

echo "\n\n";

//echo "Our Recommendations for ".$value_op;

$size = array_sum(array_map("count", $the_big_array))-3;
	
	?>
	<table class="table table-bordered">
	
		<thead class="thead-dark">
			<tr>
			<th>UBCF recommendations for <?php echo $value_op;?></th>
			</tr>
		</thead>
	<tbody>
	<?php
	
	
	for($i = 1; $i < 4; $i++)
{
    foreach($the_big_array[$i] as $key => $value) {
		
        echo "<tr><td>".$value."</td></tr>" ;
    }
}
?>
</tbody>
</table>
</br></br>

<table class="table table-bordered">
<thead class="thead-dark">
    <tr>
      <th>IBCF recommendations for <?php echo $value_op;?></th></tr></thead>
	<tbody>

<?php
if ($size<4){
	
	echo '<tr><td style="color:red;">No IBCF recommendations available for '.$value_op.' due to less data</tr></td></tbody></table>';
}
		else{
		
				for($j = 4; $j <7; $j++){
				
					foreach($the_big_array[$j] as $key => $value) {
		
						echo "<tr><td>".$value."</td></tr>" ;
				}
			}
		}
	
	
}
	?>
	</tbody>
	</table>
	
	
	
<script>
/*function val() {
    d = document.getElementById("select_id").value;
	document.getElementById('output').innerHTML=d;
}
*/
$(document).ready(function() {
    $('select').select2({
    width: '12%' // need to override the changed default
});
});
</script>
</div>	

</body>
</html>