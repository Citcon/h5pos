<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<link rel='stylesheet/less' type="text/css" href="assets/style.less">
<link rel="stylesheet" href="assets/font-awesome.css">
<link rel='stylesheet' href="assets/bootstrap.min.css">
<script src="assets/jquery.min.js"></script>
<script src="assets/bootstrap.min.js"></script>
<script src="assets/less.js" type="text/javascript"></script>
<script src="https://momentjs.com/downloads/moment.min.js"></script>
<!--<script src="https://dwa012.github.io/html5-qrcode/javascripts/html5-qrcode.min.js"></script>-->
<script src="./h5qr/lib/html5-qrcode.min.js"></script>
<script src="./h5qr/lib/jsqrcode-combined.min.js"></script>
<script type="text/javascript">
var codeScanned = false;
var inQuery = false;
function scan() {
    //startTimer();
    $('#reader').html5_qrcode(
        function(data){
            if (!codeScanned) {
                codeScanned = true;
                //alert(data);
                $('#reader').html5_qrcode_stop();
                $('#reader').hide();
                ajaxPay(data);
            }
        },
        function(error){
            $('#read_error').html(error);
        }, 
        function(videoError){
            $('#vid_error').html(videoError);
        }
    );
}
function turnOffCamera() {
    $('#reader').html5_qrcode_stop();
}
function ajaxPay(code) {
    var http = new XMLHttpRequest();
    var url = "pay.php";
    var params = "code="+code+"&price="+(document.getElementById("price").value*100)+"&tip=0&pos_local_time=" + moment().format('YYYY-MM-DD HH:mm:ss');
    http.open("POST", url, true);

    //Send the proper header information along with the request
    http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    http.onreadystatechange = function() {//Call a function when the state changes.
        if(http.readyState == 4 && http.status == 200) {
            json_result = JSON.parse(http.responseText);
            if (json_result.code == '00') {
                printReceipt(json_result.code, json_result.transaction_id, json_result.total, json_result.tip, json_result.subtotal, json_result.merchant_id, json_result.terminal_id, json_result.method, json_result.pos_local_time);
            } else if (json_result.code == '09') {
                // keep query
                inQuery = true;
                startTimer(function() {ajaxQuery(json_result.transaction_id, 1);}, 3);
            } else {
                alert('Error! ' + json_result.error_message);
            }
        }
    }
    http.send(params);
}
function ajaxQuery(transaction_id, retries) {
    if (!inQuery) {
        //alert("Query stopped");
        return;
    }
    if (retries > 5) {
        alert("Error! Inquired too many times");
        inQuery = false;
        return;
    }
    var http = new XMLHttpRequest();
    var url = "inquire.php";
    var params = "transaction_id="+transaction_id+"&pos_local_time=" + moment().format('YYYY-MM-DD HH:mm:ss');
    http.open("POST", url, true);

    //Send the proper header information along with the request
    http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    http.onreadystatechange = function() {//Call a function when the state changes.
        if(http.readyState == 4 && http.status == 200) {
            json_result = JSON.parse(http.responseText);
            if (json_result.code == '00') {
                inQuery = false;
                printReceipt(json_result.code, transaction_id, json_result.total, json_result.tip, json_result.subtotal, json_result.merchant_id, json_result.terminal_id, json_result.method, json_result.pos_local_time);
            } else if (json_result.code == '09') {
                // keep query
                startTimer(function() {ajaxQuery(json_result.transaction_id, retries+1);}, 3);
            } else {
                alert('Error! ' + json_result.error_message);
            }
        }
    }
    http.send(params);
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    modal = document.getElementById('myModal');
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
function printReceipt(pos_code, transaction_id, total, tip, subtotal, merchant_id, terminal_id, method, pos_local_time) {
    receipt = '<h3>Receipt</h3><table><tr><td>MERCHANT NO.:</td><td>' + merchant_id + '</td></tr>' +
        '<tr><td>TERMINAL NO.:</td><td>' + terminal_id + '</td></tr>' +
        '<tr><td>TRANS TYPE:</td><td><font size="3">' + method + '</font></td></tr>' +
        '<tr><td>REF. NO.:</td><td>' + transaction_id + '</td></tr>' +
        '<tr><td>DATE/TIME:</td><td>' + pos_local_time + '</td></tr>' +
        '<tr><td>TOTAL AMOUNT</td><td><font size="5">USD ' + (total/100).toFixed(2) + '</td></tr>' +
        '</table>';    
    document.getElementById('receipt').value = receipt;
    modal = document.getElementById('myModal');
    modal.innerHTML = receipt + '<br/><br/><label>Email:</label><div class="row"><div class="col-xs-11"><input type="text" class="form-control" name="to_email" id="to_email" placeholder="eg., john.smith@email.com"></div></div><div class="row"><div class="col-xs-11"><button id="email_receipt" class="btn" onclick="sendemail()">Email Receipt</button></div></div>';
    modal.style.display = "block";
}
function sendemail() {
    //alert("ok");
    if (document.getElementById("to_email").value == "") {
        alert("Please enter valid recipient email addresses.");
        return;
    }
    var http2 = new XMLHttpRequest();
    var url = "email.php";
    //alert("ok2");
    var params = "to=" + encodeURIComponent(document.getElementById("to_email").value) +
                 "&cc=" + encodeURIComponent('kenny.shi@citcon-inc.com') +
                 "&from_name=" + encodeURIComponent('Citcon USA') +
                 "&from_email=" + encodeURIComponent('kenny.shi@citcon-inc.com') + 
                 "&subject=" + encodeURIComponent('Thank you for your purchase. This is your receipt.');
    //alert(params);
/*
    var message = document.getElementById("message_pre").value;
    message = message + "<br><img src=\"" + document.getElementById("qr_url").value + "\"><br>";
    message = message + document.getElementById("message_post").value;
*/
    params = params + "&message=" + encodeURIComponent(document.getElementById('receipt').value);
    //alert(tinymce.get('email_body').getContent({format : 'raw'}));
    //alert(params);
    http2.open("POST", url, true);

    //Send the proper header information along with the request
    http2.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    http2.onreadystatechange = function() {//Call a function when the state changes.
        if(http2.readyState == 4 && http2.status == 200) {
            alert("email sent");
        } else {
	    //alert("email failed");
	}
    }
    http2.send(params);
}
function startTimer(func, seconds) {
    var myVar = setInterval(func, seconds*1000);
}
function checkCurrency() {
    var regex  = /^\d+(?:\.\d{0,2})$/;
    var numStr = document.getElementById("price").value;
    if (!regex.test(numStr)) {
        alert("Please enter a valid amount in X.XX format");
        return false;
    } else {
        return true;
    }
}
</script>
<style>
/* The Modal (background) */
.modal {
    display: none; /* Hidden by default */
    position: fixed; /* Stay in place */
    z-index: 1; /* Sit on top */
    left: 0;
    top: 0;
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    overflow: auto; /* Enable scroll if needed */
    background-color: rgb(255,255,255); /* Fallback color */
    background-color: rgba(255,255,255,1); /* Black w/ opacity */
}

/* Modal Content/Box */
.modal-content {
    background-color: #fefefe;
    margin: 15% auto; /* 15% from the top and centered */
    padding: 20px;
    border: 1px solid #888;
    width: 80%; /* Could be more or less, depending on screen size */
}

/* The Close Button */
.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}
</style>
</head>

<body>

<p><p><p><center><img src="http://www.citcon-inc.com/images/logo.png"></center><p>

<div class="body-left">
	<h1>Create a Transaction</h1>
	<form id="thisForm">
		<div class="form-group">
			<label for="price" class="">Amount (US$): </label>
			<div class="row">
				<div class="col-xs-11">
					<input type="text" class="form-control" id="price" placeholder="X.XX" value="">
				</div>		
			</div>
		</div>
		<div class="row">
			<div class="col-xs-11">
				<input type="hidden" id="receipt" name="receipt"><button type="button" class="btn" onclick="if (checkCurrency()) scan();">Scan</button>
			</div>
		</div>
                <div class="row">
                        <div class="col-xs-11">
                            <div id="reader" style="width:300px;height:250px">
                        </div>
                </div>
	</form>
</div>
<!-- The Modal -->
<div id="myModal" class="modal">

  <!-- Modal content -->
  <div class="modal-content" id="model-content">
    <span class="close">&times;</span>
    <p>Some text in the Modal..</p>
  </div>

</div>
</body>
</html>
