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

namespace Justifi_Payments\Common;

use Justifi_Payments\Common\Log;

// Allow including common helpers in multiple plugins without breaking things.
if ( class_exists( 'Justifi_Payments\Common\Ftp' ) ) {
	return;
}


/**
 * Add menu items and pages to the WordPress admin.
 */
class Ftp {
	/**
	 * An FTP\Connection instance.
	 *
	 * @var FTP\Connection
	 */
	private $ftp;

	/**
	 * Constructor stuff, if needed.
	 */
	public function __construct() {}

	/**
	 * Opens an FTP connection to the specified host. Defaults to the FTP details
	 * entered on the Settings page, but allows arbitrary values as well.
	 *
	 * @param string $host     The FTP server address. This parameter shouldn't have any trailing slashes and shouldn't be
	 *                         prefixed with ftp://.
	 * @param string $port     This parameter specifies an alternate port to connect to. If it is omitted or set to zero,
	 *                         then the default FTP port, 21, will be used.
	 * @param string $timeout  This parameter specifies the timeout in seconds for all subsequent network operations. If
	 *                         omitted, the default value is 90 seconds. The timeout can be changed and queried at any
	 *                         time with ftp_set_option() and ftp_get_option().
	 * @param string $username The username (USER).
	 * @param string $password The password (PASS).
	 */
	public function connect( $host = false, $port = false, $timeout = false, $username = false, $password = false ) {
		$host     = $host ? $host : get_field( 'justifi_payments_ftp_host', 'option' );
		$port     = $port ? $port : get_field( 'justifi_payments_ftp_port', 'option' );
		$timeout  = $timeout ? $timeout : get_field( 'justifi_payments_ftp_timeout', 'option' );
		$username = $username ? $username : get_field( 'justifi_payments_ftp_username', 'option' );
		$password = $password ? $password : get_field( 'justifi_payments_ftp_password', 'option' );

		$this->ftp = ftp_connect( $host, $port, $timeout );
		ftp_login( $this->ftp, $username, $password );
		ftp_pasv( $this->ftp, true );
	}

	/**
	 * Closes the FTP connection and releases the resource.
	 */
	public function close() {
		ftp_close( $this->ftp );
	}

	/**
	 * Uploads a file to the FTP server
	 *
	 * @param string  $remote_filename The remote file path.
	 * @param string  $local_filename  The local file path (will be overwritten if the file already exists).
	 * @param integer $mode            The transfer mode. Must be either FTP_ASCII or FTP_BINARY.
	 * @param integer $offset          The position in the remote file to start downloading from.
	 */
	public function put( $remote_filename, $local_filename, $mode = FTP_BINARY, $offset = 0 ) {
		$remote_path = get_field( 'justifi_payments_ftp_docroot', 'option' );
		if ( $remote_path ) {
			$remote_filename = trailingslashit( $remote_path ) . $remote_filename;
		}

		$put = ftp_put( $this->ftp, $remote_filename, $local_filename );
		if ( $put ) {
			Log::log( 'ftp', sprintf( 'Successfully transferred %s to %s', $local_filename, $remote_filename ) );
		} else {
			Log::log( 'ftp', sprintf( 'Error transferring %s to %s', $local_filename, $remote_filename ) );
		}
		return $put;
	}

	/**
	 * Downloads a file from the FTP server
	 *
	 * @param string  $local_filename  The local file path (will be overwritten if the file already exists).
	 * @param string  $remote_filename The remote file path.
	 * @param integer $mode            The transfer mode. Must be either FTP_ASCII or FTP_BINARY.
	 * @param integer $offset          The position in the remote file to start downloading from.
	 */
	public function get( $local_filename, $remote_filename, $mode = FTP_BINARY, $offset = 0 ) {

	}
}
