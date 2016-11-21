var chart; // global

/**
 * This plugin extends Highcharts in two ways:
 * - Use HTML5 canvas instead of SVG for rendering of the heatmap squares. Canvas
 *   outperforms SVG when it comes to thousands of single shapes.
 * - Add a K-D-tree to find the nearest point on mouse move. Since we no longer have SVG shapes
 *   to capture mouseovers, we need another way of detecting hover points for the tooltip.
 */
(function (H) {
    var Series = H.Series,
        each = H.each;
    /**
     * Create a hidden canvas to draw the graph on. The contents is later copied over
     * to an SVG image element.
     */
    Series.prototype.getContext = function () {
        if (!this.canvas) {
            this.canvas = document.createElement('canvas');
            this.canvas.setAttribute('width', this.chart.chartWidth);
            this.canvas.setAttribute('height', this.chart.chartHeight);
            this.image = this.chart.renderer.image('', 0, 0, this.chart.chartWidth, this.chart.chartHeight).add(this.group);
            this.ctx = this.canvas.getContext('2d');
        }
        return this.ctx;
    };
    /**
     * Draw the canvas image inside an SVG image
     */
    Series.prototype.canvasToSVG = function () {
        this.image.attr({ href: this.canvas.toDataURL('image/png') });
    };
    /**
     * Wrap the drawPoints method to draw the points in canvas instead of the slower SVG,
     * that requires one shape each point.
     */
    H.wrap(H.seriesTypes.heatmap.prototype, 'drawPoints', function () {
        var ctx = this.getContext();
        if (ctx) {
            // draw the columns
            each(this.points, function (point) {
                var plotY = point.plotY,
                    shapeArgs;
                if (plotY !== undefined && !isNaN(plotY) && point.y !== null) {
                    shapeArgs = point.shapeArgs;
                    ctx.fillStyle = point.color;
                    ctx.fillRect(shapeArgs.x, shapeArgs.y, shapeArgs.width, shapeArgs.height);
                }
            });
            this.canvasToSVG();
        } else {
            this.chart.showLoading('Your browser doesn\'t support HTML5 canvas, <br>please use a modern browser');
            // Uncomment this to provide low-level (slow) support in oldIE. It will cause script errors on
            // charts with more than a few thousand points.
            // arguments[0].call(this);
        }
    });
    H.seriesTypes.heatmap.prototype.directTouch = false; // Use k-d-tree
}(Highcharts));


function requestData(){
       $.ajax({
        url: './getData.php',
        type: 'GET',
        //dataType: 'json',
        dataType: 'text', // trying text right now
        data: "{}",
        contentType: "application/json; charset=utf-8",
        async: true, 
        success: function (response){
          alert('Successfully called');

          var series ={data: []};
          chart.series.data.push(response, data); 
          chart.options.series.push(series);

          //chart.addSeries(response, true, true);
          console.log(data);
          setTimeout(requestData, 300000); //request new data every 5 minutes

        },
        error: function (textStatus, errorThrown) {
                alert('there was an error in the ajax call');
                Success = false;//doesnt goes here
            },
        cache: false
      });
     }
     


$(document).ready(function () {

$('#container').height(height);
    console.log(sites.length);
   var pixels_per_site = 15;
   var margins = 35;
   var height = pixels_per_site * sites.length + margins;
 chart = new Highcharts.Chart({
        chart: {
            renderTo: 'container',
            type: 'heatmap',
            marginTop: 30,
            marginBottom: 35,
            plotBorderWidth: 1,
            events: {
                load: function() {
                  chart = this; // `this` is the reference to the chart
                  requestData();
}
              }
   
        },

        title: {
            text: 'Station Report'
        },

        xAxis: {
            //min: 0,
            //max: 24,
            //tickWidth: 1,
            //step: 2,
            gridLineWidth:0,
            type:'datetime',
	          allowDecimals: false,
            type: 'datetime'
       
     
            
        },

        yAxis: {
            categories: sites,
            title: null,
            
        },

    colorAxis: {
      min: 0,
      //max: 24,
      //max : myMax,
      max: 1, //it is one for interval of 5 minutes
      
      //GET INTERVAL FROM php page and redraw with proper scale
      //max = " <?php echo $max ?> "
     /*
      stops: [
        [0, '#8b0000'],
        [.7, '#ff0000'],
        [.8, '#ffff00'],
        [.9, '#adff2f'],
        [1, '#009900']
      ],*/
      stops: [
      [0, '#78AB46'], // pea green
      [.001, '#ffff00'], //yellow abrupt change
      [.1, '#ffa500'], //light orange
      [1, '#ff4c4c'] //light red
      ],

      min: 0,
      maxColor: '#009900',
      minColor: '#ff0000'
    },

        legend: {
            align: 'right',
            layout: 'vertical',
            margin: 0,
            verticalAlign: 'top',
            y: 25,
            symbolHeight: 280
        },
    plotOptions: {
      heatmap: {
        //colsize: 500000 * myMax, //dynamically generate square size based on interval selected.
        colsize: 500000 * 1, //1 for myMax = 1 when 5 minute intervals are chosen
        turboThreshold: 0
      }
    },
    tooltip: {
      formatter: function () {
        return '<b>' + this.series.yAxis.categories[this.point.y] + '</b>' + '<br>' + Highcharts.dateFormat('Date: %m-%d-%Y <br> Time: %H:%M', this.point.x) + ' ' + '<br>There were: ' + this.point.value +' misses';
      }
    },

        series: [{
            name: 'Site Status',
            borderWidth: 0,
            data: [],   //received from ajax call as "response"
            dataLabels: {
                enabled: false,
                //color: '#000000'
            }
        }]

    });
});


