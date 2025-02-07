function drawChart() {
    // call ajax function to get sports data
    var jsonData = $.ajax({
        url: "php/Archivos_Grafica/Consulta_Status.php",
        dataType: "json",
        async: false
    }).responseText;
    //The DataTable object is used to hold the data passed into a visualization.
    var data = new google.visualization.DataTable(jsonData);
    // To render the pie chart.
    var chart = new google.visualization.PieChart(document.getElementById('chart_container'));
    chart.draw(data, {/*Aqui pon estilos JCCM*/});
}
// load the visualization api
google.charts.load('current', {'packages':['corechart']});
// Set a callback to run when the Google Visualization API is loaded.
google.charts.setOnLoadCallback(drawChart);
