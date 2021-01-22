@extends('admin.layouts.app')
@section('title',$title)
@section('user_name',$user->name)
@section('role',$user->role)
@section('content')

        
        <div class="content-wrapper">
          <div class="row">
            <div class="col-md-6 col-lg-3 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex align-items-center justify-content-md-center">
                    <i class="mdi mdi-account icon-lg text-success"></i>
                    <div class="ml-3">
                      <p class="mb-0">Businesses</p>
                      <h6>{{$businesses}}</h6>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6 col-lg-3 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex align-items-center justify-content-md-center">
                    <i class="mdi mdi-account icon-lg text-success"></i>
                    <div class="ml-3">
                      <p class="mb-0">Users</p>
                      <h6>{{$users}}</h6>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6 col-lg-3 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex align-items-center justify-content-md-center">
                    <i class="mdi mdi-account icon-lg text-success"></i>
                    <div class="ml-3">
                      <p class="mb-0">Advertiser</p>
                      <h6>{{$advertiser}}</h6>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-lg-12 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  {{-- <h4 class="card-title">Line chart</h4> --}}
                  <canvas id="userChart" style="height:250px"></canvas>
                </div>
              </div>
            </div>
          </div>
        </div>
       
@endsection

@section('footerScript')
@parent
  <!-- Plugin js for this page-->
  <script src="{{asset('admin/node_modules/chart.js/dist/Chart.min.js')}}"></script>
  <script>
    $(document).ready(function(){
      var data = {
      labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun","Jul","Aug","Sep","Oct","Nov","Dec"],
      datasets: <?php echo json_encode($usersGraph);?>
    };
     var options = {
      scales: {
        yAxes: [{
          ticks: {
            beginAtZero: true
          },
          gridLines: {
            drawBorder: true,
           // color:"rgba(0,0,0,0)",
           // zeroLineColor:"rgb(0, 0, 0)"
            }
        }],
        xAxes: [{
            gridLines: {
             // drawBorder: true,
            //  color: "rgba(0, 0, 0,0)",
            //  zeroLineColor:"rgb(0, 0, 0)"
            }
        }]
      },
      legend: {
        display: true,
       labels: {
          usePointStyle: true, // show legend as point instead of box
          fontSize: 10 // legend point size is based on fontsize
        }
      },
      elements: {
        point: {
          radius: 3
        }
      },
    pointDot: true,
                  pointDotRadius : 6,
                  datasetStrokeWidth : 6,
                  bezierCurve : false,
    };
     if ($("#userChart").length) {
      var lineChartCanvas = $("#userChart").get(0).getContext("2d");
      var lineChart = new Chart(lineChartCanvas, {
        type: 'line',
        data: data,
        options: options
      });
    }
    });

    </script>
    
  <!-- End plugin js for this page-->
  @endsection