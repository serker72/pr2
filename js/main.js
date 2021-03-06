$( function() {


	$('.menu-header').on('click', function() {
		 
		$(this).parent('a').siblings('ul').toggle()  //.slideToggle(500)
		$(this).toggleClass('active')
		$(this).parent('a').toggleClass('active');
		
		return false;
	});
	
	$("#fast_reports_toggle").on('click', function(){
		$('#fast_reports').toggle() //.slideToggle(500)
		$(this).toggleClass('active');
		
	});
	
	$(".reestr_short").on('click',function(){
		$(this).next(".reestr_full").removeClass("reestr_hide");
		$(this).addClass("reestr_hide");
		return false;
	});
	
	$(".menu-header").each(function(index, element) {
		
		
		if($(element).parent('a').siblings('ul').find("a.cter").length>0){
			$(element).parent('a').siblings('ul').show() 
			$(element).addClass('active')
			$(element).parent('a').addClass('active');
		}
    });
	
	/*function ToggleSize(){
		//1100px$("#footer-wrapper").
		w=Math.round($(".content_inner").width())+Math.round($(".left-bar").width());
				if(w<=Math.round($(".header").width())) {
					
					//alert(w);
					$(".footer-wrapper").css("width", "100%"  ); 
					$(".header").css("width",  "100%" ); 
				}else{
	//	alert(w);
					$(".footer-wrapper").css("width", w  ); 
					$(".header").css("width", w  ); 
				}//console.log('set width='+ui.size.width);			
	}
	
	ToggleSize();
	
	$(window).bind("resize", function(){
		ToggleSize();
	});*/
	
});

function strip_tags(input, allowed) {
  //  discuss at: http://phpjs.org/functions/strip_tags/
  // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: Luke Godfrey
  // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //    input by: Pul
  //    input by: Alex
  //    input by: Marc Palau
  //    input by: Brett Zamir (http://brett-zamir.me)
  //    input by: Bobby Drake
  //    input by: Evertjan Garretsen
  // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // bugfixed by: Onno Marsman
  // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // bugfixed by: Eric Nagel
  // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // bugfixed by: Tomasz Wesolowski
  //  revised by: Rafal Kukawski (http://blog.kukawski.pl/)
  //   example 1: strip_tags('<p>Kevin</p> <br /><b>van</b> <i>Zonneveld</i>', '<i><b>');
  //   returns 1: 'Kevin <b>van</b> <i>Zonneveld</i>'
  //   example 2: strip_tags('<p>Kevin <img src="someimage.png" onmouseover="someFunction()">van <i>Zonneveld</i></p>', '<p>');
  //   returns 2: '<p>Kevin van Zonneveld</p>'
  //   example 3: strip_tags("<a href='http://kevin.vanzonneveld.net'>Kevin van Zonneveld</a>", "<a>");
  //   returns 3: "<a href='http://kevin.vanzonneveld.net'>Kevin van Zonneveld</a>"
  //   example 4: strip_tags('1 < 5 5 > 1');
  //   returns 4: '1 < 5 5 > 1'
  //   example 5: strip_tags('1 <br/> 1');
  //   returns 5: '1  1'
  //   example 6: strip_tags('1 <br/> 1', '<br>');
  //   returns 6: '1 <br/> 1'
  //   example 7: strip_tags('1 <br/> 1', '<br><br/>');
  //   returns 7: '1 <br/> 1'

  allowed = (((allowed || '') + '')
    .toLowerCase()
    .match(/<[a-z][a-z0-9]*>/g) || [])
    .join(''); // making sure the allowed arg is a string containing only tags in lowercase (<a><b><c>)
  var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
    commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
  return input.replace(commentsAndPhpTags, '')
    .replace(tags, function ($0, $1) {
      return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
    });
}