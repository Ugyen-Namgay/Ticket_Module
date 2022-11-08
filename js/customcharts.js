function horizontal_bar_chart(id,data,label=false) {
    //echo get("tempvisitors","country,count(ip) as num","NOT country='' GROUP BY country ORDER BY count(ip) DESC LIMIT 5");
    //data=[["Bhutan","2"],["Australia","1"]]
    parseddata=JSON.parse(data);
    l=[];
    d=[];
    parseddata.forEach(function(items,index){
        l.push(items[0]);
        d.push(parseInt(items[1]));
    });
    console.log(l);
    console.log(d);
    var plotdata = {
      labels: l,
      datasets: [{
          label: label,
          data: d,
          backgroundColor: [
                  '#8169f2',
                  '#6a4df5',
                  '#4f2def',
                  '#2b0bc5',
                  '#180183',
          ],
          borderColor: [
                  '#8169f2',
                  '#6a4df5',
                  '#4f2def',
                  '#2b0bc5',
                  '#180183',
          ],
          borderWidth: 2,
          fill: false
      }],
  };
  var plotoptions = {
      scales: {
          xAxes: [{
              position: 'bottom',
              display: false,
              gridLines: {
                      display: false,
                      drawBorder: true,
              },
              ticks: {
                      display: false ,//this will remove only the label
                      beginAtZero: true
              }
          }],
          yAxes: [{
              display: true,
              gridLines: {
                  drawBorder: true,
                  display: false,
              },
              ticks: {
                  beginAtZero: true
              },
          }]
      },
      legend: {
          display: false
      },
      tooltips: {
          show: false,
          backgroundColor: 'rgba(31, 59, 179, 1)',
      },
      plugins: {
      datalabels: {
              display: true,
              align: 'start',
              color: 'white',
          }
      }				

  };
  if ($("#"+id).length) {
      var barChartCanvas = $("#"+id).get(0).getContext("2d");
      // This will get the first returned node in the jQuery collection.
      var barChart = new Chart(barChartCanvas, {
          type: 'horizontalBar',
          data: plotdata,
          options: plotoptions,

      });
  }
}
