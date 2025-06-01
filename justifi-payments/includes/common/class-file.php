<?php
/**
 * Add menu items and pages to the WordPress admin.
 *
 * @link       https://bradmessenger.com/
 * @since      1.0.0
 *
 * @package    Justifi_Payments
 * @subpackage Justifi_Payments/admin
 */

// phpcs:disable WordPress.WP.AlternativeFunctions

namespace Justifi_Payments\Common;

use \Justifi_Payments\Common\Log;

// Allow including common helpers in multiple plugins without breaking things.
if ( class_exists( 'Justifi_Payments\Common\File' ) ) {
	return;
}


/**
 * Add menu items and pages to the WordPress admin.
 */
class File {
	/**
	 * Undocumented variable
	 *
	 * @var string
	 */
	public $dir;

	/**
	 * File pointer.
	 *
	 * @var resource $fp
	 */
	public $fp;

	/**
	 * Undocumented function
	 *
	 * @param string $dir Upload destination.
	 */
	public function __construct( $dir ) {
		global $wp_filesystem;

		if ( ! $wp_filesystem || ! is_object( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}

		$this->dir = $this->get_upload_dir( $dir );
	}

	/**
	 * Opens file or URL
	 *
	 * @param [type] $filename File name.
	 * @param [type] $mode     Specifies the type of access you require to the stream.
	 */
	public function open( $filename, $mode ) {
		$this->fh = fopen( $this->get_upload_path( $filename ), $mode );
	}

	/**
	 * Closes an open file pointer.
	 *
	 * @return void
	 */
	public function close() {
		fclose( $this->fh );
	}

	/**
	 * Writes a string to a file.
	 *
	 * @param string    $file     Remote path to the file where to write the data.
	 * @param string    $contents The data to write.
	 * @param int|false $mode     The file permissions as octal number, usually 0644.
	 */
	public function create( $file, $contents, $mode = false ) {
		global $wp_filesystem;
		$path = $this->get_upload_path( $file );

		// phpcs:disable WordPress.WP.AlternativeFunctions
		$handle = fopen( $path, 'w' );

		if ( false !== $handle ) {
			fwrite( $handle, $contents );
			fclose( $handle );
		}
		// phpcs:enable WordPress.WP.AlternativeFunctions
	}

	/**
	 * Reads entire file into a string.
	 *
	 * @param string $file Name of the file to read.
	 * @return string|false Read data on success, false on failure.
	 */
	public function read( $file ) {
		global $wp_filesystem;
		$path = $this->get_upload_path( $file );

		return $wp_filesystem->get_contents( $path );
	}

	/**
	 * List all files in the directory.
	 */
	public function list() {
		global $wp_filesystem;
		$path = $this->get_upload_path();

		return $wp_filesystem->dirlist( $path );
	}



	/*= ZIP/UNZIP %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
	/**
	 * Undocumented function
	 *
	 * @param string  $file   Full path and filename of ZIP archive.
	 * @param string  $to     Full path on the filesystem to extract archive to.
	 * @param boolean $unlink If the archive should be deleted after extraction.
	 */
	public function unzip( $file, $to = null, $unlink = true ) {
		$path  = $this->get_upload_path( $file );
		$files = array();

		// phpcs:disable WordPress.NamingConventions.ValidVariableName

		$zip = new \ZipArchive();
		if ( true === $zip->open( $path ) ) {
			for ( $i = 0, $c = $zip->numFiles; $i < $c; ++$i ) {
				$filename = $zip->getNameIndex( $i );
				$fileinfo = pathinfo( $filename );

				$files[] = $filename;

				copy( "zip://{$path}#{$filename}", $this->get_upload_path( $filename ) );
			}
			$zip->close();

			if ( $unlink ) {
				unlink( $path );
			}

			return $files;
		}

		// phpcs:enable WordPress.NamingConventions.ValidVariableName

		return false;
	}



	/*= DATA PARSING %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
	/**
	 * Takes a well-formed XML string and returns it as an object.
	 *
	 * @param string  $data                A well-formed XML string.
	 * @param string  $class_name          You may use this optional parameter so that simplexml_load_file() will return an object of the specified class. That class should extend the SimpleXMLElement class.
	 * @param integer $options             Since Libxml 2.6.0, you may also use the options parameter to specify additional Libxml parameters.
	 * @param string  $namespace_or_prefix Namespace prefix or URI.
	 * @param boolean $is_prefix           True if namespace_or_prefix is a prefix, false if it's a URI; defaults to false.
	 */
	public function parse_xml_string( $data, $class_name = SimpleXMLElement::class, $options = 0, $namespace_or_prefix = '', $is_prefix = false ) {
		libxml_use_internal_errors( true );
		$xml = simplexml_load_string( $data );

		if ( false === $xml ) {
			Log::log_xml_errors( 'error', libxml_get_errors() );
		}
		libxml_clear_errors();

		return $xml;
	}

	/**
	 * Convert the well-formed XML document in the given file to an object.
	 *
	 * @param string  $filename            Path to the XML file.
	 * @param string  $class_name          You may use this optional parameter so that simplexml_load_file() will return an object of the specified class. That class should extend the SimpleXMLElement class.
	 * @param integer $options             Since Libxml 2.6.0, you may also use the options parameter to specify additional Libxml parameters.
	 * @param string  $namespace_or_prefix Namespace prefix or URI.
	 * @param boolean $is_prefix           True if namespace_or_prefix is a prefix, false if it's a URI; defaults to false.
	 */
	public function parse_xml_file( $filename, $class_name = SimpleXMLElement::class, $options = 0, $namespace_or_prefix = '', $is_prefix = false ) {
		$path = $this->get_upload_path( $filename );

		libxml_use_internal_errors( true );
		$xml = simplexml_load_file( $path );

		if ( false === $xml ) {
			Log::log_xml_errors( 'error', libxml_get_errors() );
		}
		libxml_clear_errors();

		return $xml;
	}

	/**
	 * Convert a CSV file into an associative array().
	 *
	 * @param string $filename Path to the XML file.
	 */
	public function parse_csv_file( $filename ) {
		// phpcs:disable WordPress.WP.AlternativeFunctions
		$path   = $this->get_upload_path( $filename );
		$handle = fopen( $path, 'r' );

		$headers = array();
		$csv     = array();

		// phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition
		while ( ( $data = fgetcsv( $handle ) ) !== false ) {
			if ( empty( $headers ) ) {
				$headers = $data;
				continue;
			}

			$csv[] = array_combine( $headers, $data );
		}

		fclose( $handle );

		return $csv;
		// phpcs:enable WordPress.WP.AlternativeFunctions
	}



	/*= CSV %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
	/**
	 * Format line as CSV and write to file pointer
	 *
	 * @param array   $fields An array of strings.
	 * @param boolean $header Whether or not to include a header row.
	 */
	public function putcsv( $fields, $header = false ) {
		if ( $header ) {
			fputcsv( $this->fh, array_keys( $fields ) );
		}
		fputcsv( $this->fh, array_values( $fields ) );
	}



	/*= HELPERS %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
	/**
	 * Get the upload directory.
	 *
	 * @param string $dir Directory to use inside of wp-content/justifi.
	 */
	public function get_upload_dir( $dir = null ) {
		if ( ! empty( $this->dir ) ) {
			return $this->dir;
		}

		$wp_upload_dir   = wp_upload_dir();
		$filesystem_path = trailingslashit( $wp_upload_dir['basedir'] . '/justifi-payments/' . $dir );

		if ( ! is_dir( $filesystem_path ) ) {
			wp_mkdir_p( $filesystem_path );
		}

		return $filesystem_path;
	}

	/**
	 * Get full path to file.
	 *
	 * @param string $file Filename of file in the instantiated directory.
	 */
	public function get_upload_path( $file = '' ) {
		return $this->get_upload_dir() . $file;
	}
}
