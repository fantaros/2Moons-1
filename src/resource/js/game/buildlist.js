var resttime	= 0;
var time		= 0;
var endtime		= 0;
var interval	= 0;
var buildname	= "";

function Buildlist() {
	var rest	= resttime - (serverTime.getTime() - startTime) / 1000;
	if (rest <= 0) {
		window.clearInterval(interval);
		$('#time').text(Ready);
		$('#command').remove();
		document.title	= Ready + ' - ' + Gamename;
		window.setTimeout(function() {
			window.location.href = 'game.php?page=buildings';
		}, 1000);
	}
    else
    {
        console.log(GetRestTimeFormat(rest), rest);
        document.title	= GetRestTimeFormat(rest) + ' - ' + buildname + ' - ' + Gamename;
        $('#time').text(GetRestTimeFormat(rest));
    }


}



$(document).ready(function() {
	time		= $('#time').data('time');
	resttime	= $('#progressbar').data('time');
	endtime		= $('.timer:first').data('time');
	buildname	= $('.onlist:first').text();
	interval	= window.setInterval(Buildlist, 1000);
	window.setTimeout(function() {
        if(time != 0) {
            $('#progressbar').progressbar({
                value: Math.max(100 - (resttime / time) * 100, 0.01)
            });
            $('.ui-progressbar-value').addClass('ui-corner-right').animate({width: "100%"}, resttime * 1000, "linear");
        }
    }, 5);

	Buildlist();
});