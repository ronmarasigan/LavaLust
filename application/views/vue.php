<!DOCTYPE html>
<html>
<head>
	<title>LAVALust and Vue</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	<script src="<?php echo BASE_URL; ?>assets/js/vue.js"></script>
</head>
<body>
	<div id="root">
		<h1>{{ message }}</h1>
		<input type="text" v-model="message">
		<ul>
			<li v-for="car in cars">
				brand: {{ car.brand }} model: {{ car.model }}
			</li>
		</ul>
		<div v-for="car in cars">
			<h1>{{ car.brand }}</h1>
			<h2>{{ car.model }}</h2>
			 <img v-bind:src="car.image" width="200" />
			 {{ car.similar }}
			 
		</div>
		<h1 v-html="intro">Hello Ron</h1>
		<h1 v-if="s1==s2">Hello Ron</h1>


		<h1 v-bind:title="title">{{ message }}</h1>
		<img v-bind:src="url" v-bind:title="title" width="200">

	</div>
</body>
<script src="<?php echo BASE_URL; ?>assets/js/app.js"></script>	
</html>