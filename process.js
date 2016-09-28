
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
$(document).ready(function () {

   var pixels_per_site = 15;
   var margins = 35;
   var height = pixels_per_site * sites.length + margins;

    $('#container').height(height);
    console.log(sites.length);
    $('#container').highcharts({
        chart: {
            type: 'heatmap',
            marginTop: 30,
            marginBottom: 35,
            plotBorderWidth: 1,
        },


        title: {
            text: 'Station Report'
        },

        xAxis: {
            gridLineWidth:0,
            type:'datetime'
        },

        yAxis: {
            categories: sites,
            title: null
        },

    colorAxis: {
      min: 0,
      max: 24,
      /*
      stops: [
      
        [22, '#ffff00'],
        [23, '#adff2f'],
        [24, '#009900']
        
        [3, '#009900'],
        [22, '#ffff00'],
        [23, '#adff2f'],
        [24, '#009900']
      ],
      */
      stops: [
        [0, '#8b0000'],
        [.7, '#ff0000'],
        [.8, '#ffff00'],
        [.9, '#adff2f'],
        [1, '#009900']
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
        colsize: 30000000,
        turboThreshold: 0
      }
    },
    tooltip: {
      formatter: function () {
        return '<b>' + this.series.yAxis.categories[this.point.y] + ' ' + Highcharts.dateFormat('%Y-%m-%d %H:%M', this.point.x) + ' </b> ' + this.point.value;
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
    $('form').submit(function(event) {

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