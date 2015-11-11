$(document).ready(function()
{
	slide(".menu", 30, 20, .8);
});

function slide(navigation_id, pad_out, pad_in, time, multiplier)
{
	// creates the target paths
	var list_elements = navigation_id + ".menu li";
	var link_elements = list_elements + " a";

	// creates the hover-slide effect for all link elements 		
	$(link_elements).each(function(i)
	{
		$(this).hover(
		function()
		{
			$(this).stop().animate({ paddingLeft: pad_out }, 250);
		},		
		function()
		{
			$(this).stop().animate({ paddingLeft: pad_in }, 250);
		});
	});
}
$(function() {
    // setup ul.tabs to work as tabs for each div directly under div.panes
    $("ul.tabs").tabs("div.panes > div", {effect: 'fade', fadeOutSpeed: 400});
});