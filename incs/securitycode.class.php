<?php

/**
 * Security code image maker
 *
 * @package Limny
 * @author Hamid Samak <hamid@limny.org>
 * @copyright 2009-2016 Limny
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class SecurityCode {
	public $code;
	public $length = 6;
	public $characters = '0123456789';
	public $letter_spacing = 1.75;

	public $width = 180;
	public $height = 60;

	public $font_path;
	public $font_size = 48;

	public $background = [255, 255, 255];
	public $text_color = [0, 0, 0];

	public $angle = [15, 45];
	public $position = [5, 5];

	private $image;
	private $default_font_path;

	public function __construct() {
		$this->default_font_path = PATH . DS . 'misc' . DS . 'font';

		$this->generate();
	}

	private function load_font() {
		if (empty($this->font_path) === false)
			return true;
		else if (file_exists($this->default_font_path) && is_dir($this->default_font_path)) {
			foreach (scandir($this->default_font_path) as $dir)
				if (in_array($dir, ['.', '..']) === false && is_dir($this->default_font_path . DS . $dir))
					foreach (scandir($this->default_font_path . DS . $dir) as $file)
						if (in_array($dir, ['.', '..']) === false && is_file($this->default_font_path . DS . $dir . DS . $file))
							$fonts[] = $this->default_font_path . DS . $dir . DS . $file;

			if (isset($fonts) === false)
				return false;

			$this->font_path = $fonts[array_rand($fonts)];

			return true;
		}

		return false;
	}

	private function generate() {
		$this->code = '';
		$chars = str_split($this->characters);

		for ($i = 0; $i < $this->length; $i++)
			$this->code .= $chars[array_rand($chars)];

		return $this->code;
	}

	public function create($header = true, $return = false) {
		if ($this->load_font() === false)
			return false;

		$this->image = imagecreate($this->width, $this->height);
		$background = imagecolorallocate($this->image, $this->background[0], $this->background[1], $this->background[2]);
		$text_color = imagecolorallocate($this->image, $this->text_color[0], $this->text_color[1], $this->text_color[2]);

		foreach (str_split($this->code) as $key => $letter) {
			$angle = rand($this->angle[1] * -1, $this->angle[0] * -1) + rand($this->angle[0], $this->angle[1]);
			$shadow_font_size = $this->font_size / rand(1, 3);
			$random_color = imagecolorallocate($this->image, rand(150, 200), rand(150, 200), rand(150, 200));

			$x = $key * $shadow_font_size / $this->letter_spacing;
			if ($x < 1)
				$x = $this->position[0];
			$y = $shadow_font_size + $this->position[1];

			imagettftext($this->image, $shadow_font_size, $angle, $x, $y, $random_color, $this->font_path, $letter);
		}

		foreach (str_split($this->code) as $key => $letter) {
			$angle = rand($this->angle[1] * -1, $this->angle[0] * -1) + rand($this->angle[0], $this->angle[1]);

			$x = $key * $this->font_size / $this->letter_spacing;
			if ($x < 1)
				$x = $this->position[0];
			$y = $this->font_size + $this->position[1];

			imagettftext($this->image, $this->font_size, $angle, $x, $y, $text_color, $this->font_path, $letter);
		}

		if ($header === true)
			header('Content-Type: image/png');

		if ($return === true)
		    ob_start();
		
		imagepng($this->image);
		imagedestroy($this->image);

		if ($return === true) {
			$contents = ob_get_contents();
	    	ob_end_clean();

	    	return $contents;
	    }
	}
}

?>