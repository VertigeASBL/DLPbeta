function insererbanniere() {
	chn = '<!--[if !IE]><!--><object id="combanniere" type="application/x-shockwave-flash" data="squelettes/assets/combanniere.swf"><!--><![endif]-->';
	chn += '<!--[if IE]><object id="combanniere" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"><![endif]-->';
	chn += '<param name="movie" value="squelettes/assets/combanniere.swf" /><param name="quality" value="high" /><param name="wmode" value="transparent" /></object>';
	document.write(chn);
}
