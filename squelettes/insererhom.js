function insererhom(md) {
	chn = '<!--[if !IE]><!--><object id="hom-'+md+'" type="application/x-shockwave-flash" data="squelettes/assets/hom/'+md+'.swf"><!--><![endif]-->';
	chn += '<!--[if IE]><object id="hom-'+md+'" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"><![endif]-->';
	chn += '<param name="movie" value="squelettes/assets/hom/'+md+'.swf" /><param name="quality" value="high" /><param name="wmode" value="transparent" /></object>';
	document.write(chn);
}
