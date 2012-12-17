<?php
# BEGIN OPi

/*
  Les versions JavaScript de ces fonctions sont dans squelettes/js/video_embed.js
*/



global $videos_embed_fct;

// Tableau associant les types de vidéos supportées avec la fonction retournant le code embed
$videos_embed_fct = array('dailymotion' => 'dailymotion_embed',
                          'vimeo'       => 'vimeo_embed',
                          'youtube'     => 'youtube_embed');



/*
  Renvoie le code d'intégration d'une vidéo Dailymotion.

  Pre: $video_id: string
       $width: int > 0
       $height: int > 0

  Result: string
*/
function dailymotion_embed($video_id, $width, $height) {
/*
  assert( is_string($video_id) );
  assert( is_int($width) && ($width > 0) );
  assert( is_int($height) && ($height > 0) );
*/
  return '<span class="video-player dailymotion-player">
  <object width="'.$width.'" height="'.$height.'">
    <param name="allowFullScreen" value="true" />
    <param name="allowScriptAccess" value="always" />
    <param name="movie"
      value="http://www.dailymotion.com/swf/video/'.$video_id.'" />
    <embed type="application/x-shockwave-flash"
      src="http://www.dailymotion.com/swf/video/'.$video_id.'"
      width="'.$width.'" height="'.$height.'"
      allowfullscreen="true"
      allowscriptaccess="always">
    </embed>
  </object>
</span>';
}


/*
  Si $url est l'adresse d'une vidéo Dailymotion, Vimeo ou YouTube
  alors renvoie dans un array le nom du site et l'identifiant de la vidéo
  sinon renvoie NULL

  Pre: $url: string

  Result: NULL ou array('which'    => string,
                        'video_id' => string)
*/
function url_to_video_id($url) {
//  assert( is_string($url) );
  
  if(is_array($url) && is_string($url[0])) $url=$url[0];
  if(!is_string($url)) return NULL;

  $which = 'youtube';

  // '   youtube.   /   ?v=(id)   '
  preg_match('|youtube\..+?/.*?\?v=([-0-9A-Z_a-z]{11})|', $url, $matches);

  if ( empty($matches[1]) ) {
    // '   youtube.   /   ?   &v=(id)   '
    preg_match('|youtube\..+?/.*?\?.*?v=([-0-9A-Z_a-z]{11})|', $url, $matches);

    if ( empty($matches[1]) ) {
      // '   youtube.   /v/(id)   '
      preg_match('|youtube\..+?/v/([-0-9A-Z_a-z]{11})|', $url, $matches);


      if ( empty($matches[1]) ) {
        $which = 'dailymotion';

        // '   dailymotion.   /video/(id)_   '
        preg_match('|dailymotion\..+?/video/([-0-9A-Za-z]+?)_|', $url, $matches);


        if ( empty($matches[1]) ) {
          $which = 'vimeo';

          // '   http://vimeo.com/(id)   '
          preg_match('|vimeo\..+?/([0-9]+)|', $url, $matches);


          if ( empty($matches[1]) )
            return NULL;
        }
      }
    }
  }

  return array('which'    => $which,
               'video_id' => $matches[1]);
}


/*
  Renvoie le code d'intégration d'une vidéo YouTube.

  Pre: $video_id: string
       $width: int > 0
       $height: int > 0
       $https: bool
       $others: bool

  Result: string
*/
function youtube_embed($video_id, $width, $height, $https=FALSE, $others=FALSE) {
/*
  assert( is_string($video_id) );
  assert( is_int($width) && ($width > 0) );
  assert( is_int($height) && ($height > 0) );
  assert( is_bool($https) );
  assert( is_bool($others) );
*/
  return '<span class="video-player youtube-player">
  <iframe title="YouTube video player" width="'.$width.'" height="'.$height
    .'" src="http'.($https
                    ? 's'
                    : '').'://www.youtube.com/embed/'.$video_id.($others
                                                                 ? ''
                                                                 : '?rel=0')
    .'" frameborder="0" allowfullscreen></iframe>
</span>';
}


/*
  Renvoie le code d'intégration d'une Dailymotion, YouTube ou Vimeo.

  Pre: $which: string parmi les clés du tableau global $videos_embed_fct
       $video_id: string
       $width: int > 0
       $height: int > 0
       $https: bool
       $others: bool

  Result: string
*/

function video_embed($which, $video_id, $width, $height, $https=FALSE, $others=FALSE) {
  $videos_embed_fct = array('dailymotion' => 'dailymotion_embed',
                          'vimeo'       => 'vimeo_embed',
                          'youtube'     => 'youtube_embed');
/*
  assert( is_string($which) && array_key_exists($which, $videos_embed_fct) );
  assert( is_string($video_id) );
  assert( is_int($width) && ($width > 0) );
  assert( is_int($height) && ($height > 0) );
  assert( is_bool($https) );
  assert( is_bool($others) );
*/
	$return=$videos_embed_fct[$which]?$videos_embed_fct[$which]($video_id, $width, $height, $https, $others):'';
  return $return;
}


/*
  Renvoie le code d'intégration d'une vidéo Vimeo.

  Pre: $video_id: string
       $width: int > 0
       $height: int > 0

  Result: string
*/
function vimeo_embed($video_id, $width, $height) {
/*
  assert( is_string($video_id) );
  assert( is_int($width) && ($width > 0) );
  assert( is_int($height) && ($height > 0) );
*/
  return '<iframe src="http://player.vimeo.com/video/'.$video_id.'" width="'.$width.'" height="'.$height.'" frameborder="0">
</iframe>';
}

# END OPi
?>