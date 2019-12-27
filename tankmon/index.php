<?php
// Tank monitor visualiser.

// Configuration variables for TANK dimensions (for volume calculations)
$tank1DiameterMM = 3440;
$tank1EmptyDepthMM = 2580;
$tank1FullDepthMM = 100;

$tank2DiameterMM = 3440;
$tank2EmptyDepthMM = 2580;
$tank2FullDepthMM = 100;

function depth2VolumeL ($tanknum, $depthMM) {
  global $tank1EmptyDepthMM;
  global $tank2EmptyDepthMM;
  global $tank1DiameterMM;
  global $tank2DiameterMM;
  if ($tanknum == 1) {
    $tankvolume = ($tank1EmptyDepthMM + $tank1FullDepthMM - $depthMM) * pow(($tank1DiameterMM/2), 2) * pi();
  } else {
    $tankvolume = ($tank2EmptyDepthMM + $tank2FullDepthMM - $depthMM) * pow(($tank2DiameterMM/2), 2) * pi();
  }
  if ($tankvolume > 0)
    return $tankvolume / 1000000;
  else
    return 0;
}

?><!doctype html>
<html>

<head>
<title>Tank monitoring system</title>
<script src="./package/dist/moment.min.js"></script>
<script src="./package/dist/Chart.min.js"></script>
<script src="./package/dist/utils.js"></script>
<style>
	canvas {
	-moz-user-select: none;
	-webkit-user-select: none;
	-ms-user-select: none;
}
</style>
</head>

<body>

<h1 style="align:center">Tank Monitor</h1>

<div style="width:1000px">
<canvas id="chart1"></canvas>
</div>	
<div style="width:1000px">
<canvas id="chart2"></canvas>
</div>	
<div style="width:1000px">
<canvas id="chart3"></canvas>
</div>	
<br>
<script>
var dateFormat = 'MMMM DD YYYY HHmm';
<?php
$lastData = 0;
if (($handle = popen("tail -200 ./tanklogd1.csv", "r")) !== FALSE) {
while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
if ($lastData[0] == 0) {
?>
date = moment('<?php print date("F d Y Hi", $data[0]);?>', dateFormat);
data1 = [{t:date.valueOf(), y:<?php print depth2VolumeL(1,$data[1]*10);?>}]; 
<?php
} else if (($data[1]>40)&&($data[1]<2000)){
?>
date = moment('<?php print date("F d Y Hi", $data[0]);?>', dateFormat);
data1.push({t:date.valueOf(), y:<?php print depth2VolumeL(1,$data[1]*10);?>}); 
<?php
}
$lastData = $data;
}
}
fclose($handle);

$lastData = 0;
if (($handle = popen("tail -200 ./tanklogd2.csv", "r")) !== FALSE) {
while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
if ($lastData[0] == 0) {
?>
date = moment('<?php print date("F d Y Hi", $data[0]);?>', dateFormat);
data2 = [{t:date.valueOf(), y:<?php print depth2VolumeL(2,$data[1]*10);?>}]; 
<?php
} else if (($data[1]>40)&&($data[1]<2000)) {
?>
date = moment('<?php print date("F d Y Hi", $data[0]);?>', dateFormat);
data2.push({t:date.valueOf(), y:<?php print depth2VolumeL(2,$data[1]*10);?>}); 
<?php
}
$lastData = $data;
}
}
fclose($handle);
?>

var ctx1 = document.getElementById('chart1').getContext('2d');
ctx1.canvas.width = 1000;
ctx1.canvas.height = 300;

var color = Chart.helpers.color;
var cfg1 = {
	type: 'line',
	data: {
		datasets: [{
			label: 'Tank 1',
			backgroundColor: color(window.chartColors.blue).alpha(0.5).rgbString(),
			borderColor: window.chartColors.blue,
			data: data1,
			fill: false,
			borderWidth: 2
		},
		{
			label: 'Tank 2',
			backgroundColor: color(window.chartColors.purple).alpha(0.5).rgbString(),
			borderColor: window.chartColors.purple,
			data: data2,
			fill: false,
			borderWidth: 2
		}]
	},
	options: {
		scales: {
			xAxes: [{
				type: 'time',
				distribution: 'series',
				scaleLabel: {
					display: true,
					labelString: 'Date'
				}
			}],
			yAxes: [{
				scaleLabel: {
					display: true,
					labelString: 'Litres'
				}
			}]
		},
		tooltips: {
			intersect: false,
			mode: 'index',
			callbacks: {
				label: function(tooltipItem, myData) {
					var label = myData.datasets[tooltipItem.datasetIndex].label || '';
					if (label) {
						label += ': ';
					}
					label += parseFloat(tooltipItem.value).toFixed(2);
					return label;
				}
			}
		}
	}
};

var chart1 = new Chart(ctx1, cfg1);
</script>

<script>
var dateFormat = 'MMMM DD YYYY HHmm';
<?php
$lastData = 0;
if (($handle = popen("tail -200 ./tanklogv.csv", "r")) !== FALSE) {
while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
if ($lastData == 0) {
?>
date = moment('<?php print date("F d Y Hi", $data[0]);?>', dateFormat);
data2 = [{t:date.valueOf(), y:<?php print $data[1];?>}]; 
<?php } else { ?>
date = moment('<?php print date("F d Y Hi", $data[0]);?>', dateFormat);
data2.push({t:date.valueOf(), y:<?php print $data[1];?>}); 
<?php
}
$lastData = $data;
}
}
fclose($handle);
?>

var ctx2 = document.getElementById('chart2').getContext('2d');
ctx2.canvas.width = 1000;
ctx2.canvas.height = 300;

var color = Chart.helpers.color;
var cfg2 = { type: 'line',data: { datasets: [{label: 'Battery Charge',backgroundColor: color(window.chartColors.red).alpha(0.5).rgbString(),
borderColor: window.chartColors.red,data: data2,fill: false,borderWidth: 2}] },options: { scales: { xAxes: [{ type: 'time', distribution: 'series',
scaleLabel: { display: true, labelString: 'Date'} }], yAxes: [{ scaleLabel: { display: true, labelString: 'Volts' } }] }, tooltips: {
intersect: false, mode: 'index', callbacks: { label: function(tooltipItem, myData) { var label = myData.datasets[tooltipItem.datasetIndex].label || '';
if (label) { label += ': '; } label += parseFloat(tooltipItem.value).toFixed(2); return label; } } } } };

var chart2 = new Chart(ctx2, cfg2);
</script>

<script>
var dateFormat = 'MMMM DD YYYY HHmm';
<?php
$lastData = 0;
if (($handle = popen("tail -200 ./tanklogt.csv", "r")) !== FALSE) {
while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
if ($lastData == 0) {
?>
date = moment('<?php print date("F d Y Hi", $data[0]);?>', dateFormat);
data3 = [{t:date.valueOf(), y:<?php print $data[1];?>}]; 
<?php } else { ?>
date = moment('<?php print date("F d Y Hi", $data[0]);?>', dateFormat);
data3.push({t:date.valueOf(), y:<?php print $data[1];?>}); 
<?php
}
$lastData = $data;
}
}
fclose($handle);
?>

<?php
$lastData = 0;
if (($handle = popen("tail -200 ./tanklogh.csv", "r")) !== FALSE) {
while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
if ($lastData == 0) {
?>
date = moment('<?php print date("F d Y Hi", $data[0]);?>', dateFormat);
data4 = [{t:date.valueOf(), y:<?php print $data[1];?>}]; 
<?php } else { ?>
date = moment('<?php print date("F d Y Hi", $data[0]);?>', dateFormat);
data4.push({t:date.valueOf(), y:<?php print $data[1];?>}); 
<?php
}
$lastData = $data;
}
}
fclose($handle);
?>

var ctx3 = document.getElementById('chart3').getContext('2d');
ctx3.canvas.width = 1000;
ctx3.canvas.height = 300;

var color = Chart.helpers.color;
var cfg3 = { type: 'line',data: { 
datasets: [
{label: 'Temperature',backgroundColor: color(window.chartColors.green).alpha(0.5).rgbString(),
borderColor: window.chartColors.green,data: data3, fill: false,borderWidth: 2},
{label: 'Humidity', backgroundColor: color(window.chartColors.orange).alpha(0.5).rgbString(), 
borderColor: window.chartColors.orange, data: data4, fill:false,borderWidth: 2}
]},options: { scales: { xAxes: [{ type: 'time', distribution: 'series',
scaleLabel: { display: true, labelString: 'Date'} }], yAxes: [{ scaleLabel: { display: true, labelString: 'Temperature' } }] }, tooltips: {
intersect: false, mode: 'index', callbacks: { label: function(tooltipItem, myData) { var label = myData.datasets[tooltipItem.datasetIndex].label || '';
if (label) { label += ': '; } label += parseFloat(tooltipItem.value).toFixed(2); return label; } } } } };

var chart3 = new Chart(ctx3, cfg3);
</script>

</body>

</html>

