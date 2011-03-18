<?php
define('VH_CONTROLS', true);
define('VH_PRELOAD', false);
define('VH_AUTOPLAY', false);

/*	The following constants are required to support a flash fallback.
	They may be left uncommented at your discretion. */
// define('VH_FLASH_PLAYER_LOCATION', 'path_to_flash_player');
// define('VH_FLASH_PLAYER_FILETYPES', serialize(array('mp4', 'flv')));
/**
 * Embed Video
 *
 * @access	public
 * @param	array	An array of paths the video files
 * @param	string	A title for the video (optional)
 * @param	string	The path to a "preview" screenshot (optional)
 * @return	string	The valid HTML5
 */ 
if ( ! function_exists('embed_video'))
{
	function embed_video($paths, $title = NULL, $screenshot = NULL)
	{
		$html5_types = array(
			'mp4' => 'video/mp4',	// MP4 must be first for iPads running iOS 3.x
			'webm' => 'video/webm',
			'ogv' => 'video/ogg'
		);
		$flash_player_filetypes = unserialize(constant('VH_FLASH_PLAYER_FILETYPES'));
		
		$fallback_html = 'Your browser does not support video playback.';
		if($screenshot !== NULL)
			$fallback_html = '<img src="' .$screenshot. '" alt="' .$title. '" title="Your browser does not support video playback." />' . $fallback_html;
		$paths = array_map("trim", $paths);
		
		// Build array of video file paths and their filetypes
		$typesandpaths = array();
		foreach($paths as $path)
		{
			$parts = explode('.', $path);
			if(count($parts) < 2)
				continue;
			$type = $parts[count($parts)-1];
			if( ! array_key_exists($type, $html5_types) && ! in_array($type, $flash_player_filetypes))
				continue;
			$typesandpaths[$type] = $path;
		}
		if(count($typesandpaths) < 1)
			return;
		
		// Construct the <video> element
		$html = '<video';
		if(constant('VH_CONTROLS') === true)
			$html .= ' controls';
		if(constant('VH_PRELOAD') === true)
			$html .= ' preload';
		if(constant('VH_AUTOPLAY') === true)
			$html .= ' autoplay';
		$html .= '>';
		foreach($html5_types as $html5_filetype => $html5_mimetype)
			if(array_key_exists($html5_filetype, $typesandpaths))
				$html .= '<source src="' .$typesandpaths[$html5_filetype]. '" type="' .$html5_mimetype. '" />';
		
		// Construct the fallback flash player
		if(defined('VH_FLASH_PLAYER_LOCATION') && constant('VH_FLASH_PLAYER_LOCATION') !== NULL)
		{
			$printed_flash_player = false;
			foreach($typesandpaths as $type => $path)
			{
				if(in_array($type, $flash_player_filetypes)) {
					$html .= '<object type="application/x-shockwave-flash" data="' .constant('VH_FLASH_PLAYER_LOCATION'). '">';
					$html .= '<param name="movie" value="' .constant('VH_FLASH_PLAYER_LOCATION'). '" />';
					$html .= '<param name="flashvars" value="controlbar=over&amp;';
					if(constant('VH_AUTOPLAY') === true)
						$html .= 'autostart=true&amp;';
					if($screenshot !== NULL)
						$html .= 'image=' .$screenshot. '&amp;';
					$html .= 'file=' .$path. '" />';
					$html .= $fallback_html;
					$html .= '</object>';
					$printed_flash_player = true;
					break;
				}
			}
			if( ! $printed_flash_player) {
				$html .= $fallback_html;
			}
		} else
			$html .= $fallback_html;
		
		$html .= "</video>\r\n";
		
		return $html;
	}
}
/* End of file video_helper.php */