<body>
<div id="now">(now)</div>
<div id="now2">(now2)</div>
<input type="date" id="now3">
<div id="longdate">(longdate)</div>
<pre id="yearpass">

</pre>
</body>
<script>
	const MONTHS = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
	function to_yyyymmdd(d){return `${d.getFullYear()}-${("0"+(d.getMonth()+1)).slice(-2)}-${("0"+d.getDate()).slice(-2)}`;}
	function to_ymd_nice(d){return `${MONTHS[d.getMonth()]} ${("0"+d.getDate()).slice(-2)}`;}
	let today2 = new Date();
	document.getElementById("longdate").innerText = to_yyyymmdd(today2);
	document.getElementById("now3").value = to_yyyymmdd(today2);
	let yearpass =document.getElementById("yearpass");
	for (let i = 0; i < 365; i++) {
		today2.setDate(today2.getDate()+1);
		yearpass.innerText += "\n"+to_ymd_nice(today2)+" ("+to_yyyymmdd(today2)+")";
	}
</script>