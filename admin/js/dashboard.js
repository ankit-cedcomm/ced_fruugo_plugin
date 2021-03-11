jQuery(document).ready(function(){
	google.charts.load("current", {packages:["corechart"]});
  	google.charts.setOnLoadCallback(drawChart_products);
  	google.charts.setOnLoadCallback(drawChart_order);

});
function drawChart_order() {

	var totalsale = jQuery( '#ced_fruugo_piechart_3d_order' ).attr('data-netsales') ;
	var completed = jQuery( '#ced_fruugo_piechart_3d_order' ).attr('data-completed') ;
	var acknowledge = jQuery( '#ced_fruugo_piechart_3d_order' ).attr('data-acknowledge') ;
	var cancelled = jQuery( '#ced_fruugo_piechart_3d_order' ).attr('data-cancelled') ;


	var data = google.visualization.arrayToDataTable([
	  ['Orders', 'Till date'],
	  ['Completed Order',parseInt(completed)],
	  ['Acknowledge Order', parseInt(acknowledge)],
	  ['Cancelled Order', parseInt(cancelled)]
	]);

	var options = {
	  title: '',
	  pieHole: 0.4,
	  legend: 'none',
	  colors: [ '#FFA500', '#968E91', '#568BC4' ]
	};

	var chart = new google.visualization.PieChart(document.getElementById('ced_fruugo_piechart_3d_order'));
	chart.draw(data, options);
}
function drawChart_products() {

	var uploaded = parseInt(jQuery( '#ced_fruugo_piechart_3d_products' ).attr('data-uploaded')) ;
	var verified = parseInt(jQuery( '#ced_fruugo_piechart_3d_products' ).attr('data-verified')) ;
	var ready = parseInt(jQuery( '#ced_fruugo_piechart_3d_products' ).attr('data-ready')) ;
	var outofstock = parseInt(jQuery( '#ced_fruugo_piechart_3d_products' ).attr('data-outofstock')) ;

	if( uploaded != '' && uploaded != null )
	{
		var data = google.visualization.arrayToDataTable([
		  ['Products', 'Till date'],
		  ['Uploaded Products', uploaded],
		  ['Ready products', ready],
		  ['Out of Stock Products', outofstock],
		  ['Verified Products', verified]
		]);

		var options = {
		  title: '',
		  pieHole: 0.4,
		  legend: 'none',
		  colors: [ '#7A525D' , '#FFA500', '#968E91', '#568BC4' ]
		};

		var chart = new google.visualization.PieChart(document.getElementById('ced_fruugo_piechart_3d_products'));
		chart.draw(data, options);
	}
}

