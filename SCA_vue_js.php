<?php
error_reporting( E_ALL & ~E_NOTICE );
$con = mysqli_connect("localhost","root", "", "single array" );
if( mysqli_connect_error() ){
	echo "DB Error: ". mysqli_connect_error();
	exit;
}
if( $_GET['action'] == "load_states" ){
	$states = [];
	$query= "select * from states order by state";
	$res = mysqli_query($con, $query );
	while( $row = mysqli_fetch_assoc($res) ){
		$states[] = $row;
	}
	echo json_encode($states);
	exit;
}
if( $_GET['action'] == "load_cities" ){
	$cities = [];
	$query= "select a.*, b.state from cities 
		as a left join states as b on ( a.state_id = b.id )
		order by b.state, a.city";
		$res = mysqli_query($con, $query );
	while( $row = mysqli_fetch_assoc($res) ){
		$cities[] = $row;
	}
	echo json_encode($cities);
	exit;
}
if( $_GET['action'] == "load_areas" ){
	$areas = [];
	$query = "select a.*, s.state, c.city from areas as a 
		left join states as s 
		on ( a.state_id = s.id ) 
		left join cities as c 
		on ( a.city_id = c.id )  order by a.area";
	$res = mysqli_query($con, $query );
	while( $row = mysqli_fetch_assoc($res) ){
		$areas[] = $row;
	}
	echo json_encode($areas);
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
if( $_GET['action'] == "edit_city" ){
	$res = mysqli_query( $con, "update cities 
		set
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
if( $_GET['action'] == "edit_area" ){
	$res = mysqli_query( $con, "update areas 
		set
		state_id = '" . mysqli_escape_string($con, $_GET['state_id'] ) . "', 
		city_id = '" . mysqli_escape_string($con, $_GET['city_id'] ) . "',
		area = '" . mysqli_escape_string($con, $_GET['area'] ) . "'
		where id = " . $_GET['area_id']);
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

?><html>
<head>
	<script src="vue.min.js" ></script>
	<link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
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
			<option v-for="s in states" v-bind:value="s" >{{ s }}</option>
		</select>
		</div>
		<div>
			City: <input type="text" v-model="new_city['city']" placeholder="Enter City" >
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
			<option v-for="s in states" v-bind:value="s" >{{ s }}</option>
		</select>
		City: <select v-model="new_area['city']" >
				<option value="" hidden>Select</option>
				<option v-for="c in cities" v-bind:value="c['city']" >{{ c['city'] }}</option>
		</select></div>
			<div>
				Area: <input type="text" v-model="new_area['area']" placeholder="Enter area">
				<input type="button" v-on:click="add_area" value="+" class="btn btn-primary">
			</div>
		<table class="table table-bordered table-sm ">
			<tr>
				<td>State</td>
				<td>City</td>
				<td>Area</td>
			</tr>
			<tr v-for="c,i in areas" >
				<td>{{ c['state'] }}</td>
				<td>{{ c['city'] }}</td>
				<td>{{ c['area'] }}</td>
				<td><input type="button" v-on:click="deletearea(i)" value="X" ></td>
			</tr>
		</table>
	</td>
</tr>
</table>
</div>
<script>

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
				for(var i=0;i<s.length;i++){
					s[i]['edit'] = false;
				}
				app.states = s;
			}
			con.send();
		},
		load_cities: function(){
			con = new XMLHttpRequest();
			con.open("GET", "?action=load_cities",true);
			con.onload = function(){
				var c = JSON.parse(this.responseText);
				for(var i=0;i<c.length;i++){
					c[i]['edit'] = false;
				}
				app.cities = c;
			}
			con.send();
		},
		load_areas: function(){
			con = new XMLHttpRequest();
			con.open("GET", "?action=load_areas",true);
			con.onload = function(){
				var a = JSON.parse(this.responseText);
				for(var i=0;i<a.length;i++){
					a[i]['edit'] = false;
				}
				app.areas = a;
			}
			con.send();
		},
		add_state: function(){
			if(this.new_state ==''){
				alert("Please Enter the State");
			}else{
				this.states.push( this.new_state+'' );
				this.states_edit.push(false);
				this.new_state = "";
			}
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
			this.states.splice(vi,1);
			this.states_edit.splice(vi,1);
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
		edit_area: function(vi){
			this.$set( this.areas[vi], 'edit', true);
			this.editing_area_id = vi;
			},
		save_area: function(vi){
			con = new XMLHttpRequest();
			con.open("GET", "?action=edit_city&state_id="+ this.areas[vi]['state_id'] +"&city_id="+ this.areas[vi]['city_id'] + "&area=" + encodeURIComponent( this.areas[vi]['area'] ) + "&area_id=" + this.areas[vi]['id'] ) ;
			con.onload = function(){
				var st = JSON.parse( this.responseText );
				if( st['status'] == "success" ){
					app.$set( app.areas[ app.editing_area_id ], 'edit', false );
					app.$set( app.areas[ app.editing_area_id ], 'state', st['state'] );
				}else{
					alert("There was an error while updating area: \n" + this.responseText );
				}
			}
			con.send();
		},
		deletearea: function(vi){
			this.areas.splice(vi,1);
		}
		
	}
});
</script>
</body>
</html>