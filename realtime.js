/*
 * plotOptions -> heatmap -> colsize   controls wierd column formatting issue
 * it will have to be dynamically generated somehow
 * */
  $( function() {
    $( "#startdatepicker" ).datepicker({
      showButtonPanel: true
    });
  } );

  $( function() {
    $( "#enddatepicker" ).datepicker({
      showButtonPanel:true
    });
  } );

  $( function() {
    $( "#radio-1" ).checkboxradio();
  } );
jQuery(document).ready(function(){
          jQuery('.toggle_container').hide();
       jQuery('p.trigger').click(function(){
         jQuery(this).toggleClass('active').next().toggle('slow');
    });
});       
function getUrlVars() {
var vars = {};
var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
vars[key] = value;
});
return vars;
}
function requestData() {
    $.ajax({
        url: 'getData.php',
        success: function(point) {
            var series = chart.series[0],
                shift = series.data.length > 20; // shift if the series is 
                                                 // longer than 20

            // add the point
            chart.series[0].addPoint(point, true, shift);
            
            // call it again after one second
            setTimeout(requestData, 1000);    
        },
        cache: false
    });
}

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

$(document).ready(function () {

   var pixels_per_site = 15;
   var margins = 35;
   var height = pixels_per_site * sites.length + margins;
   var phpMax = getUrlVars()["radio-1"]
   console.log(phpMax);
   switch (phpMax){
      case "interval5":
        myMax = 1; //Max possible reports
        break;
      case "interval30":
        myMax = 6; 
        break;
      case "intervalhour":
        myMax = 12; 
        break;
      case "intervalday":
        myMax = 288; 
        break;
      case "intervalmonth":
        myMax = 8640;
        break;
    }
    console.log(myMax);
    $('#container').height(height);
    console.log(sites.length);

    $('#container').highcharts({
        chart: {
            renderTo: 'container',
            type: 'heatmap',
            marginTop: 30,
            marginBottom: 35,
            plotBorderWidth: 1,
            events: {
                load: requestData
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
      max : myMax,
      
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
        colsize: 500000 * myMax, //dynamically generate square size based on interval selected.
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
            data: data,
            dataLabels: {
                enabled: false,
                //color: '#000000'
            }
        }]

    });
});
$(document).ready(function() {
    // process the form
    $('execute').submit(function(event) {

        // get the form data
        // there are many ways to get this data using jQuery (you can use the class or id also)
        var formData = {
            'sdate' : $('input[name=startdatepicker]').val(),
            'edate' : $('input[name=enddatepicker]').val(),
            'interval' : $('input[name=radio-1').val() 
        };
         console.log("check");
         console.log(sdate);
         console.log(edate);
         console.log(interval);
        // process the form
        $.ajax({
            type        : 'POST', // define the type of HTTP verb we want to use (POST for our form)
            url         : 'stationstatus.php', // the url where we want to POST
	          async       : true,
            data        : formData, // our data object
            dataType    : 'json', // what type of data do we expect back from the server
            encode          : true
        })
            // using the done promise callback
            .done(function(data) {

                // log data to the console so we can see
                console.log(data); 

                // here we will handle errors and validation messages
            });

        // stop the form from submitting the normal way and refreshing the page
        event.preventDefault();
    });

});
