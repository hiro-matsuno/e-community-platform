<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php
/**
 * Description of Mime
 *
 * @author ikeda
 */
class Mime {

	const ICON_FILE = '/image/icons/page.png';
	const ICON_TEXT = '/image/icons/page_white_edit.gif';
	const ICON_PDF = '/image/icons/icon_pdf.gif';
	const ICON_DOC = '/image/icons/page_white_word.png';
	const ICON_XLS = '/image/icons/page_white_excel.png';
	const ICON_PPT = '/image/icons/page_white_powerpoint.png';
	const ICON_MSOFFICE = '/image/icons/doc_offlice.png';
	const ICON_PICT = '/image/icons/page_white_picture.png';
	const ICON_AUDIO = '/image/icons/music.png';
	const ICON_MOVIE = '/image/icons/film.png';

	static private $instance;

	private $knownTypes;

	private function __construct() {

		//	パターン一致範囲が狭いものから登録していく.
		$this->knownTypes = array(

			new MimeType( "text/plain", "|text/plain|", "テキストファイル", Mime::ICON_TEXT ),
			
			new MimeType( "application/pdf", "|application/pdf|", "PDFファイル", Mime::ICON_PDF  ),

			new MimeType( "application/msword", "%application/.*(word|excel|powerpoint|office)%", "MS Office ファイル", Mime::ICON_MSOFFICE  ),
//			new MimeType( "application/msword", "|application/.*word|", "Word文書", Mime::ICON_DOC  ),
//			new MimeType( "application/vnd.ms-excel", "|application/.*excel|", "Excelファイル", Mime::ICON_XLS  ),
//			new MimeType( "application/vnd.ms-powerpoint", "%application/.*(powerpoint|office)%", "PPTファイル", Mime::ICON_PPT  ),
			
			new MimeType( "image/bmp", "|image/bmp|", "BMP画像", Mime::ICON_PICT ),
			new MimeType( "image/gif", "|image/gif|", "GIF画像", Mime::ICON_PICT ),
			new MimeType( "image/jpeg", "|image/jpeg|", "JPEG画像", Mime::ICON_PICT ),
			new MimeType( "image/png", "|image/png|", "PNG画像", Mime::ICON_PICT ),
			new MimeType( "image/tiff", "|image/tiff|", "TIF画像", Mime::ICON_PICT ),
			new MimeType( "application/octet-stream", "|^image/|", "画像ファイル", Mime::ICON_PICT ),

			new MimeType( "video/avi", "|video/avi|", "AVI動画", Mime::ICON_MOVIE ),
			new MimeType( "video/mp4", "|video/mp4|", "MP4動画", Mime::ICON_MOVIE ),
			new MimeType( "video/mpeg", "|video/mpeg|", "MPEG動画", Mime::ICON_MOVIE ),
			new MimeType( "video/quicktime", "|video/quicktime|", "QuickTime動画", Mime::ICON_MOVIE ),
			new MimeType( "video/vnd.rn-realvideo", "|video/vnd.rn-realvideo|", "RV動画", Mime::ICON_MOVIE ),
			new MimeType( "application/octet-stream", "|^video/|", "動画ファイル", Mime::ICON_MOVIE ),

			new MimeType( "application/octet-stream", "|^audio/|", "オーディオファイル", Mime::ICON_AUDIO ),

			new MimeType( "application/octet-stream", "/.*/", "ファイル", Mime::ICON_FILE )
			
		);

	}

	public function getInstance() {

		if ( null === Mime::$instance ) {

			Mime::$instance = new Mime();

		}

		return Mime::$instance;

	}

	public function getMimeType( $fileData ) {

		$type = null;

		if ( "yt:" != substr( $fileData->getFilePath(), 0, 3 ) ) {

			$contentType = mime_content_type_wrap( $fileData->getFilePath() );

			if ( 0 < ( $index = strpos( $contentType, ";" ) ) ) {
				$contentType = substr( $contentType, 0, $index );
			}

			if ( null === ( $type = $this->searchMimeType( $contentType ) ) ) {
				throw new Exception();
			}

		} else {

			$type = new MimeType( "youtube", "", "Youtube動画", ICON_MOVIE );

		}

		return $type;

	}

	private function searchMimeType( $typeStr ) {

		foreach ( $this->knownTypes as $type ) {

			if ( $type->check( $typeStr ) ) { return $type; }

		}

		return null;

	}

}

class MimeType {

	private $type;
	private $pattern;
	private $name;
	private $icon;

	public function __construct( $type, $pattern, $name, $icon ) {

		$this->setType( $type );
		$this->setPattern( $pattern );
		$this->setName( $name );
		$this->setIcon( $icon );

	}

	public function getType() { return $this->type; }
	public function getName() { return $this->name; }
	public function getIcon() { return $this->icon; }

	protected function setType( $type ) { $this->type = $type; }
	protected function setPattern( $pattern ) { $this->pattern = $pattern; }
	protected function setName( $name ) { $this->name = $name; }
	protected function setIcon( $icon ) { $this->icon = $icon; }

	function check( $contentType ) {

		return preg_match( $this->pattern, $contentType );

	}

}
?>
