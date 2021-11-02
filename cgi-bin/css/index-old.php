<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- Bootstrap Core CSS -->
<link href="https://www.nouveauelevator.com/vendor/bootstrap/css/bootstrap.css?v=2" rel="stylesheet">

<!-- MetisMenu CSS -->
<link href="https://www.nouveauelevator.com/vendor/metisMenu/metisMenu.min.css" rel="stylesheet">

<!-- Custom CSS -->
<link href="../dist/css/sb-admin-2.css" rel="stylesheet">

<!--SB ADMIN TOGGLE CSS-->
<link href="cgi-bin/css/sbadmin2-sidebar-toggle.css" rel="stylesheet" type="text/css">

<!-- Morris Charts CSS -->
<link href="https://www.nouveauelevator.com/vendor/morrisjs/morris.css" rel="stylesheet">

<!-- Custom Fonts -->
<link href="https://www.nouveauelevator.com/vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

<!-- JQUERY UI CSS-->
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
<style>
input:valid {}
input:invalid {background-color:#ff3232;}
input, textarea, select, option {
  background-color:white !important;
}
</style>
<style>
.panel-primary .panel-body {
	background-color:#2d2d2d !important;
	color:white;
}
.panel-primary .panel-body table tbody {
	color:black;
}
.panel-primary .panel-body table tbody tr.odd {
	background-color:#3d3d3d !important;
	color:white;
}
.panel-primary .panel-body table tbody tr.even {
	background-color:#2d2d2d !important;
	color:white;
}
.panel-primary .panel-body table tbody tr td.sorting_1 {
	background-color:#1d1d1d !important;
	color:white;
}
.panel-primary label, .panel-primary .panel-body li.paginate_button.active a {
	color:white !important;
}
.panel-primary .panel-body input, .panel-primary .panel-body button, .panel-primary .panel-body textarea, .panel-primary .panel-body a, .panel-primary .panel-body select, .panel-primary .panel-body li.paginate_button a {
	color:black !important;
}
.panel-primary .panel-body a {
	color:white !important;
}
</style>
<style>
.panel-heading.heading-light {
	background-color:#454545 !important;
}
.shadower {
					-webkit-box-shadow: 0px 3px 3px 0px rgba(0,0,0,0.75);
					-moz-box-shadow: 0px 3px 3px 0px rgba(0,0,0,0.75);
					box-shadow: 0px 3px 3px 0px rgba(0,0,0,0.75);
					border-bottom:1px solid black;
				}
</</style>
<Style>
	html {
		min-height:100%;
		<?php if(!isMobile() && FALSE){?>background-image:url('https://www.nouveauelevator.com/Images/Backgrounds/New_York_City_Skyline.jpg');
		webkit-background-size: cover;
		-moz-background-size: cover;
		-o-background-size: cover;
		background-size: cover;<?php }?>
		height:100%;
	}
	body {
		min-height:100%;
		background-size:cover;
		background-color:rgba(255,255,255,.7);
		height:100%;
	}
	#page-wrapper {
		background-color:transparent !important;
		<?php if(isMobile()){?>min-height:0px !important;<?php }?>
	}
</Style>
<style>
	.hidden {display:none;}
.toggled ul#side-menu ul.second-level {
  background-color:white;
}
</style>
<style>
td.details-control {
    background: url('css/icon/details_open.png') no-repeat center center;
    cursor: pointer;
}
tr.shown td.details-control {
    background: url('css/icon/details_close.png') no-repeat center center;
}
</style>

<style>
	#page-wrapper>div.row>div.col-lg-12 {
		padding:0px;
	}
/*#page-wrapper {
	position:relative !important;
}
.pg-loading-screen {
	position:absolute !important;
	display:block !important;
}*/
</style>
<style>
@media print {
  a[href]:after {
    content: none !important;
  }
	#wrapper {
		overflow-y:auto !important;
	}
	#page-wrapper {
		height:auto !improtant;
	}
	body {
		height:auto !important;
	}
	.dataTables_scrollBody {
		height:auto !important;
		overflow:auto !important;
		max-height:none !important;
	}
	.dt-buttons {
		display:none !important;
	}
	.nav-tabs.nav {
		display:none !important;
	}
}
body {
	height:100%;
}
#wrapper {
	overflow-y:scroll;
	height:100%;
}
<?php if(!isMobile()){?>#page-wrapper {
	height:100%;
	/*overflow-y:scroll;*/
}<?php }?>
@font-face {
  font-family: 'BankGothic';
  src: url('../css/font/bankgothic-md-bt-medium-webfont.eot'); /* IE9 Compat Modes */
  src: url('../css/font/bank-gothic-md-bt-medium-1361510860.ttf')  format('truetype'); /* IE9 Compat Modes */
}
.BankGothic {
  font-family:'BankGothic';
}
</style>
<style>
    div#page-wrapper.loading {
        display:block;
    }
    div#page-wrapper.content {
        display:none;
    }
</style>
<style>.sk-cube-grid {
  width: 40px;
  height: 40px;
  margin: 100px 0px 0px 0px;
}

.sk-cube-grid .sk-cube {
  width: 33%;
  height: 33%;
  background-color: #333;
  float: left;
  -webkit-animation: sk-cubeGridScaleDelay 1.3s infinite ease-in-out;
          animation: sk-cubeGridScaleDelay 1.3s infinite ease-in-out;
}
.sk-cube-grid .sk-cube1 {
  -webkit-animation-delay: 0.2s;
          animation-delay: 0.2s; }
.sk-cube-grid .sk-cube2 {
  -webkit-animation-delay: 0.3s;
          animation-delay: 0.3s; }
.sk-cube-grid .sk-cube3 {
  -webkit-animation-delay: 0.4s;
          animation-delay: 0.4s; }
.sk-cube-grid .sk-cube4 {
  -webkit-animation-delay: 0.1s;
          animation-delay: 0.1s; }
.sk-cube-grid .sk-cube5 {
  -webkit-animation-delay: 0.2s;
          animation-delay: 0.2s; }
.sk-cube-grid .sk-cube6 {
  -webkit-animation-delay: 0.3s;
          animation-delay: 0.3s; }
.sk-cube-grid .sk-cube7 {
  -webkit-animation-delay: 0s;
          animation-delay: 0s; }
.sk-cube-grid .sk-cube8 {
  -webkit-animation-delay: 0.1s;
          animation-delay: 0.1s; }
.sk-cube-grid .sk-cube9 {
  -webkit-animation-delay: 0.2s;
          animation-delay: 0.2s; }

@-webkit-keyframes sk-cubeGridScaleDelay {
  0%, 70%, 100% {
    -webkit-transform: scale3D(1, 1, 1);
            transform: scale3D(1, 1, 1);
  } 35% {
    -webkit-transform: scale3D(0, 0, 1);
            transform: scale3D(0, 0, 1);
  }
}

@keyframes sk-cubeGridScaleDelay {
  0%, 70%, 100% {
    -webkit-transform: scale3D(1, 1, 1);
            transform: scale3D(1, 1, 1);
  } 35% {
    -webkit-transform: scale3D(0, 0, 1);
            transform: scale3D(0, 0, 1);
  }
}</style>
<style>
.nav li:hover a:hover, .nav ul li a.active {
  background-color:#151515 !important;
  color:white !important;
}
</style>
<Style>
#Table_Tickets tbody tr, #Table_Units tbody tr, #Table_Customers tbody tr, #Table_Violations tbody tr, #Table_Proposals tbody tr, #Table_Jobs tbody tr, #Table_Invoices tbody tr, #Table_Locations tbody tr {cursor:pointer;}
</Style>
 <style type="text/css" media="print">
        div#page-wrapper {margin:0px !important;}
        .no-print {
            display:none !important;
            height:0px !important;
            margin:0px;
            padding:0px;
        }
        .print {
            display:block !important;
            page-break-before:avoid;
        }
        hr {
            margin-bottom:10px;
            margin-top:10px;
        }
        pre {
white-space: normal !important;
}
    </style>
    <style type='text/css'>
        .print {
            display:none;
        }
        .no-print {
            display:block;
        }
        pre {
            white-space: -moz-pre-wrap; /* Mozilla, supported since 1999 */
            white-space: -pre-wrap; /* Opera */
            white-space: -o-pre-wrap; /* Opera */
            white-space: pre-wrap; /* CSS3 - Text module (Candidate Recommendation) http://www.w3.org/TR/css3-text/#white-space */
            word-wrap: break-word; /* IE 5.5+ */
        }

    </style>
    <style media='print'>
    .dataTables_wrapper div.row:nth-child(1) {
        display:none;
    }
    </style>
<style>
.dropbtn {
    background-color: #4CAF50;
    color: white;
    padding: 16px;
    font-size: 16px;
    border: none;
    cursor: pointer;
}

.dropbtn:hover, .dropbtn:focus {
    background-color: #3e8e41;
}

.dropdown {
    float: right;
    position: relative;
    display: inline-block;

}

.dropdown-content {
    display: none;
    position: absolute;
    background-color: #f9f9f9;
    min-width: 160px;
    overflow: auto;
    box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
    right: 0;
    z-index: 1;
}

.dropdown-content a {
    color: black;
    padding: 12px 16px;
    text-decoration: none;
    display: block;
}

.dropdown a:hover {background-color: #f1f1f1}

.show {display:block;}
</style>
<style>
div#wrapper.toggled ul.collapse.in:not([aria-expanded]) {
  display:none !important;
}
</style>
<?php  if(!isMobile()){?>
<script>
/*$(document).ready(function(){
	$(".navbar-transparent").css("height",($(window).height() - 50) + "px");
})*/
</script>
<?php }?>
<style>
<?php if(!isMobile()){?>
#page-wrapper {
  min-height: 800px;
  background-color: white;
}
div#page-wrapper {
  padding-top:50px;
}
.nav-tabs {
	border:0px;
}
div#page-wrapper {
	border:0px !important;
}
.navbar-transparent {
	min-height:1250px;
}
.navbar-transparent {
  background-color:rgba(255,255,255,.6) !important;
  border-color: #e7e7e7;
}
.sidebar {
    z-index: 1;
    position: fixed !important;
    width: 250px;
    margin-top: 51px;
}<?php }?>
</style>
