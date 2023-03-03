<?php
error_reporting(E_ALL &~E_NOTICE);
?>
<?php
$con = mysqli_connect("localhost","root", "", "single array" );
if( mysqli_connect_error() ){
	echo "DB Error: ". mysqli_connect_error();
	exit;
}
	############# State #################

if( $_GET['action'] == "load_states" ){
	$states = [];
	$res = mysqli_query( $con, "select * from states order by state");
	while( $row = mysqli_fetch_assoc($res) ){
		$states[] = $row;
	}
	echo json_encode($states);
	exit;
}

if($_GET['action'] == "delete_state"){
	$res = mysqli_query($con, "delete from states where id = " .$_GET['state_id']);
	if( mysqli_error($con) ){
		echo json_encode([
			"status"=>"error",
			"error"=>mysqli_error($con)
		]);	
		exit;
	}
	echo json_encode([
		"status"=>"success",
	]);
	exit;
}

if($_GET['action'] == "add_state"){
	$res = mysqli_query($con, "insert into states set
		state = '".mysqli_escape_string($con,$_GET['state'])."'");
	if(mysqli_error($con)){
		echo json_encode([
			"status"=> "error",
			"error"=> "Db error: " . mysqli_error($con)
		]);
		exit;
	}
	$new_id = mysqli_insert_id($con);
	echo json_encode([
		"status"=> "success",
		"new_state_id"=> $new_id
	]);
	exit;
}

if( $_GET['action'] == "edit_state" ){
	$res = mysqli_query( $con, "update states
		set state = '" . mysqli_escape_string($con, $_GET['state'] ) . "'
		where id = " . $_GET['state_id']);
	if( mysqli_error($con) ){
		echo mysqli_error($con);exit;
	}
	echo "success";
	exit;
}

	############# Cities #################

if( $_GET['action'] == "load_cities" ){
	$cities = [];
	$res = mysqli_query( $con, "select a.*, b.state from cities 
		as a left join states as b on ( a.state_id = b.id )
		order by b.state, a.city");
	while( $row = mysqli_fetch_assoc($res) ){
		$cities[] = $row;
	}
	echo json_encode($cities);
	exit;
}

if( $_GET['action'] == "edit_city" ){
	$res = mysqli_query( $con, "update cities set
		state_id = '" . mysqli_escape_string($con, $_GET['state_id'] ) . "', 
		city = '" . mysqli_escape_string($con, $_GET['city'] ) . "'
		where id = " . $_GET['city_id']);
	if( mysqli_error($con) ){
		echo mysqli_error($con);exit;
	}
	$res = mysqli_query($con, "select * from states where id = ". $_GET['state_id']);
	$row = mysqli_fetch_assoc($res);
	echo json_encode([
		"status"=>"success",
		"state"=>$row['state']
	]);
	exit;
}

	############# Areas #################

if( $_GET['action'] == "load_areas" ){
	$areas = [];
	$res = mysqli_query( $con, "select a.*, s.state, c.city from areas as a 
		left join states as s 
		on ( a.state_id = s.id ) 
		left join cities as c 
		on ( a.city_id = c.id ) order by c.city, a.area");
	while( $row = mysqli_fetch_assoc($res) ){
		$areas[] = $row;
	}
	echo json_encode($areas);
	exit;
}
?>
<html>
<head>
	<script src="vue.min.js" ></script>
	<script src="axios.min.js" ></script>
	<link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
</head>
<body>
<div id="app">
	<table class="table table-bordered table-sm"><tr>
		<td>
			<div><input type="text" v-model="new_state" placeholder="Enter State" >
				<input type="button" v-on:click="add_state" value="+" class="btn btn-primary">
			</div>
				<table class="table table-bordered table-sm ">
				<tr><td>State</td>
				<tr v-for="s,i in states" >
					<td>
						<div>
							<div v-if="s['edit']==false">{{ s['state'] }}</div>
							<div v-else >
								<input v-model="states[i]['state']">
							</div>
						</div>
					</td>
					<td>
						<input v-if="s['edit']==false" type="button" v-on:click="edit_state(i)" value="E" >
						<input v-else type="button" v-on:click="save_state(i)" value="save" >
						<input type="button" v-on:click="deletestate(i)" value="X" >
					</td>

				</tr>
			</table>
			<pre>{{states}}</pre>
		</td>
		<td>
		<div>State: <select  v-model="new_city['state']">
			<option value="" hidden>Select</option>
			<option v-for="s in states" v-bind:value="s['state']" >{{ s['state'] }}</option>
		</select></div>
		<div>City: <input type="text" v-model="new_city['city']" placeholder="Enter City" >
			<input type="button" v-on:click="add_city" value="+" class="btn btn-primary">
		</div>
		<table class="table table-bordered table-sm ">
			<tr><td>State</td><td>City</td></tr>
			<tr v-for="c,i in cities" >
			<td><div>
					<div v-if="c['edit']==false">{{ c['state'] }}</div>
					<div v-else >
						<select v-model="cities[i]['state_id']">
							<option v-for="s,si in states" v-bind:value="s['id']" >{{ s['state'] }}</option>
						</select>
					</div>
				</div></td>
			<td>
				<div>
					<div v-if="c['edit']==false">{{ c['city'] }}</div>
					<div v-else >
						<input v-model="cities[i]['city']">
					</div>
				</div>
			</td>
			<td>
				<input v-if="c['edit']==false" type="button" v-on:click="edit_city(i)" value="E" >
				<input v-else type="button" v-on:click="save_city(i)" value="save" >
				<input type="button" v-on:click="deletecity(i)" value="X" >
			</td>
		
			</tr>
		</table>
	</td>

	<td>
		<div>State: <select v-model="new_area['state']" >
			<option value="" hidden>Select</option>
			<option v-for="s in states" v-bind:value="s['state']" >{{ s['state'] }}</option>
		    </select></div>
		<div>City: <select v-model="new_area['city']" >
				<option value="" hidden>Select</option>
				<option v-for="c in cities" v-bind:value="c['city']" >{{ c['city'] }}</option>
		        </select></div>
		<div>Area: <input type="text" v-model="new_area['area']" placeholder="Enter area">
				<input type="button" v-on:click="add_area" value="+" class="btn btn-primary">
			</div>
			<table class="table table-bordered table-sm ">
				<tr><td>State</td><td>City</td><td>Area</td></tr>
				<tr v-for="a,i in areas" >
				<td><div>
						<div v-if="a['edit']==false">{{ a['state'] }}</div>
						<div v-else >
							<select v-model="areas[i]['state_id']">
								<option v-for="s,si in states" v-bind:value="s['id']" >{{ s['state'] }}</option>
							</select>
						</div>
					</div></td>
				<td><div>
					<div v-if="a['edit']==false">{{ a['area'] }}</div>
					<div v-else >
						<select v-model="areas[i]['city_id']">
							<option v-for="s,si in areas" v-bind:value="s['id']" >{{ s['area'] }}</option>
						</select>
					</div>
				</div></td>
			<td>
				<div>
					<div v-if="a['edit']==false">{{ a['area'] }}</div>
					<div v-else >
						<input v-model="areas[i]['area']">
					</div>
				</div>
			</td>
			<td>
				<input v-if="a['edit']==false" type="button" v-on:click="edit_area(i)" value="E" >
				<input v-else type="button" v-on:click="save_area(i)" value="save" >
				<input type="button" v-on:click="deletearea(i)" value="X" >
			</td>
		
			</tr>
		</table>
	</td>
</tr>
</table>
</div>
<script>

/*  variabels or functions becomes reactive */

var app = new Vue({
	el: "#app",
	data: {
		new_state: "" ,
		new_city: {
			"state": "",
			"city": ""
		},
		new_area: {
			"state": "",
			"city": "",
			"area": ""
		},
		edit: false,
		states: [
			"Andhra Pradesh", 
			"Kerala",
			"Maharasthra"
		],
		states_edit: {
		},
		cities: [
			{
				"state": "Andhra Pradesh",
				"city": "Kakinada"	
			}
		],
		areas: [
			{
				"state": "Andhra Pradesh",
				"city": "Kakinada",
				"area"	: "Bhanugudi"
			}
		]
	},
	mounted: function(){
		this.load_states();
		this.load_cities();
		this.load_areas();
	},
	methods: {
		load_states: function(){
			con = new XMLHttpRequest();
			con.open("GET", "?action=load_states",true);
			con.onload = function(){
				var s = JSON.parse(this.responseText);
				//var se = {};
				for(var i=0;i<s.length;i++){
					//se[i] = false;
					s[i]['edit'] = false;
				}
				//app.states_edit = se;
				app.states = s;
			}
			con.send();
		},
		load_cities: function(){
			con = new XMLHttpRequest();
			con.open("GET", "?action=load_cities",true);
			con.onload = function(){
				var s = JSON.parse(this.responseText);
				//var se = {};
				for(var i=0;i<s.length;i++){
					//se[i] = false;
					s[i]['edit'] = false;
				}
				//app.states_edit = se;
				app.cities = s;
			}
			con.send();
		},
		load_areas: function(){
			con = new XMLHttpRequest();
			con.open("GET", "?action=load_areas",true);
			con.onload = function(){
				var s = JSON.parse(this.responseText);
				//var se = {};
				for(var i=0;i<s.length;i++){
					//se[i] = false;
					s[i]['edit'] = false;
				}
				//app.states_edit = se;
				app.areas = s;
			}
			con.send();
		},
		add_state: function(){
			/*vpostdata = "action=add_state&state"+encodeURIComponent(this.new_state);*/
			con = new XMLHttpRequest();
			con.open("GET","?action=add_state&state"+encodeURIComponent(this.new_state),true);
			con.onload = function(){
				var st = JSON.parse( this.responseText );
			 if(st['status'] == 'success'){
			 		
					app.states.push({
						"id": st["new_state_id"],
						"state": app.new_state+"",
						"edit": false,
					});
					//this.states_edit.push(false);
					app.new_state = "";
					//load_states();
			}else{
				alert("There was an error at server: " + st['error']);
			}	
		}
		con.setRequestHeader("content-type", "application/x-www-form-urlencoded");
		con.send();
	},
		edit_state: function(vi){
			this.editing_state_id = vi;
			this.$set( this.states[vi], 'edit', true );
		},
		save_state: function(vi){
			con = new XMLHttpRequest();
			con.open("GET", "?action=edit_state&state_id="+ this.states[vi]['id'] + "&state=" + encodeURIComponent( this.states[vi]['state'] ) );
			con.onload = function(){
				
				if( this.responseText == "success" ){
					app.$set( app.states[ app.editing_state_id ], 'edit', false );
					//this.load_states();
				}else{
					alert("There was an error while updating state: \n" + this.responseText );
				}
			}
			con.send();
			
		},
		deletestate: function(vi){
			vurl = "?action=delete_state&state_id="+ this.states[vi]['id'];
			axios.get(vurl).then(response=>{
				if( response.status == 200 ){
					if( typeof(response.data) == "object" ){
						if( "status" in response.data ){
							if( response.data['status'] == "success" ){
								this.states.splice(vi,1);
							}else{
								alert("Error: " + response.data['error']);
							}
						}else{
							alert("Incorrect response");
						}
					}else{
						alert("Incorrect response");
					}
				}else{
					alert("Http response page not found");
				}
			})
		},
		add_city: function(){
			if(this.new_city['state'] == ''){
				alert("Please Enter the State");
			}
			if(this.new_city['city'] == ''){
				alert("Please Enter the City");
			}
			else{
				this.cities.push({
					"state": this.new_city['state'],
					"city": this.new_city['city']
				});
		
				this.new_city = {
					"state": "", "city": ""
				}
			}
		},
		edit_city: function(vi){
			this.$set( this.cities[vi], 'edit', true);
			this.editing_city_id = vi;
		},
		save_city: function(vi){
			con = new XMLHttpRequest();
			con.open("GET", "?action=edit_city&state_id="+ this.cities[vi]['state_id'] + "&city=" + encodeURIComponent( this.cities[vi]['city'] ) + "&city_id=" + this.cities[vi]['id'] ) ;
			con.onload = function(){
				var st = JSON.parse( this.responseText );
				if( st['status'] == "success" ){
					app.$set( app.cities[ app.editing_city_id ], 'edit', false );
					app.$set( app.cities[ app.editing_city_id ], 'state', st['state'] );

					//this.load_states();
				}else{
					alert("There was an error while updating city: \n" + this.responseText );
				}
			}
			con.send();
		},
		deletecity: function(vi){
			this.cities.splice(vi,1);
		},
		add_area: function(){
			if(this.new_area['state'] == ''){
				alert("Please Enter the State");
			}
			if(this.new_area['city'] == ''){
				alert("Please Enter the City");
			}
			if(this.new_area['area'] == ''){
				alert("Please Enter the Area");
			}else{
				this.areas.push({
					"state": this.new_area['state'],
					"city": this.new_area['city'],
					"area": this.new_area['area']
				});
			
				this.new_area = {
					"state": "", "city": "","area":""
				}
			}
		},
		deletearea: function(vi){
			this.areas.splice(vi,1);
		}
		
	}
});

// app.load_states();
// app.states = ["klsdjfs", "lksdjfs"];

</script>
</body>
</html>