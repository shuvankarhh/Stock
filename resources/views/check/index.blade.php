@extends('layouts.app')

@push('includes')
<!-- Bootstrap core CSS     -->
{{--<link href="assets/css/bootstrap.min.css" rel="stylesheet" />--}}

<link href="assets/css/jquery-ui.css" rel="stylesheet" />
<!-- Animation library for notifications   -->
<link href="assets/css/animate.min.css" rel="stylesheet"/>

<!--  Light Bootstrap Table core CSS    -->
<link href="assets/css/light-bootstrap-dashboard.css" rel="stylesheet"/>

<link href="assets/css/style.css" rel="stylesheet" />

<!--     Fonts and icons     -->
{{--<link href="assets/css/font-awesome.min.css" rel="stylesheet" />--}}
{{--<link href="assets/css/font.css" rel="stylesheet" type="text/css" />--}}

<link rel="stylesheet" type="text/css" href="assets/css/main.css" />

<!--   Core JS Files   -->
<script src="assets/js/jquery-1.10.2.js" type="text/javascript"></script>
{{--<script src="assets/js/bootstrap.min.js" type="text/javascript"></script>--}}

<!--  Notifications Plugin    -->
<script src="assets/js/bootstrap-notify.js"></script>

<!-- Light Bootstrap Table Core javascript and methods for Demo purpose -->
<script src="assets/js/light-bootstrap-dashboard.js"></script>

<!-- Java Script -->
{{--<script src="assets/js/js/bootstrap.js"></script>--}}
{{--<script src="assets/js/validator.min.js"></script>--}}
<script src="assets/js/jquery.js"></script>

<script src="assets/js/jquery-uiii2.js"></script>
<script src="assets/js/colResizable-1.6.js"></script>
@endpush

<!-- <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css"> -->
<!-- <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script> -->
<!-- <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script> -->

@push('javascript')
$(function(){
});

var json;

function resetPage() {
  window.location.reload(true);
};
function savepdf() {
  // hope the server sets Content-Disposition: attachment!
  window.location = "/check/show?last_check="+json.last_check_number+"&url="+encodeURIComponent(json.outputPDF);
};
function getResponse(mode){
  var file_data = $('#data_file').prop('files')[0];
  var form_data = new FormData();
  form_data.append('data_file', file_data);
  form_data.append('mode', mode);
  form_data.append('starting_num', $('#starting_num').val());

  if ($('#ManualMark').is(":checked"))
  {
    form_data.append('ManualMark', 1);
  }else{
    form_data.append('ManualMark', 0);
  }
  //alert(form_data);
  $.ajax({
    url: '{{ route('check.store') }}', // point to server-side PHP script
    dataType: 'text',  // what to expect back from the PHP script, if anything
    cache: false,
    contentType: false,
    processData: false,
    data: form_data,
    type: 'post',
    success: function(php_script_response){
      json=JSON.parse(php_script_response);
      if(json.success=="0"){
        $('#starting_num').val('');
        $('#msg1').text(json.error);
      }else{
        var spaces="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp";
        if(json.totalChecks>0){
          $('#checkTitle').html("Check build: "+json.CheckBuild+spaces+json.totalChecks+" Checks"+spaces+"$"+json.totalAmount+"");
        }

        if(json.preview.length>0){
          $('#first_img').attr("src",json.preview[0]);
          //style="border: 1px solid #b3b3b3"
          $('#first_img').attr("style","border: 1px solid #b3b3b3");
        }
        if(json.preview.length>1){
          $('#last_img').attr("src",json.preview[1]);
          $('#last_img').attr("style","border: 1px solid #b3b3b3");
        }
      }
      //alert(php_script_response); // display response from the PHP script, if any

    }
  });
}
function review() {
  getResponse("1");
  $('#tableBody').html('');
  for (i = 0; i < json.csv.length; i++) {
    var myrow='';
    for(j=0;j<json.csv[i].length;j++){

      if(i==0){
        myrow=myrow+"<th>"+json.csv[i][j]+"</th>";

      }else{
        myrow=myrow+"<td>"+json.csv[i][j]+"</td>";
      }
    }
    if(i==0){
      $('#tableHead').html(myrow);
    }else{
      $('#tableBody').html($('#tableBody').html()+"<tr>"+myrow+"</tr>");
    }
  }
  $("#dataTable").colResizable({
    liveDrag:true,
    gripInnerHtml:"<div class='grip'></div>",
    draggingClass:"dragging",
    resizeMode:'fit'
  });

};
function isNumeric( obj ) {
  var realStringObj = obj && obj.toString();
  return !jQuery.isArray( obj ) && ( realStringObj - parseFloat( realStringObj ) + 1 ) >= 0;
}
function uploadMe() {
  if($('#starting_num').val()==''){
    //$('#starting_num').val(parseInt($('#checkNumber').val())+1);
  }
  //if(isNumeric(($('#starting_num').val())) && parseInt($('#checkNumber').val())>1){

  $('#msg1').text('');

  $('#data_file').trigger('click');
  $("#data_file").change(function (){
    //var fileName = $(this).val();
    //$(".filename").html(fileName);
    getResponse("0");
  });

  //}else{
  //	$('#starting_num').val('');
  //	$('#msg1').text('Error: Please enter a valid digit in Entry Field');
  //}
};
@endpush

@section('content')
<div class="container bg-white"  style="padding-top: 300px">
  <!-- Row Start  -->
  <div class="row" style="margin-left: 0px; margin-right: 0px;padding: 0px 100px;">

  <div class="col-lg-9 col-md-9">

    <!-- Row Start Check Build  -->
    <div class="row" >
    <h4>
      <div id='checkTitle' class="col-lg-9 col-md-9" style="border-bottom: 2px solid;">Check build:</div>
    </h4>
  </div>
    <!-- Row End Check Build  -->
  <br>
    <!-- Row Start  Build A Check -->
    <div class="row" >
    <div class="col-lg-12 col-md-12" style="padding-left: 0px;padding-right: 0px;">
      <h5 class="build"><strong>Build A Check From A Batch</strong></h5>
      </div>
    </div>
    <!-- Row End Build A Check  -->
  <div style="height: 15px;border-left: 2px solid #b3b3b3;margin-left: 2%;"></div>
    <!-- Row Start  Box 1 -->
    <div class="row" style="border: 2px solid #b3b3b3;padding-bottom: 4%;">

            <div class="col-lg-1 col-md-1">
              <h2 style="text-align: center;">1</h2>
            </div>

            <div class="col-lg-9 col-md-9">
              <h3 style="text-align: left;margin-top: 5%;">Select Batch File from which to build checks:</h3>
              <h3 class="start_check_num">Starting check Number:</h3>
            <input type="text" value='' id="starting_num" class="check_num_field">
            <div id="msg1"></div>
            </div>

            <div class="col-lg-2 col-md-2" style="padding: 0;"><br><br>

            <input type="hidden" id='checkNumber' value="<?php echo file_get_contents(base_path('src/library/LastNumber.ck')); ?>" >
            <input type="file" id='data_file'   name="data_file" class="data_file" style="display: none;">
            <input type="button" id="upload" class="btn selct_btn" value='Select' onClick="uploadMe();" >
            </div>

    </div>

    <!-- Row End Box 1   -->

    <div style="height: 20px;border-left: 2px solid #b3b3b3;margin-left: 2%;"></div>
    <!-- Row Start  Box 2 -->
    <div class="row" style="border: 2px solid #b3b3b3;padding-bottom: 4%;">

            <div class="col-lg-1 col-md-1">
              <h2 style="text-align: center;">2</h2>
            </div>
            <div class="col-lg-9 col-md-9">
              <h3 style="margin-top: 5%;">Proof the data:</h3>
              <h3 class="start_check_num">Mark as Manual Check:</h3>
            <input type="checkbox" id="ManualMark" name="ManualMark" class="check_num_check">
            </div>
            <div class="col-lg-2 col-md-2" style="padding: 0;"><br><br>
            <input type="button" id="review" class="btn review_btn" value='Review' onClick="review();" >

            </div>

    </div>

    <!-- Row End Box 2   -->

    <div style="height: 20px;border-left: 2px solid #b3b3b3;margin-left: 2%;"></div>
    <!-- 2nd Row Start  Box 2  -->
    <div class="row" style="border: 2px solid #b3b3b3;padding-bottom: 4%; margin-left: 2%;overflow-x:scroll">

            <div class="col-lg-12 col-md-12">
              <h4 style="">Data file review</h4>
      <table id="dataTable" width="800%" border="0" cellpadding="0" cellspacing="0" >
            <thead>
                <tr id="tableHead" style="background-color: grey;color: #ffffff;">

                </tr>
                </thead>
                <tbody id="tableBody">

                </tbody>
              </table>
            </div>

    </div>


    <!-- 2nd Row End Box 2   -->

    <div style="height: 20px;border-left: 2px solid #b3b3b3;margin-left: 2%;"></div>
    <!-- Row Start  Box 3 -->
    <div class="row" style="border: 2px solid #b3b3b3;padding-bottom: 4%;">

            <div class="col-lg-1 col-md-1">
              <h2 style="text-align: center;">3</h2>
            </div>
            <div class="col-lg-9 col-md-9">
              <h3 style="margin-top: 5%;">Review a sample of the first and last check:</h3>
            </div>

    </div>

    <!-- Row End Box 3   -->

    <div style="height: 20px;border-left: 2px solid #b3b3b3;margin-left: 2%;"></div>
    <!-- 2nd Row Start  Box 3  -->
    <div class="row" style="border: 2px solid #b3b3b3;padding-bottom: 4%; margin-left: 2%;">

            <div class="col-lg-12 col-md-12">
              <h4>First:</h4>
              <img id="first_img" src="" width="100%">
              <h4 style="">Last:</h4>
              <img id="last_img" src="" width="100%">
            </div>

    </div>

    <!-- 2nd Row End Box 3   -->

    <div style="height: 20px;border-left: 2px solid #b3b3b3;margin-left: 2%;"></div>
    <!-- Row Start  Box 4 -->
    <div class="row" style="border: 2px solid #b3b3b3;padding-bottom: 4%;">

            <div class="col-lg-1 col-md-1" style="">
              <h2 style="text-align: center;">4</h2>
            </div>
            <div class="col-lg-9 col-md-9">
              <h3 style="margin-top: 5%;">Build the file:</h3>
            </div>
            <div class="col-lg-2 col-md-2" style="padding: 0;"><br><br>
            <input type="button" id='savepdf' class="btn review_btn" value='Save' onClick='savepdf();'>
            </div>

    </div>

    <!-- Row End Box 5   -->

    <div style="height: 20px;border-left: 2px solid #b3b3b3;margin-left: 2%;"></div>
    <!-- Row Start  Box 4 -->
    <div class="row" style="border: 2px solid #b3b3b3;padding-bottom: 4%;">

            <div class="col-lg-1 col-md-1">
              <h2 style="text-align: center;">5</h2>
            </div>
            <div class="col-lg-9 col-md-9">
              <h3 style="margin-top: 5%;">Check batch Build is complete:</h3>
            </div>
            <div class="col-lg-2 col-md-2" style="padding: 0;"><br><br>
            <input type='button' class="btn finish_btn"  onClick='resetPage();' value='Finish'>
            </div>

    </div>
  <br><br>
    <!-- Row End Box 4   -->


  </div>
          <div class="col-lg-3 col-md-3"></div>
  </div>
  <!-- Row Main End  -->
</div>
@endsection
