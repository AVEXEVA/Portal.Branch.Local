function formatTicket ( d ) {
	return "<div>"+
		"<div>"+
			"<div class='column'>"+
				"<div class='Account'><div class='label1'>Account:</div><div class='data'>"+d.Customer+"</div></div>"+
				"<div class='Location'><div class='label1'>Location:</div><div class='data'>"+d.Location+"</div></div>"+
				"<div class='Address'><div class='label1'>Address:</div><div class='data'>"+d.Street+"</div></div>"+
				"<div class='Address'><div class='label1'>&nbsp;</div><div class='data'>"+d.City+" ,"+d.City+" "+d.Zip+"</div></div>"+
				"<div class='Caller'><div class='label1'>Caller:</div><div class='data'>"+d.Caller+"</div></div>"+
				"<div class='Taken_By'><div class='label1'>Taken By:</div><div class='data'>"+d.Taken_By+"</div></div>"+
			"</div>"+
			"<div class='column'>"+
				"<div class='Created'><div class='label1'>Created:</div><div class='data'>"+d.Created+"</div></div>"+
				"<div class='Dispatched'><div class='label1'>Dispatched:</div><div class='data'>"+d.Dispatched+"</div></div>"+
				"<div class='Type'><div class='label1'>Type:</div><div class='data'>"+d.Job_Type+"</div></div>"+
				"<div class='Level'><div class='label1'>Level:</div><div class='data'>"+d.Level+"</div></div>"+
				"<div class='Category'><div class='label1'>Category:</div><div class='data'>"+d.Category+"</div></div>"+
			"</div>"+
			"<div class='column'>"+
				"<div class='Regular'><div class='label1'>On Site:</div><div class='data'>"+d.On_Site.substr(10,9)+"</div></div>"+
				"<div class='Regular'><div class='label1'>Completed:</div><div class='data'>"+d.Completed.substr(10,9)+"</div></div>"+
				"<div class='Regular'><div class='label1'>Regular:</div><div class='data'>"+d.Regular+"</div></div>"+
				"<div class='OT'><div class='label1'>OT:</div><div class='data'>"+d.Overtime+"</div></div>"+
				"<div class='Doubletime'><div class='label1'>DT:</div><div class='data'>"+d.Doubletime+"</div></div>"+
				"<div class='Total'><div class='label1'>Total</div><div class='data'>"+d.Total+"</div></div>"+
			"</div>"+
		"</div>"+
		"<div>"+
			"<div class='column' style='width:45%;vertical-align:top;'>"+
				"<div><b>Scope of Work</b></div>"+
				"<div><pre>"+d.Description+"</div>"+
			"</div>"+
			"<div class='column' style='width:45%;vertical-align:top;'>"+
				"<div><b>Resolution</b></div>"+
				"<div><pre>"+d.Resolution+"</div>"+
			"</div>"+
		"</div>"+
	'</div>'+
	"<div><a href='ticket.php?ID="+d.ID+"'>View Ticket</a></div>"
}